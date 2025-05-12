<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m250423_084542_add_auth_key_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%user}}', 'auth_key', $this->string(32)->unique()->comment('Ключ авторизации'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%user}}', 'auth_key');
    }
}
