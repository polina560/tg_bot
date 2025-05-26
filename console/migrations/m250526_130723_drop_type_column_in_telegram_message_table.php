<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%type_column_in_telegram_message}}`.
 */
class m250526_130723_drop_type_column_in_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%telegram_message}}', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%telegram_message}}', 'type', $this->integer());
    }
}
