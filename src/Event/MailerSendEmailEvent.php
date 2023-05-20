<?php

namespace Drupal\mailersend\Event;

use Drupal\commerce\EventBase;
use MailerSend\Helpers\Builder\EmailParams;

/**
 * Defines the cart order item add event.
 *
 * @see \Drupal\mailersend\Event\MailerSendEvents
 */
class MailerSendEmailEvent extends EventBase {

  /**
   * The email params.
   *
   * @var \MailerSend\Helpers\Builder\EmailParams
   */
  protected $emailParams;

  /**
   * The message id.
   *
   * @var string
   */
  protected $id;


  /**
   * The message key.
   *
   * @var string
   */
  protected $key;

  /**
   * The original message sent.
   *
   * @var array
   */
  protected $message;

  /**
   * Constructs a new MailerSendEmail.
   *
   * @param \MailerSend\Helpers\Builder\EmailParams $email_params
   *   The email params.
   * @param string $id
   *   The message id.
   * @param string $key
   *   The message key.
   * @param array $message
   *   The message.
   */
  public function __construct(EmailParams $email_params, string $id, string $key, array $message) {
    $this->emailParams = $email_params;
    $this->id = $id;
    $this->key = $key;
    $this->message = $message;
  }

  /**
   * Gets the email params.
   *
   * @return \MailerSend\Helpers\Builder\EmailParams
   *   The email params.
   */
  public function getEmailParams() {
    return $this->emailParams;
  }

  /**
   * Gets the message id.
   *
   * @return string
   *   The id of the message.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Gets the message key.
   *
   * @return string
   *   The key of the message.
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * Gets the message.
   *
   * @return array
   *   The message.
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Sets the email params.
   *
   * @param \MailerSend\Helpers\Builder\EmailParams $email_params
   *   The email params.
   *
   * @return $this
   */
  public function setEmailParams(EmailParams $email_params) {
    $this->emailParams = $email_params;
    return $this;
  }

}
