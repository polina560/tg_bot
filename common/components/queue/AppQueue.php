<?php

namespace common\components\queue;

use common\components\helpers\UserFileHelper;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\mutex\MysqlMutex;
use yii\queue\db\Queue;

/**
 * Class AppQueue
 *
 * @package common\components\jobs
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AppQueue extends Queue
{
    public $mutex = MysqlMutex::class;

    public $mutexTimeout = 5;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'refreshDb' => RefreshDbBehavior::class
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function release($payload): void
    {
        $this->db->close();
        $this->db->open();
        parent::release($payload);
    }
}
