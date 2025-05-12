<?php

namespace common\components\queue;

use common\components\{exceptions\ModelSaveException, helpers\UserFileHelper};
use common\modules\backup\models\DbWrap;
use common\modules\notification\{enums\Type, models\Notification};
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\base\{BaseObject, Exception};
use yii\queue\JobInterface;
use ZipArchive;

class BackupArchiveJob extends BaseObject implements JobInterface
{

    public string $dateString;

    /**
     * {@inheritdoc}
     *
     * @throws ModelSaveException
     * @throws Exception
     */
    public function execute($queue): void
    {
        ini_set('memory_limit', '200M');
        set_time_limit(0);

        $dbRemote = Yii::$app->db->master;
        $dbName = DbWrap::getDsnAttribute('dbname', $dbRemote->dsn);
        $dateString = "$dbName-$this->dateString";

        DbWrap::downloadFromObjectStorage($dateString);
        $path = Yii::getAlias('@admin/runtime/backup_db');
        UserFileHelper::createDirectory($path);
        $zipPath = "$path/$dateString/$dateString.zip";

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
        Notification::create(Type::Success, 'Архив резервной копии БД создан');
    }
}
