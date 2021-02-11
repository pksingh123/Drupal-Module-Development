<?php

namespace Drupal\site_location\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure regional settings for this site.
 *
 * @internal
 */
class SiteLocationForm extends ConfigFormBase {

    /**
     * Constructs a SiteLocation object.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The factory for configuration objects.
     */
    public function __construct(ConfigFactoryInterface $config_factory) {
        parent::__construct($config_factory);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
                $container->get('config.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sitelocation_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return ['site_location.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $site_location = $this->config('site_location.settings');

        // Zones
        //$zones = system_time_zones(NULL, TRUE);

        $zonelist = timezone_identifiers_list();

        $zones = array();
        foreach ($zonelist as $zone) {

            if (preg_match('!^((America/Chicago|America/New_York|Asia/Tokyo|Asia/Dubai|Asia/Kolkata|Europe/Amsterdam|Europe/Oslo|Europe/London))!', $zone)) {
                $zones[$zone] = $zone;
            }
        }

        $form['site_country'] = [
            '#type' => 'textfield',
            '#title' => t('Country'),
            '#default_value' => $site_location->get('country.default') ?: '',
            '#attributes' => ['class' => ['country-detect']],
        ];

        $form['site_city'] = [
            '#type' => 'textfield',
            '#title' => t('City'),
            '#default_value' => $site_location->get('city') ?: '',
        ];

        $form['site_timezone'] = [
            '#type' => 'select',
            '#title' => t('Time zone'),
            '#empty_value' => t('Select Time Zone'),
            '#default_value' => $site_location->get('timezone.default') ?: '',
            '#options' => $zones,
            '#required' => TRUE,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('site_location.settings')
                ->set('country.default', $form_state->getValue('site_country'))
                ->set('city.default', $form_state->getValue('site_city'))
                ->set('timezone.default', $form_state->getValue('site_timezone'))
                ->save();

        parent::submitForm($form, $form_state);
    }

}
