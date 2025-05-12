<?php

# Поместите сюда название вашего бота, токен и адрес, на который Telegram перенаправит пользователя после авторизации
$BOT_USERNAME = 'Test756978Bot';
$BOT_TOKEN = '7947764023:AAE191PiuI99my_VerD4jzkVOt85OoOq1dM';
$REDIRECT_URI = 'http://ifmo.su/auth/callback';

?>

<html>
<body>
<!-- Код виджета в том месте, где хотим видеть кнопку -->
<script src="https://telegram.org/js/telegram-widget.js?2" data-telegram-login="<?= $BOT_USERNAME ?>" data-size="medium" data-auth-url="<?= $REDIRECT_URI ?>" data-request-access="write"></script>
</body>
</html>
