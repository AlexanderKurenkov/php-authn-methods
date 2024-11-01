<?php

declare(strict_types=1);

// глобальная константа со значением API-ключа
define('API_KEY', 'e08550c937dfe00e83f22c866b584be0a729ca423d9bd8dfe0734900560c1a44');

// функция для проверки ключа API
function verifyApiKey($key)
{
	return $key === API_KEY;
}

// возвращает параметр строки запроса обратно пользователю
function send_success_response()
{
	if (isset($_GET['echo_param'])) {
		echo $_GET['echo_param'];
	} else {
		echo 'No "echo_param" found in query string.';
	}
}

// получение API-ключа из заголовка запроса
if (isset($_SERVER['HTTP_X_API_KEY'])) {
	$apiKey = $_SERVER['HTTP_X_API_KEY'];

	// проверка ключа API
	if (verifyApiKey($apiKey)) {
		// успешный ответ на запрос
		send_success_response();
	} else {
		// отправка кода ошибки при неверном ключе API
		http_response_code(403);
	}
} else {
	// отправка кода ошибки, если ключ API отсутствует
	http_response_code(400);
}
