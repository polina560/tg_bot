<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%telegram_message}}".
 *
 * @property int                          $id
 * @property int                          $type          Тип состояния
 * @property string                       $text          Текст сообщения
 * @property int|null                     $serial_number порядковый номер
 * @property string|null                  $callback_data
 *
 * @property-read TelegramMessageButton[] $telegramMessageButtons
 * @property-read TelegramMessageImage[]  $telegramMessageImages
 */
class TelegramMessage extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%telegram_message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type', 'text'], 'required'],
            [['type', 'serial_number'], 'integer'],
            [['text'], 'string'],
            [['callback_data'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'text' => Yii::t('app', 'Text'),
            'serial_number' => Yii::t('app', 'Serial Number'),
            'callback_data' => Yii::t('app', 'Callback Data'),
        ];
    }

    final public function getTelegramMessageButtons(): ActiveQuery
    {
        return $this->hasMany(TelegramMessageButton::class, ['telegram_message_id' => 'id']);
    }

    final public function getTelegramMessageImages(): ActiveQuery
    {
        return $this->hasMany(TelegramMessageImage::class, ['telegram_message_id' => 'id']);
    }
}
