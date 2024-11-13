<?php
session_start();
require 'db.php'; // Подключение к базе данных

// Проверка, является ли пользователь администратором
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Инициализация переменной для сообщений
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Очищаем сообщение после его отображения

// Обработка формы добавления товара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    if (!empty($name) && !empty($price) && !empty($description)) {
        // Проверка на существование товара с таким именем
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ?");
        $stmt->execute([$name]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['message'] = "<p style='color: red;'>Данный товар уже есть в списке.</p>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $price, $description]);
            $_SESSION['message'] = "<p style='color: green;'>Товар успешно добавлен!</p>";
        }
    } else {
        $_SESSION['message'] = "<p style='color: red;'>Пожалуйста, заполните все поля.</p>";
    }

    // Перенаправление после добавления
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    $_SESSION['message'] = "<p>Статус заказа обновлен!</p>";

    // Перенаправление после обновления статуса
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Обработка удаления товара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $_SESSION['message'] = "<p>Товар успешно удален!</p>";

    // Перенаправление после удаления
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Получение всех товаров
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

// Получение всех заказов с fullname пользователя
$stmt = $pdo->query("SELECT o.*, p.name AS product_name, u.fullname AS user_fullname FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON o.user_id = u.id");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администрирования</title>
    <link rel="stylesheet" href="../css/style-admins-panel.css"> <!-- Подключение стилей -->
</head>
<body>
<div class="container">
    <h1 align="middle">Магазин Авоська <br> Панель администрирования</h1>

    <h2>Добавить новый товар</h2>
    <form method="POST" class="form">
        <label for="name">Название товара:</label>
        <input type="text" name="name" required><br>
        <label for="price">Цена:</label>
        <input type="number" name="price" step="0.01" required><br>
        <label for="description">Описание:</label>
        <textarea name="description" required></textarea><br>
        <button type="submit" name="add_product">Добавить товар</button>
    </form>

    <h2>Список товаров</h2>
    <table>
        <tr>
            <th>№</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Описание</th>
            <th>Действие</th>
        </tr>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="delete_product" onclick="return confirm('Вы уверены, что хотите удалить этот товар?');">Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">
                    <div class="no-products-message">У вас нет добавленных товаров</div>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <h2>Список заказов</h2>
    <table>
        <tr>
            <th>№</th>
            <th>Пользователь</th>
            <th>Товар</th>
            <th>Количество</th>
            <th>Адрес доставки</th>
            <th>Статус и выбор статуса</th>
            <th>Действие</th>
        </tr>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['user_fullname']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <strong><?php echo htmlspecialchars($order['status']); ?></strong><br>
                        <select name="status">
                            <option value="Принят" <?php if ($order['status'] == 'Принят') echo 'selected'; ?>>Принят</option>
                            <option value="Отменен" <?php if ($order['status'] == 'Отменен') echo 'selected'; ?>>Отменен</option>
                        </select>
                </td>
                <td>
                    <button class="update" type="submit" name="update_order">Обновить статус</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center;">
                    <div class="no-orders-message">У вас нет активных заказов</div>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="message">
        <?php echo $message; ?>
    </div>  

    <div>
        <a href="logout.php" class="exit">Выйти</a>
    </div>
</div>
</body>
</html>
