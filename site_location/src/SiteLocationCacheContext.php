<?php

namespace Drupal\site_location;

use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Component\Datetime\TimeInterface;

class SiteLocationCacheContext implements CacheContextInterface {

    /**
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $timeService;

    /**
     * {@inheritdoc}
     */
    public function __construct(TimeInterface $time_service) {
        $this->timeService = $time_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabel() {
        return t('Current Time Block cache context');
    }

    /**
     * {@inheritdoc}
     */
    public function getContext() {
        $request_time = $this->timeService->getRequestTime();
        $current_time = $request_time;
        return $current_time;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheableMetadata() {
        return new CacheableMetadata();
    }

}
