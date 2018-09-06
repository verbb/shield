<?php
namespace selvinortiz\shield\migrations;

use craft\db\Migration;

/**
 * Class Install
 *
 * @package selvinortiz\shield\migrations
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%shield_logs}}', [
            'id'          => $this->primaryKey(),
            'email'       => $this->string(),
            'author'      => $this->string(100),
            'content'     => $this->text(),
            'flagged'     => $this->boolean(),
            'ham'         => $this->boolean(),
            'spam'        => $this->boolean(),
            'data'        => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%shield_logs}}');
    }
}
