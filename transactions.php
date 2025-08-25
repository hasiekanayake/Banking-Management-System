





<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch transaction history
$stmt = $conn->prepare("SELECT type, amount, timestamp FROM transactions WHERE user_id = ? ORDER BY timestamp DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="transactions.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Transaction History</h1>
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
            <section class="transaction-table">
                <table>
                    <tr>
                        <th>Type</th>
                        <th>Amount (LKR)</th>
                        <th>Date</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ucfirst($row['type']); ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo $row['timestamp']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </section>
            <a class="btn" href="dashboard.php">Back to Dashboard</a>
        </main>
        <footer>
        <?php include 'footer.php'; ?>
            <p>&copy; 2025 Banking System. All rights reserved.</p>
        </footer>
    </div>
    
</body>
</html>
