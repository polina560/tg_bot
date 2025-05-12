<?php

namespace common\components\behaviors;

use Random\RandomException;
use RuntimeException;
use yii\base\Behavior;
use yii\db\{ActiveRecord, BaseActiveRecord};

/**
 * Class GuidBehavior
 *
 * @package common\components\behaviors
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property ActiveRecord $owner
 */
class GuidBehavior extends Behavior
{
    public string $guidAttribute = 'guid';

    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'generateGuid',
        ];
    }

    /**
     * @param ActiveRecord $owner
     */
    public function attach($owner): void
    {
        if (!$owner->hasAttribute($this->guidAttribute)) {
            throw new RuntimeException(sprintf('Attribute "%s" not found', $this->guidAttribute));
        }
        parent::attach($owner);
    }

    /**
     * @throws RandomException
     */
    public function beforeInsert(): void
    {
        $this->owner->{$this->guidAttribute} = $this->generateGuid();
    }

    /**
     * @throws RandomException
     */
    private function generateGuid(): string
    {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        }
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // версия 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // варианты 8, 9, a, b
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
