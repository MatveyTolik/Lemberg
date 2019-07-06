<?php

namespace Drupal\two_view_modes\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'TwoViewModes' formatter.
 *
 * @FieldFormatter(
 *   id = "two_view_modes",
 *   label = @Translation("Two View Modes"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class TwoViewModes extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManager $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label,
      $view_mode, $third_party_settings);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'first_view_mode' => 'full',
      'second_view_mode' => 'teaser',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $entityType = 'taxonomy_term';
    $viewModes = $this->entityDisplayRepository->getViewModes($entityType);

    if (!empty($viewModes)) {
      foreach ($viewModes as $id => $viewMode) {
        $options[$id] = $viewMode['label'];
      }
    }

    $element['first_view_mode'] = [
      '#title' => $this->t('First view mode'),
      '#type' => 'select',
      '#options' => !empty($options) ? $options : [],
      '#default_value' => $this->getSetting('first_view_mode'),
    ];

    $element['second_view_mode'] = [
      '#title' => $this->t('Second view mode'),
      '#type' => 'select',
      '#options' => !empty($options) ? $options : [],
      '#default_value' => $this->getSetting('second_view_mode'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $viewBuilder = $terms = [];
    $entityType = 'taxonomy_term';
    $firstViewMode = $this->getSetting('first_view_mode');
    $secondViewMode = $this->getSetting('second_view_mode');

    foreach ($items as $delta => $item) {
      $tid = $item->getValue()['target_id'];
      $terms[] = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    }

    if (!empty($terms)) {
      // Display first taxonomy term.
      $viewBuilder[] = $this->entityTypeManager->getViewBuilder($entityType)->view(array_shift($terms), $firstViewMode);

      if (!empty($terms)) {
        // Display other taxonomy terms.
        $viewBuilder[] = $this->entityTypeManager->getViewBuilder($entityType)->viewMultiple($terms, $secondViewMode);
      }
    }

    $result = $viewBuilder;
    $result['#cache'] = [
      'max-age' => 0,
    ];

    return $result;
  }

}
