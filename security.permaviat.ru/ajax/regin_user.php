<?php
	session_start();
	include("../settings/connect_datebase.php");
	require_once("../libs/autoload.php");
	
	$login = $_POST['login'];
	$password = $_POST['password'];
	
	// ищем пользователя
	$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
	$id = -1;
	
	if($user_read = $query_user->fetch_row()) {
		echo $id;
	} else {

		$secret = '6LelY0osAAAAAJBdlDvMYtICQJxC1f2lMZF5FPZU';

		if (isset($_POST['g-recaptcha-response'])) {
			$recaptcha = new \ReCaptcha\ReCaptcha($secret);
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

			if ($resp->isSuccess()) {
				$mysqli->query("INSERT INTO `users`(`login`, `password`, `roll`) VALUES ('".$login."', '".$password."', 0)");
		
				$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."';");
				$user_new = $query_user->fetch_row();
				$id = $user_new[0];
					
				if($id != -1) $_SESSION['user'] = $id; // запоминаем пользователя
				echo $id;
			} else {
				echo "Пользователь не распознан.";
			}
		} else {
			echo "Нет ответа от recaptcha"; 
		}

		
	}
?>