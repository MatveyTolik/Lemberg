<?php

namespace Drupal\subscription\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\subscription\Event\SubscriptionEvent;

/**
 * Class UserEventsSubscriber
 *
 * @package Drupal\subscription\EmailStaticsEventSubscriber
 */
class UserEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SubscriptionEvent::SUBSCRIPTION => 'userSubscription'
    ];
  }

  /**
   * @param Event $event
   */
  public function userSubscription(Event $event) {

    $a = 2;
  }

}
