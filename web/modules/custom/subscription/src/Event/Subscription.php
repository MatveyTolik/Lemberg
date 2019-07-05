<?php

namespace Drupal\subscription\Event;

/**
 * Class EmeEvents.
 *
 * @package Drupal\subscription\Event
 */
final class Subscription {
  /**
   * Name of the event fired when a new Subscription is reported.
   *
   * @Event
   *
   * @see \Drupal\subscription\Event\Subscription
   *
   * @var string
   */
  const SUBSCRIPTION = 'user.subscription';

}
