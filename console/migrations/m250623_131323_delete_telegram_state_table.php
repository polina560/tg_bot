<?php

use yii\db\Migration;

/**
 * Class m250623_131323_delete_telegram_state_table
 */
class m250623_131323_delete_telegram_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('{{%telegram_state}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%telegram_state}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()->comment('Название состояния'),

        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250623_131323_delete_telegram_state_table cannot be reverted.\n";

        return false;
    }
    */
}
