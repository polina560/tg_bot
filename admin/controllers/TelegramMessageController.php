<?php

namespace admin\controllers;

use admin\controllers\AdminController;
use admin\modules\rbac\components\RbacHtml;
use common\components\helpers\UserUrl;
use common\models\TelegramMessage;
use common\models\TelegramMessageButton;
use common\models\TelegramMessageImage;
use common\models\TelegramMessageSearch;
use Exception;
use kartik\grid\EditableColumnAction;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * TelegramMessageController implements the CRUD actions for TelegramMessage model.
 *
 * @package admin\controllers
 */
final class TelegramMessageController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all TelegramMessage models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $model = new TelegramMessage();

        if (RbacHtml::isAvailable(['create']) && $model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
        }

        $searchModel = new TelegramMessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index',
            ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'model' => $model]
        );
    }

    /**
     * Displays a single TelegramMessage model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Creates a new TelegramMessage model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string|null $redirect если нужен иной редирект после успешного создания
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(string $redirect = null): Response|string
    {
        $model = new TelegramMessage();
        $modelsImages = [new TelegramMessageImage()];
//        $modelsButtons = [new TelegramMessageButton()];

        if ($model->load(Yii::$app->request->post())) {
            $modelsImages = TelegramMessageImage::createMultiple();
            TelegramMessageImage::loadMultiple($modelsImages, Yii::$app->request->post());

//            $modelsButtons = TelegramMessageButton::createMultiple();
//            TelegramMessageButton::loadMultiple($modelsButtons, Yii::$app->request->post());

            $valid = $model->validate()
                && TelegramMessageImage::validateMultiple($modelsImages);
//                && TelegramMessageButton::validateMultiple($modelsButtons);

            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if ($this->_saveModels($model, $modelsImages)) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    $transaction->rollBack();
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::$app->session->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'modelMessage' => $model,
            'modelsImages' => (empty($modelsImages)) ? [new TelegramMessageImage()] : $modelsImages,
//            'modelsButtons' => (empty($modelsButtons)) ? [new TelegramMessageButton()] : $modelsButtons,
        ]);
    }

    /**
     * Updates an existing TelegramMessage model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $modelImages = $model->telegramMessageImages;
//        $modelButtons = $model->telegramMessageButtons;

        $pkey = 'id';

        if ($model->load(Yii::$app->request->post())) {
            $oldImageIDs = ArrayHelper::map($modelImages, $pkey, $pkey);
            $modelImages = TelegramMessageImage::createMultiple($modelImages);
            TelegramMessageImage::loadMultiple($modelImages, Yii::$app->request->post());
            $deletedImageIDs = array_diff($oldImageIDs, array_filter(ArrayHelper::map($modelImages, $pkey, $pkey)));

//            $oldButtonIDs = ArrayHelper::map($modelButtons, $pkey, $pkey);
//            $modelButtons = TelegramMessageButton::createMultiple($modelButtons);
//            TelegramMessageButton::loadMultiple($modelButtons, Yii::$app->request->post());
//            $deletedButtonIDs = array_diff($oldButtonIDs, array_filter(ArrayHelper::map($modelButtons, $pkey, $pkey)));

            $valid = $model->validate()
                && TelegramMessageImage::validateMultiple($modelImages);
//                && TelegramMessageButton::validateMultiple($modelButtons);

            if ($valid && $transaction = Yii::$app->db->beginTransaction()) {
                try {
                    if (!empty($deletedImageIDs)) {
                        TelegramMessageImage::deleteAll([$pkey => $deletedImageIDs]);
                    }
//                    if (!empty($deletedButtonIDs)) {
//                        TelegramMessageButton::deleteAll([$pkey => $deletedButtonIDs]);
//                    }
                    if ($this->_saveModels($model, $modelImages)) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    $transaction->rollBack();

                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::$app->session->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'modelMessage' => $model,
            'modelsImages' => (empty($modelImages)) ? [new TelegramMessageImage()] : $modelImages,
//            'modelsButtons' => (empty($modelButtons)) ? [new TelegramMessageButton()] : $modelButtons,
        ]);
    }

    /**
     * @param TelegramMessageImage[]     $modelsImages
     * @param TelegramMessageButton[]     $modelsButtons

     */
    private function _saveModels(TelegramMessage $model, array $modelsImages): bool
    {
        if ($model->save(false)) {
            foreach ($modelsImages as $modelImage) {
                if (!empty($modelImage->image)) {
                    $modelImage->telegram_message_id = $model->id;
                    if (!$modelImage->save(false)) {
                        return false;
                    }
                }
            }
//            foreach ($modelsButtons as $modelButton) {
//                if (!empty($modelButton->btn_name)) {
//                    $modelButton->telegram_message_id = $model->id;
//                    if (!$modelButton->save(false)) {
//                        return false;
//                    }
//                }
//            }
            return true;
        }
        return false;
    }

    /**
     * Deletes an existing TelegramMessage model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', "Элемент №$id удален успешно");
        return $this->redirect(UserUrl::setFilters(TelegramMessageSearch::class));
    }

    /**
     * Finds the TelegramMessage model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(int $id): TelegramMessage
    {
        if (($model = TelegramMessage::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => TelegramMessage::class
            ]
        ];
    }
}
