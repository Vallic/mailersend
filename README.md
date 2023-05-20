CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Send Test Email
 * HTML Emails
 * Advanced options

INTRODUCTION
------------

The MailerSend module integrates Drupal mail system with MailerSend,
a transactional email delivery API. This module allows transactional emails
from Drupal to be sent with MailerSend API.

Learn more at [mailersend.com](https://www.mailersend.com/)


REQUIREMENTS
------------
This module requires the [Mail System module](https://drupal.org/project/mailsystem)

It also relies upon the [MailerSend PHP SDK](https://github.com/mailersend/mailersend-php)


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module,
ideally managed by [Composer](https://www.drupal.org/docs/extending-drupal/installing-modules#s-add-a-module-with-composer)

 * Enable the module from Admin » Extend or with
`drush en mailersend`.

CONFIGURATION
-------------

1. [Acquire a MailerSend API key](https://app.mailersend.com/domains)
2. **Set MailerSend API Key** in Admin » Configuration » Web Services
(`admin/config/services/mailersend`).

Remaining options:

 * Queue Outgoing Messages
   - Don't send mail directly, but adds it to the queue. During cron X number of
    items from queue is going to be processed and mails sent.

 * From address
    - The email address that emails should be sent from

 * HTML mails
   - Add support to send HTML mails without any other module

 * HTML format
   - An input format to apply to the message body
     before sending emails.

 * Analytics Options
    - Track opens: Toggles open tracking for messages
    - Track clicks: Toggles click tracking for messages
    - Track content: The HTML of all sent emails will be stored in the email
       activity (only applicable for paid accounts).

3. **Update Mail System settings**:
  - MailerSend Mail interface is enabled by using the
    [Mail System module](http://drupal.org/project/mailsystem).
  - Go to the [Mail System configuration page](admin/config/system/mailsystem) to start
    sending emails through MailerSend. Once you do this, you'll see
    a list of the module keys that are using MailerSend listed near
    the top of the MailerSend settings page.
  - Once you set the site-wide default (and any other listed module classes)
    to MailerSend Mailer, your site will immediately start using
    MailerSend to deliver all outgoing email.
  - Module/key pairs: The key is optional, not every module or email uses a
    key. That is why on the mail system settings page, you may see some modules
    listed without keys.
    For more details about this, see the mail system configuration page
    (admin/config/system/mailsystem).

SEND TEST EMAIL
---------------
The Send Test Email tab sends an email manually, through the MailerSend
and the configured options.

HTML EMAILS
---------------
When you enable HTML emails to be sent via MailerSend integration,
default template is located inside folder templates called
`mailersend-message.html.twig`.

Best practice would be that you copy this template in your theme
and adjust styling according to your needs.


ADVANCED OPTIONS
----------------

1. Altering MailerSend parameters via event subscriber
On each mail sent event `MailerSendEmailEvent` is fired.
Via event subscriber you can alter parameters for MailerSend before call
is made to MailerSend API.

See official PHP SDK for more detailed information what options are available
https://github.com/mailersend/mailersend-php#email-api

2. MailerSend templates and personalization
There is no out of box solution provided. You can via `MailerSendEmailEvent`
sent template id or personalization variables.
