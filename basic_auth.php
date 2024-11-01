<?php

declare(strict_types=1);

// учетные данные
$username = 'username';
$password = 'password';
// директива realm заголовка авторизации
$realm = 'basic-auth';

// возвращает параметр строки запроса обратно пользователю
function send_success_response()
{
	if (isset($_GET['echo_param'])) {
		echo $_GET['echo_param'];
	} else {
		echo 'В строке запроса не найден "echo_param".';
	}
}

// проверка, отправил ли пользователь учетные данные, и верны ли логин и пароль
if (
	isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) &&
	$_SERVER['PHP_AUTH_USER'] === $username && $_SERVER['PHP_AUTH_PW'] === $password
) {
	// ответ при успешной аутентификации пользователя
	send_success_response();
} else {
	// неверные учетные данные, в авторизации отказано
	header('WWW-Authenticate: Basic realm="' . $realm . '"');
	http_response_code(401); // Unauthorized
}
