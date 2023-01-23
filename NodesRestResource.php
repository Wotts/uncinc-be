<?php

namespace Drupal\nodes_rest\Plugin\rest\resource;

use Psr\Log\LoggerInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get the results of a view.
 *
 * @RestResource(
 *   id = "nodes_rest_list",
 *   label = @Translation("Nodes listing"),
 *   serialization_class = "Drupal\node\Entity\Node",
 *   uri_paths = {
 *     "canonical" = "/nodes/list"
 *   }
 * )
 */
class NodesRestResource extends ResourceBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The nodes manager.
   * 
   * @var \Repositories\NodesRepository
   */
  protected $nodesRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    NodesRepository $nodes_repository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->entityTypeManager = $entity_type_manager;
    $this->nodesRepository = $nodes_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager')
    );
  } 

  /**
   * Get Nodes, optionally filter by type and paginate.
   * 
   * @param int $id
   * @param string $type
   * @param int $page_size
   * @param int $page_number
   * @return ResourceResponse
   *
   * {@inheritdoc}
   */
  public function get(
    int $id,
    string $type,
    int $page_size,
    int $page_number = 1
  ) {
    $nodes;
    
    if ($type) {
      $nodes = $this->nodesRepository->findBy(['type' => $type]);
    } elseif ($id) {
      $nodes = $this->nodesRepository->findBy(['id' => $id]);
    } else {
      $nodes = $this->nodesRepository->findAll();
    }

    $result;
    $pages;

    if ($page_size) {
      $chunks = array_chunk($nodes, $page_size);
      $result = $chunks[$page_number];
      $pages = count($chunks);
    } else {
      $result = $nodes;
      $pages = 1;
    }

    $output = [
      'result' => $result,
      'page' => $page_number,
      'pages' => $pages,
    ];

    $response = new ResourceResponse($output, 200);

    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['user']);
    $response->addCacheableDependency($cacheability);

    return $response;
  }

}
