<?php
namespace Drupal\custom_rest_resources\Plugin\rest\resource;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\rest\resource\EntityResource;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;

 /**
 * Provides a resource to get and patch asset type terms
 *
 * @RestResource(
 *   id = "custom_entity_resource",
 *   label = @Translation("Custom Entity Resource"),
 *   entity_type = "node",
 *   serialization_class = "Drupal\node\Entity\Node",
 *   uri_paths = {
 *     "canonical" = "/api/node/{node}",
 *     "https://www.drupal.org/link-relations/create" = "/api/node"
 *   }
 * )
 */
class CustomEntityResource extends EntityResource {

   /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $link_relation_type_manager
   *   The link relation type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, array $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory, PluginManagerInterface $link_relation_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $serializer_formats, $logger, $config_factory, $link_relation_type_manager);

  }

   /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The entity.
   */
  public function post(EntityInterface $entity = NULL) {

    parent::post($entity);
    return new ResourceResponse($entity);
  }

   /**
   * Responds to PATCH requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $original_entity
   *   The original entity.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   The current entity.
   */
  public function patch(EntityInterface $original_entity, EntityInterface $entity = NULL) {

    parent::patch($original_entity, $entity);

    return new ResourceResponse($entity, 200);
  }
}