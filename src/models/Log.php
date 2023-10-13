<?php
namespace verbb\shield\models;

use craft\base\Model;

use DateTime;

class Log extends Model
{
    // Public Properties
    // =========================================================================

    public ?int $id = null;
    public ?string $type = null;
    public ?string $email = null;
    public ?string $author = null;
    public mixed $content = null;
    public bool $flagged = false;
    public bool $ham = false;
    public bool $spam = false;
    public ?string $data = null;
    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
    public ?string $uid = null;


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['type', 'email', 'author', 'content'], 'required'];

        return $rules;
    }
}
