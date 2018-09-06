<?php
namespace selvinortiz\shield\records;

use craft\db\ActiveRecord;
use selvinortiz\enums\CommentType;

class LogRecord extends ActiveRecord
{
    /**
     * @var int
     */
    public $id;

    /**
     * @see CommentType
     *
     * @var string One of CommentType::*
     */
    public $type;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $author;

    /**
     * @var string
     */
    public $content;

    /**
     * @var bool
     */
    public $flagged;

    /**
     * @var bool
     */
    public $ham;

    /**
     * @var bool
     */
    public $spam;

    /**
     * @var array
     */
    public $data;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%shield_logs}}';
    }
}
