<?php

namespace api\modules\telegram;

use common\models\TelegramMessage;
use common\models\TelegramMessageImage;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
use Yii;
use yii\base\Module;

class TelegramBot extends Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\telegram\controllers';

    /**
     * @param TelegramMessage        $text
     * @param TelegramMessageImage[] $images
     */
    static public function imageToArray(array $text, int $price = 0, int $reminder = 0)
    {
        if ($images = TelegramMessageImage::find()->where(['telegram_message_id' => $text->id])->all()) {
            foreach ($images as $index => $image) {
                if ($index == 0) {
                    $media_group[] = new InputMediaPhoto(
                        ['media' => Yii::getAlias('@htdocs') . $image->image, 'caption' => sprintf($text->text, $price, $reminder)]
                    );
                } else {
                    $media_group[] = new InputMediaPhoto(['media' => Yii::getAlias('@htdocs') . $image->image]);
                }
            }
            return $media_group;
        } else {
            return [];
        }
    }

}
