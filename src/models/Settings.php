<?php
namespace selvinortiz\shield\models;

use craft\base\Model;

class Settings extends Model
{
    public $akismetApiKey = '';
    public $akismetOriginUrl = '';
    public $logSubmissions = false;
    public $enableContactFormSupport = true;
    public $enableGuestEntriesSupport = true;
    public $enableSproutFormsSupport = false;
    public $enableCommentsSupport = false;

    public function rules()
    {
        $rules = [
            [['akismetApiKey', 'akismetOriginUrl'], 'required'],
            [['akismetOriginUrl'], 'url'],
        ];

        return array_merge(parent::rules(), $rules);
    }
}
