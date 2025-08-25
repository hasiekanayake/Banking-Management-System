<?php

require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_email = htmlentities($_POST['receiver_email']);
    $amount = floatval($_POST['amount']);
    $sender_id = $_SESSION['user_id'];

    if ($amount <= 0) {
        echo "Amount must be greater than 0!";
        exit;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Validate receiver
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $receiver_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            throw new Exception("Receiver not found!");
        }

        $stmt->bind_result($receiver_id);
        $stmt->fetch();
        $stmt->close();

        // Check sender's balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $sender_id);
        $stmt->execute();
        $stmt->bind_result($sender_balance);
        $stmt->fetch();
        $stmt->close();

        if ($sender_balance < $amount) {
            throw new Exception("Insufficient balance!");
        }

        // Debugging: Check values before updates
        echo "Sender Balance Before Transfer: {$sender_balance}<br>";
        echo "Transfer Amount: {$amount}<br>";

        // Debit sender
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing debit query: " . $conn->error);
        }
        $stmt->bind_param("di", $amount, $sender_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing debit query: " . $stmt->error);
        }
        $stmt->close();
/*
        // Credit receiver
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing credit query: " . $conn->error);
        }
        $stmt->bind_param("di", $amount, $receiver_id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing credit query: " . $stmt->error);
        }
        $stmt->close();*/

        // Record transaction for sender
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (?, 'debit', ?)");
        if (!$stmt) {
            throw new Exception("Error preparing transaction log (sender): " . $conn->error);
        }
        $stmt->bind_param("id", $sender_id, $amount);
        if (!$stmt->execute()) {
            throw new Exception("Error logging transaction (sender): " . $stmt->error);
        }
        $stmt->close();

        // Record transaction for receiver
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (?, 'credit', ?)");
        if (!$stmt) {
            throw new Exception("Error preparing transaction log (receiver): " . $conn->error);
        }
        $stmt->bind_param("id", $receiver_id, $amount);
        if (!$stmt->execute()) {
            throw new Exception("Error logging transaction (receiver): " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
       
        $conn->commit();
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({title: 'Success', text: 'Transfer successful!', icon: 'success'}).then(() => { window.location.href = 'dashboard.php'; }); });</script>";
    } catch (Exception $e) {
        // Rollback on failure
        $conn->rollback();
        echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Error', 'Transfer failed: " . $e->getMessage() . "', 'error'); });</script>";
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="transfer.css">
</head>
<body>
    <header>
        <h1>Transfer Funds</h1>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </header>
    <div class="container">
        <nav>
            <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="transfer.php">Transfer Funds</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <main>
            <section class="transfer-form">
                <h2>Make a Transfer</h2>
                <form method="POST" action="">
                    <input type="email" name="receiver_email" placeholder="Receiver's Email" required>
                    <input type="number" step="0.01" name="amount" placeholder="Amount (LKR)" required>
                    <button type="submit">Transfer</button>
                </form>
            </section>
        </main>
        <footer>
        <?php include 'footer.php'; ?>
            <p>&copy; 2025 Banking System. All rights reserved.</p>
        </footer>
    </div>
    
</body>
</html>
