<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%dialog_state}}".
 *
 * @property int         $id
 * @property string      $username
 * @property string|null $first_name            Имя пользователя
 * @property string|null $last_name             Фамилия пользователя
 * @property int         $dialog_id             ID Диалога
 * @property int|null    $last_msg_id           ID последнего сообщения
 * @property int|null    $last_msg_time         Время последнего сообщения
 * @property int|null    $quantity_reminder_msg Кол-во напоминалок
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
            [['username', 'dialog_id'], 'required'],
            [['dialog_id', 'last_msg_id', 'last_msg_time', 'quantity_reminder_msg'], 'integer'],
            [['username', 'first_name', 'last_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'dialog_id' => Yii::t('app', 'Dialog ID'),
            'last_msg_id' => Yii::t('app', 'Last Msg ID'),
            'last_msg_time' => Yii::t('app', 'Last Msg Time'),
            'quantity_reminder_msg' => Yii::t('app', 'Quantity Reminder Msg'),
        ];
    }
}
