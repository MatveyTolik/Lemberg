<?php

namespace Drupal\news_entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
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
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Constructs a new NewsForm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user account.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, AccountProxyInterface $account, PrivateTempStoreFactory $temp_store_factory) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->tempStoreFactory = $temp_store_factory;
    $this->account = $account;
    $this->store = $this->tempStoreFactory->get('multistep_data');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('user.private_tempstore')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if ($this->step == 1) {
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#submit' => ['::submitNextStep'],
      ];

      $form['field_news_category']['#type'] = 'hidden';
      $form['field_tags']['#type'] = 'hidden';
    }

    if ($this->step == 2) {
      $form['actions']['submit']['previous_step'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#submit' => ['::submitPreviousStep'],
      ];

      $form['field_cover_image']['#type'] = 'hidden';
      $form['field_description']['#type'] = 'hidden';
      $form['field_link']['#type'] = 'hidden';

      $this->entity->set('field_cover_image', $this->store->get('field_cover_image'));
      $this->entity->set('field_description', $this->store->get('field_description'));
      $this->entity->set('field_link', $this->store->get('field_link'));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

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
   */
  public function submitNextStep(array &$form, FormStateInterface $form_state) {
    $this->store->set('field_cover_image', $form_state->getValue('field_cover_image'));
    $this->store->set('field_description', $form_state->getValue('field_description')[0]['value']);
    $this->store->set('field_link', $form_state->getValue('field_link')[0]);

    $form_state->setRebuild();
    $this->step++;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPreviousStep(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
    $this->step--;
  }

}
