<?php

namespace Drupal\custom_rest_resources\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 * id = "custom_node_rest_resource",
 * label = @Translation("Custom node rest resource"),
 * serialization_class = "Drupal\node\Entity\Node",
 * uri_paths = {
 * "canonical" = "/api/custom-node-rest.json",
 * "https://www.drupal.org/link-relations/create" = "/api/custom-node-rest.json"
 * }
 * )
 */
class CustomNodeRestResource extends ResourceBase {

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a new ArticalGetRestResource object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param array $serializer_formats
     *   The available serialization formats.
     * @param \Psr\Log\LoggerInterface $logger
     *   A logger instance.
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   A current user instance.
     */
    public function __construct(
    array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
                $configuration, $plugin_id, $plugin_definition, $container->getParameter('serializer.formats'), $container->get('logger.factory')->get('example_node_rest'), $container->get('current_user')
        );
    }

    /**
     * Responds to POST requests.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity object.
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post($node_data) {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }
        if ($node_data->nid->value == '') {
            $node = Node::create(
                            array(
                                'type' => $node_data->type->target_id,
                                'title' => $node_data->title->value,
                                'body' => [
                                    'summary' => '',
                                    'value' => $node_data->body->value,
                                    'format' => 'full_html',
                                ],
                            )
            );
            $node->save();
        } elseif ($node_data->nid->value != '' && is_int($node_data->nid->value)) {
            $values = \Drupal::entityQuery('node')->condition('nid', $node_data->nid->value)->execute();
            $node_exists = !empty($values);
            if ($node_exists) {
                $node = Node::load($node_data->nid->value);
                //Title field set 
                $node->setTitle($node_data->title->value);
                //Body can now be an array with a value and a format.
                //If body field exists.
                $body = [
                    'value' => $node_data->body->value,
                    'format' => 'basic_html',
                ];
                $node->set('body', $body);
                $node->save();
                //return new ResourceResponse($node);
            } else {
                \Drupal::logger('custom_node_rest_api')->error('Node nid ' . $node_data->nid->value . ' not exists');
                return new ResourceResponse(array('Error' => 'Node nid not exists'));
            }
        } else {
            return new ResourceResponse(array('Error' => 'Data not corrected'));
        }
        return new ResourceResponse($node);
    }

    /**
     * Responds to GET requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get() {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }
        $entities = \Drupal::entityTypeManager()
                ->getStorage('node')
                ->loadMultiple();
        foreach ($entities as $entity) {
            $result[$entity->id()] = array(
                'nid' => $entity->id(),
                'title' => $entity->title->value
            );
        }
        $response = new ResourceResponse($result);
        $response->addCacheableDependency($result);
        return $response;
    }

}
