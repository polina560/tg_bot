<?php

use yii\db\Migration;

/**
 * Class m241205_081614_add_totp
 */
class m241205_081614_add_totp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->addColumn('{{%user_admin}}', 'totp_secret', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropColumn('{{%user_admin}}', 'totp_secret');
    }
}
