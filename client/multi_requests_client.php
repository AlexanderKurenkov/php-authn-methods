<?php

declare(strict_types=1);

// глобальная константа со значением API-ключа
define('API_KEY', 'e08550c937dfe00e83f22c866b584be0a729ca423d9bd8dfe0734900560c1a44');

// учетные данные
$username = 'username';
$password = 'password';

$curlHandles = [];

// функция для инициализации обработчика cURL
function init_curl_handle($endpoint, $authType)
{
	// создание обработчика cURL
	$ch = curl_init();

	// инициализация параметров запроса
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_NOBODY, false);

	if ($authType === CURLAUTH_BASIC || $authType === CURLAUTH_DIGEST) {
		// при выполнении дайджест-аутентификации с помощью cURL:
		// 1) cURL отправляет первоначальный запрос без каких-либо заголовков авторизации;
		// 2) сервер выдает в ответ код состояния 401 и указывает директивы заголовка WWW-Authenticate;
		// 3) cURL анализирует заголовок WWW-Authenticate, создает соответствующие заголовки для проверки подлинности,
		global $username, $password;
		curl_setopt($ch, CURLOPT_HTTPAUTH, $authType);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	} else if ($authType === 'API_KEY') {
		// отправка пользовательского заголовка со значением API ключа
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . API_KEY]);
	}

	return $ch;
}

// функция для инициализации мульти-обработчика cURL
function init_curl_multi_handle($endpoint, $authType)
{
	// количество одновременных запросов
	$concurrentRequests = 500;

	// создание мульти-обработчика cURL
	$multiHandle = curl_multi_init();

	// создание массива обработчиков запроса
	global $curlHandles;
	for ($i = 0; $i < $concurrentRequests; $i++) {
		$curlHandles[$i] = init_curl_handle($endpoint, $authType);
		curl_multi_add_handle($multiHandle, $curlHandles[$i]);
	}

	return $multiHandle;
}

// функция для отправки запроса
function send_requests($multiHandle, $curlHandles)
{
	$running = 0;

	// запускает бенчмарк
	$start = microtime(true);

	// отправка запросов и ождание ответов
	do {
		//запускаем множественный обработчик
		curl_multi_exec($multiHandle, $running);
		curl_multi_select($multiHandle);
	} while ($running > 0);

	// измеряет время от начала бенчмарка
	$end = microtime(true);

	$successfulResponses = 0;

	// получение данных из всех запросов
	foreach ($curlHandles as $ch) {
		// получение кода состояния HTTP
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode == 200) {
			$successfulResponses++;
		}

		// удаление завершенных обработчиков
		curl_multi_remove_handle($multiHandle, $ch);
		// завершает сеанс cURL
		curl_close($ch);
	}
	// закрывает набор cURL-дескрипторов
	curl_multi_close($multiHandle);

	// подсчет времени на выполнение одного запроса (на основе числа удачных запросов)
	$totalTime = $end - $start;
	$requestsPerSecond = $successfulResponses / $totalTime;

	echo "=====================================================\n";
	echo "Результаты тесторования:\n";
	echo "=====================================================\n";
	echo "Total Time: " . round($totalTime, 2) . " seconds\n";
	echo "Requests per Second: " . round($requestsPerSecond, 2) . "\n";
	echo "Successful Responses (200 OK): $successfulResponses\n";
}

// отображение пользовательского меню
echo "Введите номер задачи для тестирования (1-3):\n";
echo "1) базовая аутентификация\n";
echo "2) дайджест-аутентификация\n";
echo "3) аутентификация с помощью ключа API\n";
echo "Ожидание ввода: ";

// получение ввода от пользователя
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
$authType = null;
$endpoint = '';

// проверка правильности введенных пользователем данных
switch ($input) {
	case '1':
		$endpoint = 'http://localhost/basic_auth.php?echo_param=test';
		$authType = CURLAUTH_BASIC;
		break;
	case '2':
		$endpoint = 'http://localhost/digest_auth.php?echo_param=test';
		$authType = CURLAUTH_DIGEST;
		break;
	case '3':
		$endpoint = 'http://localhost/api_key_auth.php?echo_param=test';
		$authType = 'API_KEY';
		break;
	default:
		echo "Неверный ввод. Пожалуйста, введите число от 1 до 3.\n";
		break;
}

// закрывает открытый дескриптор файла
fclose($handle);

// выполнение задачи, указанной пользователем
$multiHandle = init_curl_multi_handle($endpoint, $authType);
send_requests($multiHandle, $curlHandles);
