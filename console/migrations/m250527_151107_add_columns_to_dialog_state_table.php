<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%dialog_state}}`.
 */
class m250527_151107_add_columns_to_dialog_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%dialog_state}}', 'ans_1', $this->integer());
        $this->addColumn('{{%dialog_state}}', 'ans_2', $this->integer());
        $this->addColumn('{{%dialog_state}}', 'ans_3', $this->integer());
        $this->addColumn('{{%dialog_state}}', 'ans_4', $this->integer());
        $this->addColumn('{{%dialog_state}}', 'ans_5', $this->integer());

        $this->addColumn('{{%dialog_state}}', 'remainder', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dialog_table}}', 'ans_1');
        $this->dropColumn('{{%dialog_table}}', 'ans_2');
        $this->dropColumn('{{%dialog_table}}', 'ans_3');
        $this->dropColumn('{{%dialog_table}}', 'ans_4');
        $this->dropColumn('{{%dialog_table}}', 'ans_5');

        $this->dropColumn('{{%dialog_state}}', 'remainder');
    }
}
