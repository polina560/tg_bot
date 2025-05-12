<?php

namespace admin\modules\modelExportImport\widgets;

use yii\bootstrap5\{Modal, Widget};

class ImportWidget extends Widget
{
    public ?string $action = null;

    public function run(): void
    {
        Modal::begin([
            'title' => 'Импортировать запись(и)',
            'toggleButton' => [
                'label' => 'Импортировать запись(и)',
                'class' => 'btn btn-success'
            ]
        ]);
        echo $this->render('importForm', ['action' => $this->action]);
        Modal::end();
    }
}
