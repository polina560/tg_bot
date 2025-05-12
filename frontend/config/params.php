<?php

return [
    'adminEmail' => 'admin@example.com',

//   'upload-max-size' => 1024*1024*10, // Максимальный вес файла для загрузки = 10Mb

    // Метод восстановления пароля. Должен быть активен один из списка ниже. по умолчанию - token
    'passwordRestoreType' => 'token', // 1. Отправляем
//    'passwordRestoreType' => 'generate', // 2. Отправляем сгенерированный пароль пользователю

    'signup' => [ // Регистрация
        'enabled_clients' => [
            'email-password' => true
        ],
        'require' => [
//            'rules_accepted' => true // Необходимо согласиться с правилами
        ],
        'unique' => [
            'email' => true // Почта должна быть уникальной
        ]
    ]
];
