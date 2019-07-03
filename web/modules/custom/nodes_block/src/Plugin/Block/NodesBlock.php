<?php

namespace Drupal\nodes_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\entity_name_retriever\EntityNameRetriever;

/**
 * Provides a 'Nodes' Block.
 *
 * @Block(
 *   id = "nodes_block",
 *   admin_label = @Translation("Nodes block"),
 *   category = @Translation("Nodes Block"),
 * )
 */
class NodesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new NodesBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   */
  public function __construct($configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration,
      $plugin_id,
      $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $viewBuilder = $types = [];
    $entity_type = 'node';
    $config = $this->getConfiguration();

    if (isset($config['nodes_block']['type'])) {
      $type_ids = $config['nodes_block']['type'];
      foreach ($type_ids as $type_id) {
        if (!empty($type_id)) {
          $types[] = $this->entityTypeManager->getStorage('node_type')->load($type_id);
        }
      }
    }
    else {
      $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    }

    foreach ($types as $type) {
      $content_type = $type->id();

      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $query->condition('type', $content_type);
      $query->condition('status', '1');
      if (!empty($config['nodes_block']['number'])) {
        $query->range(0, $config['nodes_block']['number']);
      }
      $nids = $query->execute();

      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      $viewBuilder[] = $this->entityTypeManager->getViewBuilder($entity_type)->viewMultiple($nodes, 'teaser');
    }

    return array(
      $viewBuilder,
      '#cache' => [
        'tags' => ['node_list'],
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $options = [];
    $config = $this->getConfiguration();
    $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }

    $form['nodes_block']['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of nodes'),
      '#description' => $this->t('Number nodes for display'),
      '#default_value' => isset($config['nodes_block']['number']) ? $config['nodes_block']['number'] : 10,
    ];

    $form['nodes_block']['type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Node types'),
      '#description' => $this->t('Select node type(s) for display in block'),
      '#default_value' => isset($config['nodes_block']['type']) ? $config['nodes_block']['type'] : '',
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['nodes_block']['number'] = $values['nodes_block']['number'];
    $this->configuration['nodes_block']['type'] = $values['nodes_block']['type'];
  }

}
