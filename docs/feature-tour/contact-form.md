# Contact Form

Shield checks submissions from Craft's Contact Form plugin with Akismet before the message is sent. Configure the [Akismet settings](docs:get-started/configuration) and leave `enableContactFormSupport` enabled for this integration.

The Contact Form plugin must be installed and enabled, with a working form on your site. Shield registers its check with the plugin's before-send event, so this integration does not require the custom field mapping used by Guest Entries.

Submit a test message through your form using an address you control. Confirm the normal successful submission and delivery path, then review Shield's logs if you have enabled submission logging. Shield marks detected spam on the Contact Form event; your form should continue to show the response appropriate to that integration rather than exposing internal validation details to visitors.

If the check is not running, verify both plugins are enabled and that your form submits through Contact Form rather than a separate custom controller.
