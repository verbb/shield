# Shield plugin for Craft CMS
Use the power of [Akismet](https://akismet.com) to **Shield** your [Craft CMS](https://craftcms.com) website from annoying Spam. Protects the following:

- [Contact Form](https://plugins.craftcms.com/contact-form) plugin
- [Guest Entries](https://plugins.craftcms.com/guest-entries) plugin

## Installation
You can install Shield via the plugin store, or through Composer.

### Craft Plugin Store
To install **Shield**, navigate to the _Plugin Store_ section of your Craft control panel, search for `Shield`, and click the _Try_ button.

### Composer
You can also add the package to your project using Composer and the command line.

1. Open your terminal and go to your Craft project:
```shell
cd /path/to/project
```

2. Then tell Composer to require the plugin, and Craft to install it:
```shell
composer require verbb/shield && php craft plugin/install shield
```

## Contact Form
If you’re using [Contact Form](https://github.com/craftcms/contact-form) by [P&T](https://pixelandtonic.com/), Shield can help you protect your forms against annoying Spam

- Follow the [Contact Form](https://github.com/craftcms/contact-form) setup guide, if you haven’t already.
- Make sure Contact Form support is enabled in your [Shield Config](https://github.com/verbb/shield/blob/craft-4/src/config.php)
- That is it, all future submissions will be monitored by Shield 🔥

## Guest Entries
Shield can help you protect against Spam in your guest entries.

1. Follow the [Guest Entries](https://github.com/craftcms/guest-entries) setup guide, if you haven’t already
2. Make sure [Guest Entries](https://github.com/craftcms/guest-entries) support is enabled in your [Shield Config](https://github.com/verbb/shield/blob/craft-4/src/config.php)
3. Add the `hidden input` fields to your form so **Shield** knows what to validate

## Setup [#](#setup "Setup")

To setup Shield to protect your guest entries, the following hidden fields must be defined.

```html
<input type="hidden" name="shield[emailField]" value="{guestEntryEmailFieldHandle}">
<input type="hidden" name="shield[authorField]" value="{guestEntryFullNameFieldHandle}">
<input type="hidden" name="shield[contentField]" value="{guestEntryBodyFieldHandle}">
```

These fields need to be defined so that **Shield** knows what attributes to look for in the **guest entry** in order to prepare the data to pass along to Akismet for validation.

When the form is submitted and the **Guest Entry** is validated, the entry will be handed to **Shield** which will then grab the `shield[emailField|authorField|contentField]` values containing **twig** placeholders which will then be **replaced** by attribute values found in the [EntryModel](http://buildwithcraft.com/docs/templating/entrymodel)

_Note that the `emailField` and `authorField` are not required._

## Credits
Originally created by [Selvin Ortiz](https://github.com/selvindev).

## Show your Support
Shield is licensed under the MIT license, meaning it will always be free and open source – we love free stuff! If you'd like to show your support to the plugin regardless, [Sponsor](https://github.com/sponsors/verbb) development.

<h2></h2>

<a href="https://verbb.io" target="_blank">
    <img width="100" src="https://verbb.io/assets/img/verbb-pill.svg">
</a>
