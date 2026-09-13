# Configuration

You can customise Shield’s settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `shield.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will enable submission logging:

```php
<?php

return [
    'logSubmissions' => true,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults.

## Configuration Options

::: reference
### `akismetApiKey`

**Type:** `string` · **Default:** `''`

The API key for Akismet.
:::

::: reference
### `akismetOriginUrl`

**Type:** `string` · **Default:** `''`

The origin URL for Akismet.
:::

::: reference
### `logSubmissions`

**Type:** `bool` · **Default:** `false`

Whether to log submissions.
:::

::: reference
### `enableContactFormSupport`

**Type:** `bool` · **Default:** `true`

Whether to enable support for the [Contact Form](https://plugins.craftcms.com/contact-form) plugin.
:::

::: reference
### `enableGuestEntriesSupport`

**Type:** `bool` · **Default:** `true`

Whether to enable support for the [Guest Entries](https://plugins.craftcms.com/guest-entries) plugin.
:::

::: reference
### `enableUserRegistrationSupport`

**Type:** `bool` · **Default:** `true`

Whether to enable support for Craft public user registrations.
:::


## Control Panel
You can also manage configuration settings through the Control Panel by visiting Settings → Shield.
