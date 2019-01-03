<?php

return [
    '*' => [
        'akismetApiKey' => getenv('AKISMET_API_KEY'),
        'akismetOriginUrl' => 'https://plugindev.test',
        'logSubmissions' => false,
        'enableContactFormSupport' => true,
        'enableGuestEntriesSupport' => true,
        'enableSproutFormsSupport' => true,
        'enableCommentsSupport' => true,
    ],
    'dev' => [],
    'staging' => [],
    'production' => [],
];
