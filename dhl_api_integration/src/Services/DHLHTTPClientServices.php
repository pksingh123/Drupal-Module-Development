<?php

declare(strict_types=1);

namespace Drupal\dhl_api_integration\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality to connect to DHL API.
 */
class DHLHTTPClientServices
{

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Logger Factory Var.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a DHLHTTPClientServices object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   A Guzzle client object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger Factory Service Object.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger)
  {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger->get('apar_technologies_dhl_api');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('http_client'),
      $container->get('config.factory'),
      $container->get('logger.factory')
    );
  }

  /**
   * Implement location finder api.
   *
   * @$params
   */
  public function locationFinder($params)
  {
    $config = $this->configFactory->get('dhl_api_integration.settings');
    $apiBaseUrl = $config->get('api_base_url');
    $apiKey = $config->get('api_key');
    $responseArray = [];
    try {

      $response = $this->httpClient->get($apiBaseUrl . '/find-by-address', [
        'headers' => [
          'DHL-API-Key' => $apiKey,
          'Content-type' => 'application/json',
        ],
        'query' => $params,
      ])->getBody()->getContents();

      $response = Json::decode($response);
      $responseArray = ['success' => TRUE, 'data' => $response];
      return $responseArray;
    } catch (\Exception $e) {
      $response = $e->getResponse();
      if ($response) {
        $errorMessageRaw = $response->getBody()->getContents();
        $errorMessage = Json::decode($errorMessageRaw);
        $sfaResponse = ['Query' => $params, 'Message' => $errorMessage];
        $this->loggerFactory->error('<pre><code>' . print_r($sfaResponse, TRUE) . '</code></pre>');
        $responseArray = ['success' => FALSE, 'error_message' => $errorMessage];
      } else {
        $responseArray = ['success' => FALSE, 'error_message' => 'Error'];
      }

      return $responseArray;
    }
  }
}
