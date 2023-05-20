<?php

namespace Drupal\mailersend;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\mailersend\Event\MailerSendEmailEvent;
use Drupal\mailersend\Event\MailerSendEvents;
use Drupal\user\UserInterface;
use MailerSend\Exceptions\MailerSendException;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\MailerSend;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The MailerSend API integration.
 */
class MailerSendApi {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The mailer send.
   *
   * @var \MailerSend\MailerSend
   */
  protected $mailerSend;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * MailerSendApi constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger channel factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_channel_factory, EventDispatcherInterface $event_dispatcher) {
    $this->configFactory = $config_factory;
    $this->config = $config_factory->get('mailersend.settings');
    $this->logger = $logger_channel_factory->get('mailersend');
    $this->mailerSend = new MailerSend(['api_key' => $this->config->get('api_key')]);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Send mail using MailerSend API.
   *
   * @param array $message
   *   The mail message.
   *
   * @return bool
   *   Return true if sent, otherwise false.
   */
  public function email(array $message) {
    $site_info = $this->configFactory->get('system.site');
    $from_address = $this->config->get('email') ?? $site_info->get('mail');

    $plain_text = $message['params']['body'] ?? $message['body'];
    $html_text = $message['html_body'] ?? $plain_text;

    $recipient_name = $message['to'];
    if (!empty($message['params']['account']) && $message['params']['account'] instanceof UserInterface) {
      $recipient_name = $message['params']['account']->getAccountName();
    }

    $recipient = [new Recipient($message['to'], $recipient_name)];

    $email_params = (new EmailParams())
      ->setFrom($from_address)
      ->setFromName($site_info->get('name'))
      ->setRecipients($recipient)
      ->setSubject($message['params']['subject'] ?? $message['subject'])
      ->setHtml($html_text)
      ->setText($plain_text);

    if (!empty($message['reply-to'])) {
      $email_params->setReplyTo($message['reply-to']);
    }

    if (!empty($message['reply-to-name'])) {
      $email_params->setReplyToName($message['reply-to-name']);
    }

    foreach ($message['headers'] as $key => $value) {
      $key = strtolower($key);

      if (!empty($value)) {
        switch ($key) {
          case 'cc':
            $email_params->setCc([new Recipient($value, $value)]);
            break;

          case 'bcc':
            $email_params->setBcc([new Recipient($value, $value)]);
            break;

          case 'reply-to':
            $email_params->setReplyTo($value);
            break;
        }
      }
    }

    $message_attachments = $message['params']['attachments'] ?? $message['attachments'] ?? [];
    $attachments = [];
    foreach ($message_attachments as $attachment) {
      if (is_string($attachment)) {
        $file_name = pathinfo($attachment);
        $attachments[] = new Attachment(file_get_contents($attachment), $file_name['basename'] ?? 'Attachment');
      }
    }

    if ($attachments) {
      $email_params->setAttachments($attachments);
    }

    if (!empty($message['params']['send_at'])) {
      $email_params->setSendAt($message['params']['send_at']);
    }

    // Allow altering of the mail.
    $event = new MailerSendEmailEvent($email_params, $message['id'], $message['key'], $message);
    $this->eventDispatcher->dispatch($event, MailerSendEvents::MAILERSEND_EMAIL);
    $email_params = $event->getEmailParams();

    try {
      $response = $this->mailerSend->email->send($email_params);
    }
    catch (MailerSendException $exception) {
      $this->logger->debug($exception->getMessage());
    }

    return (isset($response['status_code']) && $response['status_code'] >= 200 && $response['status_code'] <= 204);
  }

}
