<?php

namespace common\models;

use common\models\AppActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%telegram_button}}".
 *
 * @property int         $id
 * @property string|null $title
 * @property string|null $key
 * @property int|null    $serial_number
 */
class TelegramButton extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%telegram_button}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['serial_number'], 'integer'],
            [['title', 'key'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'key' => Yii::t('app', 'Key'),
            'serial_number' => Yii::t('app', 'Serial Number'),
        ];
    }
}
