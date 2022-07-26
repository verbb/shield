<?php
namespace verbb\shield\models;

use craft\base\Model;

class Log extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $type;
    public $email;
    public $author;
    public $content;
    public $flagged = false;
    public $ham = false;
    public $spam = false;
    public $data;
    public $dateCreated;
    public $dateUpdated;
    public $uid;


    // Public Methods
    // =========================================================================

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['type', 'email', 'author', 'content'], 'required'];

        return $rules;
    }
}
