<?php

namespace Drupal\mailersend\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Send test mail.
 */
class MailerSendTestForm extends FormBase {

  /**
   * The mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * MailerSendTestForm constructor.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mailManager
   *   The mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(MailManagerInterface $mailManager, LanguageManagerInterface $languageManager, MessengerInterface $messenger, FileSystemInterface $file_system) {
    $this->mailManager = $mailManager;
    $this->languageManager = $languageManager;
    $this->messenger = $messenger;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('language_manager'),
      $container->get('messenger'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailersend_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['from_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From name'),
      '#maxlength' => 128,
    ];
    $form['to'] = [
      '#type' => 'email',
      '#title' => $this->t('To'),
      '#maxlength' => 128,
      '#required' => TRUE,
    ];
    $form['to_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('To Name'),
      '#maxlength' => 128,
    ];
    $form['reply_to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reply-To'),
      '#maxlength' => 128,
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#maxlength' => 128,
      '#required' => TRUE,
    ];
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#rows' => 20,
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send test message'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mailersend_settings = $this->config('mailersend.settings');

    $params = [
      'body' => $form_state->getValue('body')['value'] ?? 'empty body',
      'subject' => $form_state->getValue('subject'),
      'attachments' => [
        $this->fileSystem->realpath('core/themes/olivero/screenshot.png'),
      ],
    ];

    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $from = $mailersend_settings->get('email');
    $result = $this->mailManager->mail('mailersend', 'mailersend_test', $form_state->getValue('to'), $langcode, $params, $from);
    if (isset($result['result']) && $result['result']) {
      $this->messenger->addMessage($this->t('MailerSend test email sent from %from to %to.', [
        '%from' => $from,
        '%to' => $form_state->getValue('to'),
      ]));
    }
  }

}
