<?php
session_start();

// Удаляем все сессионные переменные
$_SESSION = [];

// Если нужно, уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на страницу входа
header("Location: login.php");
exit();
?>
