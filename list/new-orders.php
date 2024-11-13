<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "avoskadb");

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";

// Обработка нового заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $delivery_address = $_POST['delivery_address'];

    // Проверка на существующий заказ
    $check_stmt = $mysqli->prepare("SELECT * FROM orders WHERE user_id = ? AND product_id = ? AND quantity = ? AND delivery_address = ?");
    $check_stmt->bind_param("iiis", $user_id, $product_id, $quantity, $delivery_address);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Вы уже заказали этот товар на этот адрес.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, product_id, quantity, delivery_address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $product_id, $quantity, $delivery_address);
        $stmt->execute();
        header("Location: orders.php"); // Перенаправление на страницу заказов после успешного создания
        exit();
    }
}

// Получение всех продуктов с ценами и описаниями
$products = $mysqli->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заказа</title>
    <link rel="stylesheet" href="../css/style-orders.css"> <!-- Подключаем внешний файл стилей -->
</head>
<body>
<div class="container">
    <h1>Создание нового заказа</h1>
    <form method="post" class="form">
        <label for="product_id">Товар:</label>
        <select name="product_id" required>
            <?php while($product = $products->fetch_assoc()): ?>
            <option value="<?php echo $product['id']; ?>">
                <?php echo htmlspecialchars($product['name']); ?> - <?php echo htmlspecialchars($product['price']); ?> руб. - <?php echo htmlspecialchars($product['description']); ?>
            </option>
            <?php endwhile; ?>
        </select>
        <label for="quantity">Количество:</label>
        <input type="number" name="quantity" required min="1">
        <label for="delivery_address">Адрес доставки:</label>
        <input type="text" name="delivery_address" required>
        <button type="submit">Создать заказ</button>
        <a href="orders.php" class="button back">Назад</a>
    </form>
    
    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
</div>
</body>
</html>
