<?php

namespace frontend\assets;

use common\assets\CommonAsset;
use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 *
 * @package frontend\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class FrontendAsset extends AssetBundle
{
    public $sourcePath = '@frontend/assets/source';

    public $css = ['styles/site.scss'];

    public $depends = [CommonAsset::class];
}