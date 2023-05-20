<?php

namespace Drupal\mailersend\Plugin\Mail;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\mailersend\MailerSendApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @file
 * Implements Drupal MailSystemInterface.
 *
 * @Mail(
 *   id = "mailersend",
 *   label = @Translation("MailerSend"),
 *   description = @Translation("Sends the message using MailerSend API.")
 * )
 */
class MailerSendMail implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The MailerSend API.
   *
   * @var \Drupal\mailersend\MailerSendApi
   */
  protected $mailerSendApi;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * MailerSendMail constructor.
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue factory service.
   * @param \Drupal\mailersend\MailerSendApi $mailer_send_api
   *   The MailerSend API.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, QueueFactory $queueFactory, MailerSendApi $mailer_send_api, RequestStack $request_stack, RendererInterface $renderer) {
    $this->configFactory = $config_factory;
    $this->queueFactory = $queueFactory;
    $this->mailerSendApi = $mailer_send_api;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
    $this->config = $config_factory->get('mailersend.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('queue'),
      $container->get('mailersend.api'),
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message): array {
    // Join message array.
    if (is_array($message['body'])) {
      $message['body'] = Html::transformRootRelativeUrlsToAbsolute(implode("\n\n", $message['body']), $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost());
    }

    // If we need to send HTML mail, prepare html_body here.
    if ($this->config->get('html')) {
      $module = $message['module'];
      $key = $message['key'];
      $subject = $message['params']['subject'] ?? $message['subject'];
      $body = $message['params']['body'] ?? $message['body'];

      $format = $this->config->get('html_format') ?? filter_fallback_format();
      $message['html_body'] = check_markup($body, $format, $message['langcode'] ?? '');
      $body = [
        '#theme' => 'mailersend_message',
        '#module' => $module,
        '#key' => $key,
        '#subject' => $subject,
        '#body' => $message['html_body'],
        '#site_name' => $this->configFactory->get('system.site')->get('name'),
        '#langcode' => $message['langcode'],
      ];

      $message['html_body'] = $this->renderer->renderPlain($body);

    }
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    // Queue item.
    if ($this->config->get('queue')) {
      return (bool) $this->queueFactory->get('mailersend_queue')->createItem($message);
    }

    // Send directly mail.
    return $this->mailerSendApi->email($message);
  }

}
