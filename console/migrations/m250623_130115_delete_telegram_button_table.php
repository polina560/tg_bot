<?php

use yii\db\Migration;

/**
 * Class m250623_130115_delete_message_button_table
 */
class m250623_130115_delete_telegram_button_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('{{%telegram_button}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%telegram_button}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'key' => $this->string(),
            'serial_number' => $this->integer()
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_130115_delete_message_button_table cannot be reverted.\n";

        return false;
    }
    */
}
