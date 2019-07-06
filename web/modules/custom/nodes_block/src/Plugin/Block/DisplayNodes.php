<?php

namespace Drupal\nodes_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'DisplayNodes' Block.
 *
 * @Block(
 *   id = "display_nodes",
 *   admin_label = @Translation("Display Nodes"),
 *   category = @Translation("Display Nodes"),
 * )
 */
class DisplayNodes extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration,
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

    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', $config['content_type'], 'IN');
    $query->condition('status', '1');
    $query->sort('type');
    $query->sort('created', 'DESC');
    $query->range(0, $config['limit']);
    $nids = $query->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    // Display nodes.
    $viewBuilder[] = $this->entityTypeManager->getViewBuilder($entity_type)->viewMultiple($nodes, 'teaser');

    $result = $viewBuilder;
    $result['#cache'] = [
      'tags' => [
        'node_list'
      ],
    ];
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $options = $this->getContentTypeList();

    $form['limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Number of nodes'),
      '#description' => $this->t('Number nodes for display'),
      '#default_value' => isset($config['limit']) ? $config['limit'] : 10,
    ];

    $form['content_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Node types'),
      '#description' => $this->t('Select node type(s) for display in block'),
      '#default_value' => isset($config['content_type']) ? $config['content_type'] : '',
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['limit'] = $form_state->getValue('limit');
    $content_types = array_filter($form_state->getValue('content_type'));
    $content_types = !empty($content_types) ? $content_types : $this->getContentTypeList();
    $this->configuration['content_type'] = array_keys($content_types);
  }

  /**
   * Return content types.
   *
   * @return array
   *   Content types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getContentTypeList() {
    $options = [];
    $types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    foreach ($types as $type) {
      $options[$type->id()] = $type->label();
    }

    return $options;
  }

}
