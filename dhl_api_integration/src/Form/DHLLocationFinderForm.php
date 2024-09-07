<?php

declare(strict_types=1);

namespace Drupal\dhl_api_integration\Form;

use Drupal\dhl_api_integration\Services\DHLHttPClientServices;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides functionality to filter locations based on country, city&postalcode.
 */
class DHLLocationFinderForm extends FormBase
{

  /**
   * The DHL client service variable.
   *
   * @var \Drupal\dhl_api_integration\Services\DHLHttPClientServices
   */
  protected $dhlClient;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs Service object.
   *
   * @param \Drupal\dhl_api_integration\Services\DHLHttPClientServices $dhl_client
   *   DHL client Service Object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service to display messages.
   */
  public function __construct(DHLHttPClientServices $dhl_client, MessengerInterface $messenger)
  {
    $this->dhlClient = $dhl_client;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('dhl.http_client'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'dhl_location_finder_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    if ($form_state->get('locations')) {
      $form['#attached']['library'][] = 'dhl_api_integration/dhl_location_output_style';
      $form['results'] = [
        '#type' => 'markup',
        '#markup' => '
          <div class="location-results">
            <h3>' . $this->t('Locations Results') . '</h3>
            <div class="location-data">' . '<pre>' . $form_state->get('locations') . '</pre>' . '</div>
          </div>',
      ];
    }
    $form['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#default_value' => $form_state->getValue('country', ''),
      '#required' => TRUE,
      '#description' => $this->t('Enter the country code (e.g., US for United States).'),
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#default_value' => $form_state->getValue('city', ''),
      '#required' => TRUE,
    ];

    $form['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#default_value' => $form_state->getValue('postal_code', ''),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Find Locations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $country = $form_state->getValue('country');
    $city = $form_state->getValue('city');
    $postal_code = $form_state->getValue('postal_code');
    $param_array = [
      'countryCode' => $country,
      'addressLocality' => $city,
      'postalCode' => $postal_code,
    ];
    $response = $this->dhlClient->locationFinder($param_array);
    if ($response['success'] == FALSE) {
      if (isset($response['error_message']['detail'])) {
        $errorMessage = $response['error_message']['detail'];
      } else {
        $errorMessage = $response['error_message'];
      }
      $this->messenger->addStatus($this->t("Your query is failed. %error.", ['%error' => $errorMessage]));
    } else {

      $locations = $this->prePareLocationArray($response);
      $filteredLocations = $this->filterLocations($locations);
      $yamlContent = '';
      $yamlDocuments = array_map(function ($doc) {
        // Remove 'location' key.
        unset($doc['location']);
        return Yaml::dump($doc, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
      }, $filteredLocations);

      $yamlContent .= "---\n";
      $yamlContent .= implode("\n--- \n", $yamlDocuments);
      $form_state->set('locations', $yamlContent);
    }
    // Set the value of the form field after submission.
    $form_state->setValue('country', $country);
    $form_state->setValue('city', $city);
    $form_state->setValue('postal_code', $postal_code);
    // Rebuild the form to reflect the changes.
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  private function filterLocations(array $locations)
  {
    return array_filter($locations, function ($location) {
      $weekend_open = !empty($location['openingHours']['saturday']) && !empty($location['openingHours']['sunday']);
      $odd_number = $this->locationIsOddAfterHyphen($location['location']);
      return $weekend_open && !$odd_number;
    });
  }

  /**
   * {@inheritdoc}
   */
  private function prePareLocationArray(array $response)
  {
    $locations = [];
    foreach ($response['data']['locations'] as $location) {
      $locationId = $location['location']['ids'][0]['locationId'];
      $openingHours = [];
      foreach ($location['openingHours'] as $openinghours) {
        $dayOfWeek = $openinghours['dayOfWeek'];
        $parseurl = parse_url($dayOfWeek, PHP_URL_PATH);
        if (!empty($parseurl)) {
          $day = strtolower(ltrim($parseurl, '/'));
          $openingHours[$day] = $openinghours['opens'] . ' - ' . $openinghours['closes'];
        }
      }
      $countryCode = $location['place']['address']['countryCode'];
      $postalCode = $location['place']['address']['postalCode'];
      $addressLocality = $location['place']['address']['addressLocality'];
      $streetAddress = $location['place']['address']['streetAddress'];

      $locations[] = [
        'location' => $locationId,
        'locationName' => $location['name'],
        'address' => [
          'countryCode' => "$countryCode",
          'postalCode' => "$postalCode",
          'addressLocality' => "$addressLocality",
          'streetAddress' => "$streetAddress",
        ],
        'openingHours' => $openingHours,
      ];
    }
    return $locations;
  }

  /**
   * {@inheritdoc}
   */
  private function locationIsOddAfterHyphen($location)
  {
    $parts = explode('-', $location);

    if (count($parts) == 2 && is_numeric($parts[1])) {
      return strlen($parts[1]) % 2 != 0;
    }
    return FALSE;
  }
}
