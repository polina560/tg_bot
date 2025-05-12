<?php

namespace common\models;

use admin\widgets\{ckfinder\CKFinderInputFile,
    input\DatePicker,
    input\DateTimePicker,
    input\TimePicker,
    input\YesNoSwitch};
use common\enums\{Boolean, ParamType};
use common\models\traits\ObjectStorageTrait;
use Exception;
use kartik\color\ColorInput;
use kartik\editable\Editable;
use Yii;
use yii\bootstrap5\Html;

/**
 * This is the model class for table "{{%param}}".
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property int                         $id          [int] ID
 * @property string                      $group       [varchar(255)] Группа параметров
 * @property string                      $key         [varchar(255)] Название параметра
 * @property string                      $value       [varchar(255)] Значение
 * @property string                      $description [varchar(255)] Описание параметра
 * @property int                         $deletable   [tinyint(1)] Разрешено ли удалять параметр из панели администратора
 * @property int                         $is_active   [tinyint(1)] Доступен ли параметр во внешнем API
 * @property string                      $type        [varchar(255)] Тип значения параметра
 *
 * @property-read null|bool|string|array $columnValue
 * @property-read null|string|array      $inputWidget
 * @property-read null|string            $fileUrl
 */
class Param extends AppActiveRecord
{
    use ObjectStorageTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%param}}';
    }

    /**
     * Получить полный список параметров
     *
     * @param string|null $group Фильтр по группе
     *
     * @throws Exception
     */
    public static function getAll(string $group = null): array
    {
        $items = self::find()->filterWhere(['group' => $group, 'is_active' => Boolean::Yes->value])->asArray()->all();
        $result = [];
        foreach ($items as $item) {
            if (!isset($result[$item['group']])) {
                $result[$item['group']] = [];
            }
            try {
                $value = match (ParamType::from($item['type'])) {
                    ParamType::Image, ParamType::File => self::fileUrl($item['value']),
                    ParamType::Switch => (bool)$item['value'],
                    ParamType::Date => Yii::$app->formatter->asDate($item['value']),
                    ParamType::Datetime => Yii::$app->formatter->asDatetime($item['value']),
                    ParamType::Time => Yii::$app->formatter->asDuration($item['value']),
                    default => $item['value'],
                };
            } catch (Exception $e) {
                Yii::warning($e->getMessage(), __METHOD__);
                $value = $item['value'];
            }
            $result[$item['group']][$item['key']] = $value;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['key', 'required'],
            [['deletable', 'is_active'], 'boolean'],
            [['group', 'key', 'value', 'description'], 'string', 'max' => 255],
            ['type', 'string', 'max' => 10],
            ParamType::validator('type'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'group' => Yii::t('app', 'Group'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'description' => Yii::t('app', 'Description'),
            'type' => Yii::t('app', 'Type'),
            'deletable' => Yii::t('app', 'Deletable'),
            'is_active' => Yii::t('app', 'Is Active'),
        ];
    }

    /**
     * @return array|bool|string|null
     * @throws Exception
     */
    final public function getColumnValue(): bool|array|string|null
    {
        try {
            $value = match (ParamType::from($this->type)) {
                ParamType::Image => $this->fileUrl
                    ? Html::img($this->fileUrl, ['style' => 'max-width:150px;max-height:50px;', 'alt' => ''])
                    : null,
                ParamType::File => $this->fileUrl
                    ? Html::a(basename($this->value), $this->fileUrl, ['target' => '_blank'])
                    : null,
                ParamType::Switch => Boolean::from($this->value)->coloredDescription(),
                ParamType::Color => Yii::$app->formatter->asColor($this->value),
                ParamType::Date => Yii::$app->formatter->asDate($this->value),
                ParamType::Datetime => Yii::$app->formatter->asDatetime($this->value),
                ParamType::Time => Yii::$app->formatter->asDuration($this->value),
                default => Html::encode($this->value),
            };
        } catch (Exception $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            $value = $this->value;
        }
        return $value;
    }

    /**
     * Получить имя класса виджета или его полный конфиг
     *
     * Выбор виджета для поля ввода основан на типе параметра
     */
    final public function getInputWidget(): array|string|null
    {
        try {
            $widget = match (ParamType::from($this->type)) {
                ParamType::File => [
                    'class' => CKFinderInputFile::class,
                    'resourceType' => 'Files',
                    'isImage' => false,
                ],
                ParamType::Image => CKFinderInputFile::class,
                ParamType::Color => [
                    'class' => ColorInput::class,
                    'useNative' => true,
                ],
                ParamType::Switch => YesNoSwitch::class,
                ParamType::Date => DatePicker::class,
                ParamType::Datetime => DateTimePicker::class,
                ParamType::Time => TimePicker::class,
                ParamType::Number => [
                    'inputType' => Editable::INPUT_HTML5,
                    'options' => ['type' => 'number'],
                ],
                default => Editable::INPUT_TEXTAREA,
            };
        } catch (Exception $e) {
            Yii::warning($e->getMessage(), __METHOD__);
            $widget = null;
        }
        return $widget;
    }

    private string|null $_fileUrl;

    final public function getFileUrl(): ?string
    {
        if (isset($this->_fileUrl)) {
            return $this->_fileUrl;
        }
        if (!empty($this->value)) {
            return $this->_fileUrl = self::fileUrl($this->value);
        }
        return $this->_fileUrl = null;
    }
}
