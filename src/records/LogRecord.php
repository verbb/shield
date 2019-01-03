<?php
namespace selvinortiz\shield\records;

use craft\db\ActiveRecord;

class LogRecord extends ActiveRecord
{
    public function rules()
    {
        $rules = [
            [['type', 'email', 'author', 'content'], 'required']
        ];

        return array_merge(parent::rules(), $rules);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%shield_logs}}';
    }
}
