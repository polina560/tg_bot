<?php

namespace admin\modules\modelExportImport\actions;

use admin\modules\modelExportImport\{behaviors\ExportImportBehavior, models\ImportModel};
use common\components\exceptions\ModelSaveException;
use Throwable;
use Yii;
use yii\base\Action;
use yii\db\{ActiveRecord, StaleObjectException};
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\{Response, UploadedFile};

/**
 * Class ImportAction
 *
 * @package modelExportImport\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ImportAction extends Action
{
    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    public function run(): Response
    {
        $model = new ImportModel();
        if (!$model->load(Yii::$app->request->post())) {
            Yii::$app->session->addFlash('error', 'Ошибка загрузки файла');
            return $this->controller->redirect(Yii::$app->request->referrer);
        }
        $file = UploadedFile::getInstance($model, 'file');
        if (!$file) {
            Yii::$app->session->addFlash('error', 'Ошибка загрузки файла');
            return $this->controller->redirect(Yii::$app->request->referrer);
        }
        $data = Json::decode(file_get_contents($file->tempName));
        if (!empty($data)) {
            if (array_is_list($data)) {
                foreach ($data as $datum) {
                    $this->importModel($datum);
                }
            } else {
                $this->importModel($data);
            }
            Yii::$app->session->addFlash('success', 'Данные импортированы');
        }
        return $this->controller->redirect(Yii::$app->request->referrer);
    }

    /**
     * @throws Throwable
     * @throws ModelSaveException
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    private function importModel(array $data): void
    {
        $class = $data[ExportImportBehavior::CLASS_PARAM] ?? null;
        if ($class && class_exists($class)) {
            /* @var ActiveRecord|ExportImportBehavior $model */
            $model = new $class();
            if ($model instanceof ActiveRecord) {
                $model->import($data);
            } else {
                Yii::$app->session->addFlash('error', 'Неизвестная модель данных');
            }
        }
    }
}
