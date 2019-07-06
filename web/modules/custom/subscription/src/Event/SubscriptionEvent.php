<?php

namespace Drupal\subscription\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Defines a Locale event.
 */
class SubscriptionEvent extends Event {

  const SUBSCRIPTION = 'user.subscription';

}
