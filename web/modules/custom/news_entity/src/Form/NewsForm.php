<?php

namespace Drupal\news_entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for News edit forms.
 *
 * @ingroup news_entity
 */
class NewsForm extends ContentEntityForm {

  /**
   * The current form step.
   *
   * @var int
   */
  protected $step = 1;

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Private temporary storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $store;

  /**
   * Constructs a new NewsForm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, PrivateTempStoreFactory $temp_store_factory, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('multistep_data');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('user.private_tempstore'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Add next step button, hide fields.
    if ($this->step == 1) {
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#submit' => ['::submitNextStep'],
      ];

      hide($form['field_news_category']);
      hide($form['field_tags']);
    }

    // Add previous step button, hide fields.
    if ($this->step == 2) {
      $form['actions']['submit']['previous_step'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#submit' => ['::submitPreviousStep'],
      ];

      hide($form['field_cover_image']);
      hide($form['field_description']);
      hide($form['field_link']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Set values into entity.
    $file = $this->store->get('field_cover_image');
    if (!empty($file['fids'])) {
      $this->entity->set('field_cover_image', [
        'target_id' => $file['fids'][0],
        'alt' => $file['alt'],
      ]);
    }

    $this->entity->set('field_description', [
      'value' => $this->store->get('field_description')['value'],
      'format' => $this->store->get('field_description')['format'],
    ]);

    $this->entity->set('field_link', $this->store->get('field_link'));

    $status = parent::save($form, $form_state);
    $this->deleteStore();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label News.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label News.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.news.canonical', ['news' => $entity->id()]);
  }

  /**
   * {@inheritdoc}
   *
   * Save values into store and change get next step form.
   */
  public function submitNextStep(array &$form, FormStateInterface $form_state) {
    $this->store->set('field_cover_image', array_shift($form_state->getValue('field_cover_image')));
    $this->store->set('field_description', array_shift($form_state->getValue('field_description')));
    $this->store->set('field_link', array_shift($form_state->getValue('field_link')));

    $form_state->setRebuild();
    $this->step++;
  }

  /**
   * {@inheritdoc}
   *
   * Get previous step form.
   */
  public function submitPreviousStep(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $this->step--;
  }

  /**
   * Helper method that removes all the keys from the store collection used for
   * the multistep form.
   */
  protected function deleteStore() {
    $keys = ['field_cover_image', 'field_description', 'field_link'];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }

}
