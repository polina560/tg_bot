# Yii2 Backend Template

Основа для backend проекта основанная на Yii2 Framework с интегрированным VueJS
Описание API: [phpDocumentor](https://docs.dev.peppers-studio.ru/yii-template/)

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/phpunit/phpunit/php)

## Требования к ПО

1. [PHP](https://www.php.net/downloads.php) версии 8.2 или новее

   Для установки чистого PHP на Windows необходимо:
    1. Распаковать архив
    2. Добавить распакованную папку (где находится php.exe)
       в [PATH](https://www.php.net/manual/ru/faq.installation.php#faq.installation.addtopath)
    3. Переименовать `php.ini-development` в `php.ini`
    4. Включить дополнительные расширения в php.ini: bz2, curl, ctype, mbstring, exif, fileinfo, gd, imap, intl, mysqli,
       odbc, openssl, pdo_mysql, soap, sockets, sodium, xml или xsl
2. [NodeJS](https://nodejs.org/en/download/) версии 20 или новее
3. MySQL от версии 5.5 или MariaDB

## Быстрый старт в Open Server

Распаковать в новую папку в `OSPanel/home`, запустить php скрипт [init-all](#скрипт-быстрого-разворачивания)

```shell
php init-all --domain=<локальный домен> --db-name=<название БД>
```

И перезапустить Open Server

## Запуск простого локального сервера

```shell
npm run start
```

Локальный сервер будет доступен по ссылке <http://localhost:9001>

Если используете Open Server и нужен только **hot-reload**:

```shell
npm run serve-js
```

## Деплой проекта на сервере

### Слить содержимое в корень сайта

- Вариант 1: скачать архив, загрузить файлы на хостинг
- Вариант 2: напрямую копировать содержимое репозитория на хостинг

### Webroot

Настроить webroot на htdocs

Проверить что в Apache включен modrewrite и AllowOverwriteAll

Если проект устанавливается на Nginx, то нужно настроить редирект всех неизвестных путей в index.php для локаций:

- `/`
- `/admin`
- `/api`

### Сборка

Вместо `npm` можно использовать `pnpm`

```shell
composer install --no-interaction # На продакшене с флагом--no-dev
php init --env="Stage Development" --overwrite=All # На продакшене --env="Stage Production"
npm install
npm run build
```

### Миграции

```shell
php yii migrate --interactive=0
php yii rbac/migrate --interactive=0
```

### Очереди

Процесс обработки очереди запускается командой

```shell
php yii queue/listen --verbose
```

Можно запустить одновременно несколько процессов, для повышения производительности

### ENV настройки

Звездочкой помечены обязательные для работы приложения

- `YII_ENV`* - dev/prod
- `DB_HOST` - хост от БД (по умолчанию localhost)
- `DB_NAME`* - название БД
- `DB_USER`* - пользователь БД (логин)
- `DB_PASS`* - пароль БД
- `DB_CHARSET` - кодировка подключения к БД (по умолчанию utf8mb4)

Дополнительные настройки

- `APP_DOMAIN` домен без указания протокола (необходимо указать, если заголовок Host не проксируется до PHP контейнера)
- `BASE_URI` - базовый url, когда приложение доступно не в корне домена, например `/some/path`
- `TRUSTED_HOSTS` - список разрешенных хостов, разделенных запятой, откуда принимать запросы. Нужен чтобы не пропускать
  запросы от неизвестных reverse proxy
- `CORS_DOMAINS` - список разрешенных доменов для CORS политики
- `CSP_NONCE` - строка, которая будет добавляться ко всем скриптам в атрибут nonce и в мета-тег csp-nonce

Репликация БД

- `DB_SLAVE_HOSTS` - список хостов через запятую на read-only реплики
- `DB_SLAVE_NAME` - название БД от реплики
- `DB_SLAVE_USER` - пользователь от реплики
- `DB_SLAVE_PASS` - пароль от реплики

Внешнее хранилище для кеша и сессий

- `REDIS_HOSTNAME` - хост Redis
- `REDIS_PORT` - порт Redis (по умолчанию 6379)
- `REDIS_DATABASE` - БД Redis (по умолчанию 0)
- `REDIS_PASSWORD` - пароль Redis

Внешнее хранилище для файлов

- `S3_ENDPOINT` - хост S3 хранилища
- `S3_REGION` - регион S3 хранилища
- `S3_KEY` - ключ S3 хранилища
- `S3_SECRET` - секрет S3 хранилища
- `S3_BUCKET` - публичный bucket S3 хранилища
- `S3_PRIVATE_BUCKET` - приватный bucket S3 хранилища

-------------------
После запуска панель администратора будет доступна по адресу `/admin/`, а точка входа для api - `/api/v1/`

В DEV окружении по ссылке `/api/v1/site/docs` доступен тестер методов API в Swagger интерфейсе

Для отправки почты нужно внести настройки соединения в разделе "Настройки" в Админ.панели

## Скрипт быстрого разворачивания

В проекте есть скрипт для разворачивания проекта одной командой

```shell
php init-all
```

Скрипт автоматически устанавливает и обновляет composer, применяет миграции и делает сборку JS

Он имеет следующие опции:

- `--env` - окружение, возможные значения: *peppers*, *dev*, *prod*
- `--domain` - доменное имя вместе с протоколом, которое приложение будет считать "своим" (необходимо для корректного
  обнаружения домена при отложенной отправке почты)
- `--db-host=127.0.0.1:3306 --db-name=some-db --db-user=some-user --db-password=some-password` - конфигурация БД, не
  задавать при вызове в docker контейнере
- `-a` - флаг для создания админа после выполнения всех инициализирующих скриптов
- `-u` - флаг для безопасного обновления уже развернутого проекта (будут применены новые миграции, а локальные
  конфигурации не будут перетерты)
- `--skip-node` пропуск установки NodeJS зависимостей

## Настройка cron

Примеры для настройки запуска скрипта `daemon/clear-backups` каждые 2 минуты:

Linux

1. Открыть для редактирования crontab:

    ```shell
    sudo crontab -e
    ```

2. Добавить строчку:
   > */2 * * * * <путь до бинарника>/php <root каталог сайта>/yii queue/run

## Настройка systemd

Чтобы настроить запуск воркеров под управлением systemd, создайте конфиг с именем `yii-queue@.service` в папке
`/etc/systemd/system` со следующими настройками:

```ini
[Unit]
Description = Yii Queue Worker %I
After = network.target mysql.service

[Service]
User = www-data
Group = www-data
ExecStart = /usr/bin/php /var/www/<path_to_my_project>/yii queue/listen --verbose
Restart = on-failure

[Install]
WantedBy = multi-user.target
```

Вместо `www-data` укажите пользователя у которого есть полный доступ к файлам сайта и к исполняемому файлу PHP.
Перезагрузите systemd, чтобы он увидел новый конфиг, с помощью команды:

```shell
systemctl daemon-reload
```

Набор команд для управления воркерами:

```shell
# Запустить два воркера
systemctl start yii-queue@1 yii-queue@2
# Получить статус запущенных воркеров
systemctl status "yii-queue@*"
# Остановить один воркер
systemctl stop yii-queue@2
# Остановить все воркеры
systemctl stop "yii-queue@*"
# Добавить воркеры в автозагрузку
systemctl enable yii-queue@1 yii-queue@2
```

-------------------

[google-docs-icon]: https://icons.iconarchive.com/icons/papirus-team/papirus-apps/24/google-docs-icon.png

![Google Doc][google-docs-icon] [Тестирование с помощью docker-compose](https://docs.google.com/document/d/1dKVLSUFN3Gac5nS_pZwa8JKYG-umfNu0qpfv4GEPXsY/edit)
