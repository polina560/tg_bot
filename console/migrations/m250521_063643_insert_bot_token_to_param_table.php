<?php

use yii\db\Migration;

/**
 * Class m250521_063643_insert_bot_token_to_param_table
 */
class m250521_063643_insert_bot_token_to_param_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert(
            '{{%param}}',
            [
                'group',
                'key',
                'value',
                'description',
                'deletable',
                'is_active',
            ],
            [
                ['telegram_bot', 'telegram_bot_token', '', 'API токен телеграм-бота', false, true],
                ['telegram_bot', 'telegram_bot_username', '', 'API токен телеграм-бота', false, true]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%param}}', ['group' => 'telegram_bot', 'key' => 'telegram bot_token']);
        $this->delete('{{%param}}', ['group' => 'telegram_bot', 'key' => 'telegram bot_username']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250521_063643_insert_bot_token_to_param_table cannot be reverted.\n";

        return false;
    }
    */
}
