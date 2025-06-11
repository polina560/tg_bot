<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%telegram_message_button}}`.
 */
class m250611_083049_add_value_column_to_telegram_message_button_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%telegram_message_button}}', 'value', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%telegram_message_button}}', 'value');
    }
}
