
<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
        </header>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="transfer.php">Transfer Funds</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main>
            <section class="balance">
                <h2>Account Overview</h2>
                <p>Your balance: <strong>LKR <?php echo number_format($balance, 2); ?></strong></p>
                <a class="btn" href="transfer.php">Transfer Funds</a>
            </section>
        </main>
        <footer>
            <p>&copy; 2025 Banking System. All rights reserved.</p>
        </footer>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>
