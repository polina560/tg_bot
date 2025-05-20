<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%telegram_message_image}}".
 *
 * @property int                  $id
 * @property int                  $telegram_message_id ID сообщения
 * @property int|null             $serial_number       Порядковый номер
 *
 * @property-read TelegramMessage $telegramMessage
 */
class TelegramMessageImage extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%telegram_message_image}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['telegram_message_id'], 'required'],
            [['telegram_message_id', 'serial_number'], 'integer'],
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
            'serial_number' => Yii::t('app', 'Serial Number'),
        ];
    }

    final public function getTelegramMessage(): ActiveQuery
    {
        return $this->hasOne(TelegramMessage::class, ['id' => 'telegram_message_id']);
    }
}
