<?php
session_start();
include("../settings/connect_datebase.php");

if (!isset($_POST['g-recaptcha-response'])) {
    echo "Ошибка: нет данных от reCAPTCHA.";
    exit;
}

$recaptchaResponse = $_POST['g-recaptcha-response'];
$secret = '6Lekb0osAAAAALUJ2h_h3GZsYc5d_5fqY-LoPuu8'; 

$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secret,
    'response' => $recaptchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

if ($response && $response['success']) {
    $score = $response['score'] ?? 0.0;

    if ($score >= 0.5) {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `login` = ? AND `password` = ?");
        $stmt->bind_param("ss", $login, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $_SESSION['user'] = $user['id'];
            echo md5(md5($user['id'])); 
        } else {
            echo ""; 
        }
    } else {
        echo "Подозрительная активность. Попробуйте позже.";
    }
} else {
    echo "Проверка безопасности не пройдена.";
}
?>