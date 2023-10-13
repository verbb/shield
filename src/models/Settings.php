<?php
namespace verbb\shield\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public string $akismetApiKey = '';
    public string $akismetOriginUrl = '';
    public bool $logSubmissions = false;
    public bool $enableContactFormSupport = true;
    public bool $enableGuestEntriesSupport = true;
    public bool $enableSproutFormsSupport = false;
    public bool $enableCommentsSupport = false;


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['akismetApiKey', 'akismetOriginUrl'], 'required'];
        $rules[] = [['akismetOriginUrl'], 'url'];

        return $rules;
    }
}
