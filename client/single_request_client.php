<?php

declare(strict_types=1);

define('API_KEY', 'e08550c937dfe00e83f22c866b584be0a729ca423d9bd8dfe0734900560c1a44');

// определяем учетные данные
$username = 'user';
$password = 'password';

// формируем URL со строкой запроса
// $url = 'http://localhost/basic_auth.php?echo_param=test';
$url = 'http://localhost/api_key_auth.php?echo_param=test';

// указываем заголовок для аутентификации
$context = stream_context_create(array(
	'http' => array(
		// 'header'  => "Authorization: Basic " . base64_encode($username . ":" . $password),
		'header'  => "X-Api-Key: " . API_KEY
	),
));

// читаем содержимое ответа
$response = file_get_contents($url, false, $context);

// проверяем успешность ответа
if ($response !== false) {
	echo $response;
} else {
echo 'Ошибка при выполнении запроса';
}
