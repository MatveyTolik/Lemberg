<?php

namespace Drupal\two_view_modes\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityManagerInterface;
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
 *     "Random"
 *   }
 * )
 */
class TwoViewModes extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Construct a MyFormatter object
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityManagerInterface $entityManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label,
      $view_mode, $third_party_settings);

    $this->entityManager = $entityManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      // Add any services you want to inject here
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the random string.');
    return $summary;
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
    $element['first_view_mode'] = [
      '#title' => $this->t('First view mode'),
      '#type' => 'select',
      '#options' => [
        'teaser' => $this->t('teaser'),
        'full' => $this->t('full'),
      ],
      '#default_value' => $this->getSetting('first_view_mode'),
    ];

    $element['second_view_mode'] = [
      '#title' => $this->t('Second view mode'),
      '#type' => 'select',
      '#options' => [
        'short' => $this->t('teaser'),
        'long' => $this->t('full'),
      ],
      '#default_value' => $this->getSetting('second_view_mode'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }

    return $element;
  }

}
