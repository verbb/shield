# Configuration
Create a `shield.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

The below shows the defaults already used by Shield, so you don't need to add these options unless you want to modify the values.

```php
<?php

return [
    '*' => [
        'akismetApiKey' => '',
        'akismetOriginUrl' => '',
        'logSubmissions' => false,
        'enableContactFormSupport' => true,
        'enableGuestEntriesSupport' => true,
    ],
];
```

## Configuration options
- `akismetApiKey` - The API key for Akismet.
- `akismetOriginUrl` - The origin URL for Akismet.
- `logSubmissions` - Whether to log submissions.
- `enableContactFormSupport` - Whether to enable support for the [Contact Form](https://plugins.craftcms.com/contact-form) plugin.
- `enableGuestEntriesSupport` - Whether to enable support for the [Guest Entries](https://plugins.craftcms.com/guest-entries) plugin.

## Control Panel
You can also manage configuration settings through the Control Panel by visiting Settings → Shield.
