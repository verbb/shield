<?php
namespace verbb\shield\records;

use craft\db\ActiveRecord;

class Log extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%shield_logs}}';
    }
}
