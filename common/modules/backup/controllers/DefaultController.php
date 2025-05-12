<?php

namespace common\modules\backup\controllers;

use admin\controllers\AdminController;
use common\components\{helpers\UserFileHelper, queue\BackupArchiveJob};
use common\modules\backup\{Backup, models\DbWrap};
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{Response, UploadedFile};
use ZipArchive;

/**
 * Class DefaultController
 *
 * @package backup\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DefaultController extends AdminController
{
    private string $queue_id = '__queue_id_for_archiving_task_exec';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'remove' => ['POST'],
                    'export' => ['POST'],
                    'import' => ['POST'],
                    'tables' => ['POST']
                ]
            ]
        ]);
    }

    /**
     * Открытие страницы для работы с бэкапами
     *
     * @throws \yii\base\Exception
     */
    final public function actionIndex(): string
    {
        set_time_limit(0);
        $data = DbWrap::getBackups();
        return $this->render('index', compact('data'));
    }

    /**
     * Получить список активных бекапов
     *
     * @throws \yii\base\Exception
     */
    final public function actionActiveBackups(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'messages' => ['success' => ['Список бекапов обновлен']],
            'data' => DbWrap::getBackups()
        ];
    }

    /**
     * Экспорт таблицы
     *
     * @throws \yii\base\Exception
     */
    final public function actionExport(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        // экспорт
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];

        $is_file = Yii::$app->request->post('is_file');
        $date = Yii::$app->request->post('date', date('Y-m-d_H-i-s'));

        if ($is_file == "false") {
            if (!$table = Yii::$app->request->post('table')) {
                return ['messages' => ['error' => ['POST field is empty']]];
            }
            try {
                if (DbWrap::export($table, $date)) {
                    $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                    $json['messages']['success'][] = Yii::t(
                        Backup::MODULE_MESSAGES,
                        'Export of table {table} completed successfully in {time} seconds',
                        ['table' => $table, 'time' => round($time, 3)]
                    );
                } else {
                    $json['messages']['warning'][] = Yii::t(
                        Backup::MODULE_MESSAGES,
                        'An error occurred while exporting the table {table}',
                        ['table' => $table]
                    );
                }
            } catch (Exception $e) {
                $json['messages']['danger'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while exporting the table {table}',
                    ['table' => $table]
                );
                $json['messages']['danger'][] = $e->getMessage();
                return $json;
            }
        } else {
            try {
                if (DbWrap::exportFiles($date)) {
                    $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                    $json['messages']['success'][] = Yii::t(
                        Backup::MODULE_MESSAGES,
                        'Export of files completed successfully in {time} seconds',
                        ['time' => round($time, 3)]
                    );
                    $id = Yii::$app->queue->push(new BackupArchiveJob(['dateString' => $date]));
                    Yii::$app->cache->set($this->queue_id, $id);
                } else {
                    $json['messages']['warning'][] = Yii::t(
                        Backup::MODULE_MESSAGES,
                        'An error occurred while exporting files'
                    );
                }
            } catch (Exception $e) {
                $json['messages']['danger'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while exporting files'
                );
                $json['messages']['danger'][] = $e->getMessage();


                return $json;
            }
        }
        return $json;
    }

    /**
     * Импорт таблицы
     *
     * @throws Exception|\yii\base\Exception
     */
    final public function actionImport(): array
    {
        $json = [];
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);

        if (!$dateString = Yii::$app->request->post('date')) {
            return ['messages' => ['error' => ['`date` POST field is empty']]];
        }

        $file = Yii::$app->request->post('is_file');
        if ($file == "false") {
            if (!$table = Yii::$app->request->post('table')) {
                return ['messages' => ['error' => ['`table` POST field is empty']]];
            }
            if (DbWrap::import($table, $dateString)) {
                $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $json['messages']['success'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'Import of table {table} completed successfully in {time} seconds',
                    ['table' => $table, 'time' => round($time, 3)]
                );
            } else {
                $json['messages']['warning'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while importing the table {table}',
                    ['table' => $table]
                );
            }
        } else {
            $file_name = "uploads";
            if (DbWrap::importFiles($file_name, $dateString)) {
                $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $json['messages']['success'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'Import of files completed successfully in {time} seconds',
                    ['time' => round($time, 3)]
                );
            } else {
                $json['messages']['warning'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while importing files',

                );
            }
        }
        return $json;
    }

    /**
     * Удаление всех бэкапов
     */
    final public function actionRemove(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        // remove
        $json = [];
        if (DbWrap::removeAll()) {
            $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $json['messages']['success'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'Deleting database backup files successfully completed in {time} seconds',
                ['time' => $time]
            );
        } else {
            $json['messages']['warning'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while deleting database backup files'
            );
        }
        return $json;
    }

    /**
     * Получение списка таблиц
     */
    final public function actionTables(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        try {
            if ($tables = DbWrap::getTables()) {
                $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                $json['messages']['success'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'List of tables successfully retrieved in {time} seconds',
                    ['time' => round($time, 3)]
                );
                $json['data'] = $tables;
            } else {
                $json['messages']['danger'][] = Yii::t(
                    Backup::MODULE_MESSAGES,
                    'An error occurred while retrieving the list of tables'
                );
            }
        } catch (Exception) {
            $json['messages']['danger'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'An error occurred while retrieving the list of tables'
            );
            return $json;
        }
        return $json;
    }

    /**
     * Упаковка дампа в архив и его скачивание
     *
     * @throws \yii\base\Exception
     */
    final public function actionDownload(): Response|array
    {
        if (!Yii::$app->queue->isDone(Yii::$app->cache->get($this->queue_id))) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $json['messages']['warning'][] = Yii::t(
                Backup::MODULE_MESSAGES,
                'Происходит сборка архива. Попробуйте позже.',

            );
            return $json;
        }
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $dateString = (!empty($_GET['date'])) ? $_GET['date'] : null;
        DbWrap::downloadFromObjectStorage($dateString);
        $path = Yii::getAlias('@admin/runtime/backup_db');
        UserFileHelper::createDirectory($path, 0777);
        $zipPath = "$path/$dateString/$dateString.zip";

        if (!file_exists($zipPath)) {
            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator("$path/$dateString"),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // Исключаем упаковку архива в архив
                    if (!str_ends_with($filePath, "$dateString.zip")) {
                        $relativePath = basename($filePath);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
        }

        return Yii::$app->response->sendFile($zipPath);
    }

    /**
     *  Загрузка архива с дампом на сервер
     *
     * @throws \yii\base\Exception
     */
    final public function actionUpload(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '200M');
        set_time_limit(0);
        $json = [];
        if (!$file = UploadedFile::getInstanceByName('file')) {
            $json['messages']['error'][] = 'File not found (request body is empty)';
            return $json;
        }

        $path = Yii::getAlias("@admin/runtime/backup_db/$file->baseName/");
        $filename = "$file->baseName.$file->extension";
        UserFileHelper::createDirectory($path);
        $file->saveAs($path . $filename);
        $zip = new ZipArchive();
        $zip->open($path . $filename);
        $zip->extractTo($path);
        $zip->close();
        DbWrap::uploadToObjectStorage($file->baseName);
        $json['messages']['success'][] = Yii::t(Backup::MODULE_MESSAGES, 'Database backup loaded successfully');
        return $json;
    }
}
