<?php

namespace common\modules\user\models;

use common\models\AppActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%auth}}".
 *
 * @property int       $id
 * @property int       $user_id
 * @property string    $source
 * @property string    $source_id
 *
 * @property-read User $user
 */
class Auth extends AppActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%auth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'source', 'source_id'], 'required'],
            ['user_id', 'integer'],
            [['source', 'source_id'], 'string', 'max' => 255],
            [
                'user_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
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
            'source' => Yii::t('app', 'Source'),
            'source_id' => Yii::t('app', 'Source ID'),
        ];
    }

    final public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
