<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=avoskadb', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Не удалось подключиться к базе данных: " . $e->getMessage());
}

// Переменные для хранения сообщений
$errorMessage = "";
$successMessage = "";

// Проверка, был ли отправлен POST-запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']); // Исправлено на 'fullname'
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверка на пустые поля
    if (empty($fullname) || empty($phone) || empty($email) || empty($username) || empty($password)) {
        $errorMessage = "Пожалуйста, заполните все поля.";
    } else {
        // Проверка существования email
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $emailCount = $stmt->fetchColumn();

        // Проверка существования логина
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $usernameCount = $stmt->fetchColumn();

        if ($emailCount > 0) {
            $errorMessage = "Пользователь с таким адресом электронной почты уже существует.";
        } elseif ($usernameCount > 0) {
            $errorMessage = "Пользователь с таким логином уже существует.";
        } else {
            // Хеширование пароля перед вставкой
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Вставка нового пользователя
            try {
                $stmt = $pdo->prepare("INSERT INTO users (fullname, phone, email, username, password, role) VALUES (:fullname, :phone, :email, :username, :password, 'user')");
                $stmt->execute([
                    'fullname' => $fullname,
                    'phone' => $phone,
                    'email' => $email,
                    'username' => $username,
                    'password' => $hashedPassword
                ]);
                $successMessage = "Регистрация прошла успешно."; // Устанавливаем сообщение об успехе
            } catch (PDOException $e) {
                $errorMessage = "Ошибка: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/styles.css">
    <title>Регистрация</title>
</head>
<body>
<div class="container">
    <h1>Магазин Авоська</h1>
    <header class="header">Регистрация</header>
    <form method="POST">
        <input type="text" name="fullname" placeholder="ФИО" required>
        <input type="text" name="phone" placeholder="Телефон" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>

    <!-- Отображение сообщений -->
    <?php if ($successMessage): ?>
        <p class="success-message"><?php echo $successMessage; ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p class="error-message"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <div class="loginn"><span>Уже есть аккаунт? <a href="list/login.php">Войдите</a></span></div>
</div>
</body>
</html>
