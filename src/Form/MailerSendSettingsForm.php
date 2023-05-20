<?php

namespace Drupal\mailersend\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Settings form of MailerSend integration module.
 */
class MailerSendSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailersend_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailersend.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mailersend.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('API Secret Key'),
      '#description' => $this->t('The api key generated on MailerSend dashboard.'),
      '#default_value' => $config->get('api_key') ?? '',
    ];

    $form['queue'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Queue outgoing messages'),
      '#description' => $this->t('When set, emails will not be immediately sent. Instead, they will be placed in a queue.'),
      '#default_value' => $config->get('queue') ?? FALSE,
    ];

    $form['html'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send HTML emails'),
      '#description' => $this->t('When set, HTML mails from Drupal will be sent. For more advanced templating and CSS inside email templates see @url', ['@url' => Link::fromTextAndUrl('Cerberus', Url::fromUri('https://www.cerberusemail.com'))->toString()]),
      '#default_value' => $config->get('html') ?? TRUE,
    ];

    // Get a list of all formats.
    $formats = filter_formats();
    $format_options = [];
    foreach ($formats as $format) {
      $filters = $format->getFilterTypes();
      // Skip any potential plain text filters.
      if ($format->get('format') === 'plain_text' || isset($filters['filter_html_escape'])) {
        continue;
      }
      $format_options[$format->get('format')] = $format->get('name');
    }

    $form['html_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('HTML email format'),
      '#description' => $this->t('Drupal filter format with which mail body is going to be rendered.'),
      '#options' => $format_options,
      '#default_value' => $config->get('html_format') ?? NULL,
      '#states' => [
        'visible' => [
          ':input[name="html"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="html"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('From address'),
      '#description' => $this->t('The sender email address if is different than Drupal site main email address. This address needs to be verified on MailerSend account.'),
      '#default_value' => $config->get('email') ?? $this->configFactory->get('system.site')->get('mail'),
    ];

    $form['analytics'] = [
      '#title' => $this->t('Analytics options'),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#tree' => TRUE,
    ];

    $form['analytics']['opens'] = [
      '#title' => $this->t('Track opens'),
      '#type' => 'checkbox',
      '#description' => $this->t('An invisible beacon embedded in your emails lets you see who opens your emails.'),
      '#default_value' => $config->get('analytics.opens') ?? FALSE,
    ];

    $form['analytics']['clicks'] = [
      '#title' => $this->t('Track clicks'),
      '#type' => 'checkbox',
      '#description' => $this->t('View all click activity including which links, how many times each was clicked and who clicked.'),
      '#default_value' => $config->get('analytics.clicks') ?? FALSE,
    ];

    $form['analytics']['content'] = [
      '#title' => $this->t('Track content (paid account only)'),
      '#type' => 'checkbox',
      '#description' => $this->t('The HTML of all sent emails will be stored in the email activity. Enabling this option might reveal sensitive information.The HTML of all sent emails will be stored in the email activity. Enabling this option might reveal sensitive information.'),
      '#default_value' => $config->get('analytics.content') ?? FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for API secret key. If missing throw error.
    if (empty($form_state->getValue('api_key'))) {
      $form_state->setError($form['api_key'], $this->t('You have not stored an API Secret Key.'));
    }

    if (!empty($form_state->getValue('html')) && empty($form_state->getValue('html_format'))) {
      $form_state->setError($form['html_format'], $this->t('You need to select HTML format if you want to send HTML mails'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('mailersend.settings');

    $config->set('api_key', $form_state->getValue('api_key'));
    $config->set('queue', $form_state->getValue('queue'));
    $config->set('html', $form_state->getValue('html'));
    $config->set('html_format', $form_state->getValue('html_format'));
    $config->set('email', $form_state->getValue('email'));
    $config->set('analytics', $form_state->getValue('analytics'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}

