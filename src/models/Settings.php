<?php
namespace verbb\shield\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public $akismetApiKey = '';
    public $akismetOriginUrl = '';
    public $logSubmissions = false;
    public $enableContactFormSupport = true;
    public $enableGuestEntriesSupport = true;
    public $enableSproutFormsSupport = false;
    public $enableCommentsSupport = false;


    // Public Methods
    // =========================================================================

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['akismetApiKey', 'akismetOriginUrl'], 'required'];
        $rules[] = [['akismetOriginUrl'], 'url'];

        return $rules;
    }
}
