<?php

namespace api\components\requestLimiter;

use api\modules\v1\controllers\AppController;
use Yii;
use yii\base\{ActionEvent, Behavior, Controller};

/**
 * Class RequestLimiter
 *
 * @property-read AppController $owner
 *
 * @package api\components\requestLimiter
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class RequestLimiter extends Behavior
{
    public array $actions = [];
    public array $blockingLimits = [
        'example' => [ // Защита от перебора
            [
                'max_errors' => 3, // Кол-во допустимых ошибок за
                'period' => 15, // период в котором ожидаются неверные значения
                'cooldown' => 30 // Задержка блокировки (сек)
            ],
        ],
    ];


    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'checkBlock',
            Controller::EVENT_AFTER_ACTION => 'clearBlock'
        ];
    }

    /**
     * @throws RequestLimiterException
     */
    public function checkBlock(ActionEvent $event): void
    {
        if (in_array($event->action->id, $this->actions)) {
            $cacheKey = $this->getCacheKey($event->action->id);
            if (Yii::$app->cache->exists($cacheKey . '_cooldown')) {
                throw new RequestLimiterException(message: 'Вы заблокированы');
            }
        }
    }

    public function addWrongValue(mixed $value): void // это для увеличения счетчика
    {
        $actionId = Yii::$app->controller->action->id;
        if (in_array($actionId, $this->actions)) {

            $cacheKey = $this->getCacheKey($actionId);
            $requests = Yii::$app->cache->get($cacheKey) ?: [];

            // Добавляем новое значение, если его там нет
            if (!in_array($value, $requests)) {
                $requests[] = $value;
            }

            $blockingLevels = $this->blockingLimits[$actionId];

            if (!empty($blockingLevels)) {
                $reversed = array_reverse($blockingLevels);
                foreach ($reversed as $level) {
                    if (count($requests) < $level['max_errors']) {
                        $period = $level['period'];
                        break;
                    }
                }
                foreach ($blockingLevels as $level) {
                    if (count($requests) >= $level['max_errors']) {
                        $cooldown = $level['cooldown'];
                        Yii::$app->cache->set($cacheKey . '_cooldown', true, $cooldown);
                        break;
                    }
                }
            }
            Yii::$app->cache->set($cacheKey, $requests, $period ?? null);
        }
    }

    public function clearBlock(ActionEvent $event): void
    {
        // Если экшен успешный - то очищаем блокировку
        if (in_array($event->action->id, $this->actions) && $event->action->controller->response->isSuccessful) {
            $cacheKey = $this->getCacheKey($event->action->id);
            Yii::$app->cache->delete($cacheKey);
            Yii::$app->cache->delete($cacheKey . '_cooldown');
        }
    }

    protected function getCacheKey(string $actionId): string
    {
        return "rl_{$actionId}_" . Yii::$app->request->userIP . '_' .
            str_replace(
                ['/', '.', ' ', ';', '(', ')', ','],
                ['', '_', '_', '', '', '', ''],
                strtolower(Yii::$app->request->userAgent)
            );
    }
}
