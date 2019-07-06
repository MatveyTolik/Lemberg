<?php

namespace Drupal\subscription\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\subscription\Event\SubscriptionEvent;

/**
 * Class UserEventsSubscriber.
 *
 * @package Drupal\subscription\EmailStaticsEventSubscriber
 */
class UserEventsSubscriber implements EventSubscriberInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * UserEventsSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user account.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManager $entity_type_manager, MessengerInterface $messenger) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SubscriptionEvent::SUBSCRIPTION => 'userSubscription',
    ];
  }

  /**
   * Add role subscriber to current user.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The Event to process.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function userSubscription(Event $event) {
    $currentUser = $this->currentUser;
    if ($currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($currentUser->id());
      if (!($user->hasRole('subscriber'))) {
        $user->addRole('subscriber');
        $user->save();
        $this->messenger->addStatus('Subscriber role added successfully');
      }
      else {
        $this->messenger->addStatus('You already have a subscriber role');
      }
    }
    else {
      $this->messenger->addStatus('You are not authorized, please sign in to your account on the site');
    }

  }

}
