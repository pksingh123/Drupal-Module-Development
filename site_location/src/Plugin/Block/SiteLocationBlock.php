<?php

namespace Drupal\site_location\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Site Location Block' Block.
 *
 * @Block(
 *   id = "sitelocation_block",
 *   admin_label = @Translation("Site Location block"),
 *   category = @Translation("Site Location"),
 * )
 */
class SiteLocationBlock extends BlockBase {

    /**
     * Class constructor
     */
    Protected $currentTimeBasedOnTimeZone;

    public function __construct() {
        $this->currentTimeBasedOnTimeZone = \Drupal::service('site_location.currenttime');
    }

    /**
     * {@inheritdoc}
     */
    public function build() {

        $siteLocationDetailsArr = $this->currentTimeBasedOnTimeZone->siteLocationAndCurentTime();
        $country = $siteLocationDetailsArr['country'];
        $city = $siteLocationDetailsArr['city'];
        $date = $siteLocationDetailsArr['date'];
        $html = "<div>";
        if(!empty($country)){
            $html .= "<h2>$country</h2>";
        }
        if(!empty($city)){
            $html .= "<h3>$city</h3>";
        }
        
        $html .= "<h4>$date</h4>";
        $html .= "</div>";

        $build['current_time'] = [
            '#markup' => $html
        ];
        return $build;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheContexts() {
        return Cache::mergeContexts(
                        parent::getCacheContexts(), ['current_time']
        );
    }

}
