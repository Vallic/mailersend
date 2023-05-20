<?php

namespace Drupal\mailersend\Event;

/**
 * Defines events for the mailersend module.
 */
final class MailerSendEvents {

  /**
   * Name of the event fired before sending mail with MailerSendApi.
   *
   * @Event
   *
   * @see \Drupal\mailersend\Event\MailerSendEmailEvent
   */
  const MAILERSEND_EMAIL = 'mailersend.email';

}
