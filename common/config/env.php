<?php

if (file_exists(dirname(__DIR__, 2) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();
}
