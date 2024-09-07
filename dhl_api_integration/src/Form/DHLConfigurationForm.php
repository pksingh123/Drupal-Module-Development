<?php

declare(strict_types=1);

namespace Drupal\dhl_api_integration\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality to set the configuration of DHLConfigurationForm API.
 */
class DHLConfigurationForm extends ConfigFormBase
{

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a DHLConfigurationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory)
  {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['dhl_api_integration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'dhl_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('dhl_api_integration.settings');

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base URL'),
      '#default_value' => $config->get('api_base_url'),
    ];
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DHL API Key'),
      '#default_value' => $config->get('api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    // Add validation if necessary.
    $api_base_url = $form_state->getValue('api_base_url');
    if (!filter_var($api_base_url, FILTER_VALIDATE_URL)) {
      $form_state->setErrorByName('api_base_url', $this->t('The URL %api_base_url is not valid.', ['%api_base_url' => $api_base_url]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $api_base_url = $form_state->getValue('api_base_url');
    $api_base_url = rtrim($api_base_url, '/');

    $this->configFactory->getEditable('dhl_api_integration.settings')
      ->set('api_base_url', $api_base_url)
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
