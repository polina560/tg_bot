<?php

namespace frontend\assets;

use common\assets\ViteAsset;

/**
 * Vue Frontend Application assets bundle
 *
 * @package frontend\assets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class VueAsset extends ViteAsset
{
    public array $vueJs = ['frontend/app.tsx'];

    public $depends = [FrontendAsset::class];
}