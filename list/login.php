<?php
session_start();
require 'db.php'; // Подключение к базе данных

$errorMessage = ""; // Переменная для хранения сообщения об ошибке

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Получаем пользователя из базы данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Успешная аутентификация
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role']; // Роль пользователя

        // Перенаправляем в зависимости от роли
        if ($user['role'] === 'admin') {
            header("Location: admin-panel.php");
        } else {
            header("Location: orders.php");
        }
        exit();
    } else {
        $errorMessage = "Неверный логин или пароль."; // Устанавливаем сообщение об ошибке
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style-logi.css"> <!-- Подключение стилей -->
    <title>Вход в систему</title>
</head>
<body>
<div class="container">
    <h1>Магазин Авоська</h1>
    <header class="header">Вход в систему</header>
    <form method="POST">
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
    
        <?php if ($errorMessage): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <div class="login"><span>Нет аккаунта? <a href="../index.php">Зарегистрируйтесь</a></span></div>
    </div>

   

</body>
</html>
