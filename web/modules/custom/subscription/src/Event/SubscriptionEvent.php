<?php

namespace Drupal\subscription\Event;

use Symfony\Component\EventDispatcher\Event;

class SubscriptionEvent extends Event {
  const SUBSCRIPTION = 'user.subscription';

  protected $message;

  /**
   * Constructor for the OtherModuleEvent class.
   *
   * @param string $message
   */
  public function __construct($message) {
    $this->message = $message;
  }

  /**
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * @param string $message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

}
