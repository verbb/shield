<?php
namespace verbb\shield\migrations;

use craft\db\Migration;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%shield_logs}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(25),
            'email' => $this->string(),
            'author' => $this->string(100),
            'content' => $this->text(),
            'flagged' => $this->boolean(),
            'ham' => $this->boolean(),
            'spam' => $this->boolean(),
            'data' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%shield_logs}}');

        return false;
    }
}
