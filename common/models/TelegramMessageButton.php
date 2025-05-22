<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%telegram_message_button}}".
 *
 * @property int                  $id
 * @property int                  $telegram_message_id ID сообщения
 * @property string|null          $text                Текст на кнопке
 * @property string               $btn_name            Имя кнопки
 * @property int|null             $serial_number       Порядковый номер
 *
 * @property-read TelegramMessage $telegramMessage
 */
class TelegramMessageButton extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%telegram_message_button}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['telegram_message_id', 'serial_number'], 'integer'],
            [['text', 'btn_name'], 'string', 'max' => 255],
            [['telegram_message_id'], 'exist', 'skipOnError' => true, 'targetClass' => TelegramMessage::class, 'targetAttribute' => ['telegram_message_id' => 'id']]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'telegram_message_id' => Yii::t('app', 'Telegram Message ID'),
            'text' => Yii::t('app', 'Text'),
            'btn_name' => Yii::t('app', 'Btn Name'),
            'serial_number' => Yii::t('app', 'Serial Number'),
        ];
    }

    final public function getTelegramMessage(): ActiveQuery
    {
        return $this->hasOne(TelegramMessage::class, ['id' => 'telegram_message_id']);
    }
}
