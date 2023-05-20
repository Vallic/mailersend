<?php

namespace Drupal\mailersend\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\mailersend\MailerSendApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sends queued mail messages.
 *
 * @QueueWorker(
 *   id = "mailersend_queue",
 *   title = @Translation("Sends queued mail messages trough MailerSend API"),
 *   cron = {"time" = 60}
 * )
 */
class QueueProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The MailerSend API.
   *
   * @var \Drupal\mailersend\MailerSendApi
   */
  protected $mailerSendApi;

  /**
   * QueueProcessor constructor.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\mailersend\MailerSendApi $mailer_send_api
   *   The MailerSend API.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailerSendApi $mailer_send_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailerSendApi = $mailer_send_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mailersend.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->mailerSendApi->email($data);
  }

}
