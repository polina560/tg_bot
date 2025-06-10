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
 * @property int|null $ans_1
 * @property int|null $ans_2
 * @property int|null $ans_3
 * @property int|null $ans_4
 * @property int|null $ans_5
 * @property int|null $remainder
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
            [['user_id', 'chat_id', 'last_msg_id', 'last_msg_time', 'quantity_reminder_msg', 'ans_1', 'ans_2', 'ans_3', 'ans_4', 'ans_5', 'remainder'], 'integer']
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
            'ans_1' => Yii::t('app', 'Ans 1'),
            'ans_2' => Yii::t('app', 'Ans 2'),
            'ans_3' => Yii::t('app', 'Ans 3'),
            'ans_4' => Yii::t('app', 'Ans 4'),
            'ans_5' => Yii::t('app', 'Ans 5'),
            'remainder' => Yii::t('app', 'Remainder'),
        ];
    }
}
