<?php

namespace Drupal\subscription\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\subscription\Event\Subscription;

/**
 * Class EmailStaticsEventSubscriber
 *
 * @package Drupal\emeevents\EmailStaticsEventSubscriber
 */
class UserEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      Subscription::SUBSCRIPTION => 'userSubscription'
    ];
  }

  /**
   * @param Event $event
   */
  public function userSubscription(Event $event) {

    $a = 2;
  }

}
