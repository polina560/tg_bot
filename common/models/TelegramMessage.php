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
 * @property int|null                     $serial_number Порядковый номер
 * @property string|null                  $key   Команда для вызова
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
            [['text'], 'required'],
            [['serial_number'], 'integer'],
            [['text'], 'string'],
            [['key'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'text' => Yii::t('app', 'Text'),
            'serial_number' => Yii::t('app', 'Serial Number'),
            'key' => Yii::t('app', 'Key'),
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
