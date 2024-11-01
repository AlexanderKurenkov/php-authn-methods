<?php

declare(strict_types=1);

// учетные данные
$username = 'username';
$password = 'password';
// директива realm заголовка авторизации
$realm = 'digest-authn';

// возвращает параметр строки запроса обратно пользователю
function send_success_response()
{
	if (isset($_GET['echo_param'])) {
		echo $_GET['echo_param'];
	} else {
		echo 'No "echo_param" found in query string.';
	}
}


// отправка кода ошибки при невозможности аутентифицироваться
function send_unauthorized_response()
{
	global $realm;
	http_response_code(401); // Unauthorized
	header('WWW-Authenticate: Digest realm="' . $realm . '", nonce="' . uniqid() . '"');
}

// функция для разбора заголовка авторизации HTTP
function http_digest_parse($txt)
{
	// защита от отсутствующих директив
	$needed_parts = ['nonce' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
	$data = [];
	$keys = implode('|', array_keys($needed_parts));

	preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

	foreach ($matches as $m) {
		$data[$m[1]] = $m[3] ? $m[3] : $m[4];
		unset($needed_parts[$m[1]]);
	}

	return $needed_parts ? false : $data;
}

// проверка, использована ли при запросе дайджест-аутентификация
if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
	// отправка указаний для аутентификации
	send_unauthorized_response();
	exit();
}

// анализируем переменную PHP_AUTH_DIGEST
$data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);

if ($data && $data['username'] === $username) {
	// генерируем корректный ответ
	$A1 = md5($data['username'] . ':' . $realm . ':' . $password);
	$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
	$valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $A2);

	if ($data['response'] === $valid_response) {
		send_success_response();
	}
}
