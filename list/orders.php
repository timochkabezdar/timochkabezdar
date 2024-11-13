<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "avoskadb");

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Перенаправление на страницу входа
    exit();
}

$user_id = $_SESSION['user_id'];

// Обработка удаления заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order_id'])) {
    $delete_order_id = $_POST['delete_order_id'];
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_order_id, $user_id);
    $stmt->execute();
}

// Получение заказов пользователя
$orders = $mysqli->query("SELECT o.id, p.name, o.quantity, o.delivery_address, o.status FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = $user_id");
$order_count = $orders->num_rows; // Подсчет количества заказов
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ваши заказы</title>
    <link rel="stylesheet" href="../css/style-orders.css"> <!-- Подключаем внешний файл стилей -->
</head>
<body>
<div class="container">
    <h1 align="middle">Магазин Авоська</h1>
    <h2>Ваши заказы</h2>

    <table>
        <tr>
            <th>Название товара</th>
            <th>Количество</th>
            <th>Адрес доставки</th>
            <th>Статус заказа</th>
            <th>Действия</th>
        </tr>
        <?php if ($order_count > 0): ?>
            <?php while($order = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="delete-button" onclick="return confirm('Вы уверены, что хотите отменить этот заказ?');">Отменить заказ</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="no-orders">У вас нет активных заказов</td> <!-- Сообщение о том, что нет заказов -->
            </tr>
        <?php endif; ?>
    </table>
        
    <div>
        <a href="new-orders.php" class="button right">Создать новый заказ</a>
        <a href="logout.php" class="exit">Выйти</a>
    </div>
</div>
</body>
</html>
