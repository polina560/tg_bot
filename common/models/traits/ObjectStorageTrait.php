<?php

namespace common\models\traits;

use Aws\S3\S3Client;
use common\components\helpers\UserUrl;
use Yii;

trait ObjectStorageTrait
{
    private static S3Client $s3Client;

    public static function hasS3Storage(): bool
    {
        $ENV = Yii::$app->environment;
        return !empty($ENV->S3_ENDPOINT) && !empty($ENV->S3_KEY) && !empty($ENV->S3_SECRET) && !empty($ENV->S3_REGION) && !empty($ENV->S3_PRIVATE_BUCKET);
    }

    public static function getS3Client(): S3Client
    {
        if (!isset(self::$s3Client)) {
            $ENV = Yii::$app->environment;
            self::$s3Client = new S3Client([
                'endpoint' => $ENV->S3_ENDPOINT,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $ENV->S3_KEY,
                    'secret' => $ENV->S3_SECRET
                ],
                'signature_version' => 'v4',
                'version' => 'latest',
                'region' => $ENV->S3_REGION
            ]);
        }
        return self::$s3Client;
    }

    public static function fileUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (
            ($filename = Yii::getAlias('@root/htdocs/') . ltrim($value, '/'))
            && (file_exists($filename) && !is_dir($filename))
        ) {
            return UserUrl::toAbsolute($value);
        }
        if (self::hasS3Storage() && self::getS3Client()
                ->doesObjectExist(Yii::$app->environment->S3_BUCKET, $value)) {
            if (!str_starts_with($value, 'http') && !str_starts_with($value, '//')) {
                $value = UserUrl::toAbsolute($value);
            }
            return $value;
        }
        return null;
    }
}
