<?php

namespace common\modules\mail;

use Yii;
use yii\base\{Module};
use yii\i18n\PhpMessageSource;

/**
 * mail module definition class
 *
 * @package mail
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Mail extends Module
{
    public const MODULE_MESSAGES = 'modules/mail/';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\mail\controllers';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/common/modules/mail/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_MESSAGES . 'error' => 'error.php',
                self::MODULE_MESSAGES . 'success' => 'success.php'
            ]
        ];
    }
}
