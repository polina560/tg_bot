<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%auth}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m241011_071521_create_auth_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        // drops foreign key for table `user`
        $this->dropForeignKey('{{%fk-social_network-user_id}}', '{{%social_network}}');
        // drops index for column `user_id`
        $this->dropIndex('{{%idx-social_network-user_id}}', '{{%social_network}}');
        $this->dropTable('{{%social_network}}');
        $this->createTable('{{%auth}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'source' => $this->string()->notNull(),
            'source_id' => $this->string()->notNull()
        ]);
        // creates index for column `source`
        $this->createIndex('{{%idx-auth-source}}', '{{%auth}}', ['source', 'source_id']);
        // creates index for column `user_id`
        $this->createIndex('{{%idx-auth-user_id}}', '{{%auth}}', 'user_id');
        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-auth-user_id}}',
            '{{%auth}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey('{{%fk-auth-user_id}}', '{{%auth}}');
        // drops index for column `user_id`
        $this->dropIndex('{{%idx-auth-user_id}}', '{{%auth}}');
        // drops index for column `source`
        $this->dropIndex('{{%idx-auth-source}}', '{{%auth}}');
        $this->dropTable('{{%auth}}');
        $this->createTable('{{%social_network}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            'social_network_id' => $this->string(10)->notNull()->comment('ID/тип соц сети'),
            'user_auth_id' => $this->string(300)->notNull()->comment('ID пользователя в соц. сети'),
            'access_token' => $this->string(300)->comment('Токен доступа'),
            'last_auth_date' => $this->integer(11)->comment('Дата последней авторизации'),
        ]);
        // creates index for column `user_id`
        $this->createIndex('{{%idx-social_network-user_id}}', '{{%social_network}}', 'user_id');
        // add foreign key for table `user`
        $this->addForeignKey(
            '{{%fk-social_network-user_id}}',
            '{{%social_network}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }
}
