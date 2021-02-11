<?php

namespace Drupal\site_location;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CurrentTimeBasedOnTimeZone.
 */
class CurrentTimeBasedOnTimeZone {

    /**
     * The datetime.time service.
     * 
     * @var \Drupal\Component\Datetime\TimeInterface
     */
    protected $timeService;

    /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * The date format.
     *
     * @var \Drupal\Core\Datetime\DateFormatterInterface
     */
    protected $dateFormat;

    public function __construct(TimeInterface $time_service, ConfigFactoryInterface $config_factory, DateFormatterInterface $date_formatter) {

        $this->timeService = $time_service;
        $this->configFactory = $config_factory;
        $this->dateFormat = $date_formatter;
    }

    /**
     * Show the current date and time base on timezone of the site location.
     *
     * @return string
     * Return the date.
     */
    public function siteLocationAndCurentTime() {
        $siteLocationDetailsArr = array();
        $config = $this->configFactory->get('site_location.settings');
        $siteLocationDetailsArr['country'] = $config->get('country.default');
        $siteLocationDetailsArr['city'] = $config->get('city.default');
        $time_zone = $config->get('timezone.default');
        $date = $this->dateFormat->format($this->timeService->getRequestTime(), 'custom', 'dS M Y - H:i A', $time_zone);
        $siteLocationDetailsArr['date'] = $date;
        return $siteLocationDetailsArr;
    }

}
