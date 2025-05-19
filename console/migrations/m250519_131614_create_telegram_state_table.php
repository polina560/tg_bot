<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_state}}`.
 */
class m250519_131614_create_telegram_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_state}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()->comment('Название состояния'),

        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%telegram_state}}');
    }
}
