<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%dialog_state}}`.
 */
class m250527_131039_drop_dialog_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%dialog_state}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%dialog_state}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}
