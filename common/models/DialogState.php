<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%dialog_state}}".
 *
 * @property int      $id
 * @property int|null $user_id
 * @property int|null $chat_id               ID Диалога
 * @property int|null $last_msg_id           ID последнего сообщения
 * @property int|null $last_msg_time         Время последнего сообщения
 * @property int|null $quantity_reminder_msg Кол-во напоминалок
 */
class DialogState extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%dialog_state}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'chat_id', 'last_msg_id', 'last_msg_time', 'quantity_reminder_msg'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'chat_id' => Yii::t('app', 'Chat ID'),
            'last_msg_id' => Yii::t('app', 'Last Msg ID'),
            'last_msg_time' => Yii::t('app', 'Last Msg Time'),
            'quantity_reminder_msg' => Yii::t('app', 'Quantity Reminder Msg'),
        ];
    }
}
