<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%telegram_image}}".
 *
 * @property int         $id
 * @property string|null $image
 * @property string|null $key
 * @property int|null    $serial_number
 */
class TelegramImage extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%telegram_image}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['serial_number'], 'integer'],
            [['image', 'key'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'image' => Yii::t('app', 'Image'),
            'key' => Yii::t('app', 'Key'),
            'serial_number' => Yii::t('app', 'Serial Number'),
        ];
    }
}
