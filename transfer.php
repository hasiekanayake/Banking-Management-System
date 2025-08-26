<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user balance
$stmt = $conn->prepare("SELECT balance, name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($sender_balance, $sender_name);
$stmt->fetch();
$stmt->close();

$transfer_success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_email = htmlspecialchars($_POST['receiver_email']);
    $amount = floatval($_POST['amount']);
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : 'Fund Transfer';
    $sender_id = $_SESSION['user_id'];

    if ($amount <= 0) {
        $error_message = "Amount must be greater than 0!";
    } else {
        try {
            // Begin transaction
            $conn->begin_transaction();

            // Validate receiver
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->bind_param("s", $receiver_email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                throw new Exception("Receiver not found!");
            }

            $stmt->bind_result($receiver_id, $receiver_name);
            $stmt->fetch();
            $stmt->close();

            // Check if sender is trying to transfer to themselves
            if ($sender_id === $receiver_id) {
                throw new Exception("You cannot transfer funds to yourself!");
            }

            // Check sender's balance
            if ($sender_balance < $amount) {
                throw new Exception("Insufficient balance!");
            }

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

            // Credit receiver
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error preparing credit query: " . $conn->error);
            }
            $stmt->bind_param("di", $amount, $receiver_id);
            if (!$stmt->execute()) {
                throw new Exception("Error executing credit query: " . $stmt->error);
            }
            $stmt->close();

            // Record transaction for sender
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'debit', ?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing transaction log (sender): " . $conn->error);
            }
            $sender_desc = "Transfer to " . substr($receiver_email, 0, 3) . "***" . substr($receiver_email, strpos($receiver_email, "@"));
            $stmt->bind_param("ids", $sender_id, $amount, $sender_desc);
            if (!$stmt->execute()) {
                throw new Exception("Error logging transaction (sender): " . $stmt->error);
            }
            $stmt->close();

            // Record transaction for receiver
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing transaction log (receiver): " . $conn->error);
            }
            $receiver_desc = "Transfer from " . $sender_name;
            $stmt->bind_param("ids", $receiver_id, $amount, $receiver_desc);
            if (!$stmt->execute()) {
                throw new Exception("Error logging transaction (receiver): " . $stmt->error);
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();
            $transfer_success = true;
            
            // Refresh balance after transfer
            $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($sender_balance);
            $stmt->fetch();
            $stmt->close();
            
        } catch (Exception $e) {
            // Rollback on failure
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds | Banking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #2c3e50;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }
        
        header h1 {
            font-size: 28px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        header h1 i {
            margin-right: 10px;
        }
        
        /* Navigation */
        nav {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            justify-content: center;
            gap: 10px;
        }
        
        nav li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            background: var(--light);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        nav li a:hover {
            background: var(--primary);
            color: white;
        }
        
        nav li a.active {
            background: var(--primary);
            color: white;
        }
        
        nav li a i {
            margin-right: 8px;
        }
        
        /* Main Content */
        main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 900px) {
            main {
                grid-template-columns: 1fr;
            }
        }
        
        /* Balance Card */
        .balance-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .balance-card h2 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .balance-card h2 i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        .balance-amount {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }
        
        .balance-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .balance-info p {
            margin-bottom: 10px;
            color: #7f8c8d;
        }
        
        /* Transfer Form */
        .transfer-form {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .transfer-form h2 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .transfer-form h2 i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .amount-input {
            position: relative;
        }
        
        .currency-symbol {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .amount-input input {
            padding-left: 30px;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        /* Error Message */
        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
        }
        
        /* Success Message */
        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
        }
        
        /* Footer */
        footer {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <h1><i class="fas fa-money-bill-wave"></i> Transfer Funds</h1>
        </header>
        
        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                <li><a href="transfer.php" class="active"><i class="fas fa-money-bill-wave"></i> Transfer Funds</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main>
            <!-- Balance Card -->
            <section class="balance-card">
                <h2><i class="fas fa-wallet"></i> Account Balance</h2>
                <div class="balance-amount">
                    LKR <?php echo number_format($sender_balance, 2); ?>
                </div>
                <div class="balance-info">
                    <p><strong>Account Holder:</strong> <?php echo htmlspecialchars($sender_name); ?></p>
                    <p><strong>Available Balance:</strong> LKR <?php echo number_format($sender_balance, 2); ?></p>
                </div>
            </section>
            
            <!-- Transfer Form -->
            <section class="transfer-form">
                <h2><i class="fas fa-paper-plane"></i> Make a Transfer</h2>
                
                <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($transfer_success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Transfer successful! Funds have been transferred.
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="receiver_email" class="form-label">Receiver's Email</label>
                        <input type="email" id="receiver_email" name="receiver_email" class="form-input" 
                               placeholder="Enter recipient's email address" required
                               value="<?php echo isset($_POST['receiver_email']) ? htmlspecialchars($_POST['receiver_email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="amount" class="form-label">Amount to Transfer</label>
                        <div class="amount-input">
                            <span class="currency-symbol">LKR</span>
                            <input type="number" id="amount" name="amount" class="form-input" 
                                   placeholder="0.00" min="0.01" step="0.01" required
                                   value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <input type="text" id="description" name="description" class="form-input" 
                               placeholder="e.g., Dinner payment, Rent, etc."
                               value="<?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Transfer Funds
                    </button>
                </form>
            </section>
        </main>
        
        <!-- Footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Banking System. All rights reserved. | Secure Banking</p>
        </footer>
    </div>

    <script>
        // Show SweetAlert on successful transfer
        <?php if ($transfer_success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success',
                text: 'Transfer completed successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Optional: Redirect or refresh page
                // window.location.href = 'dashboard.php';
            });
        });
        <?php endif; ?>

        // Show SweetAlert on error
        <?php if (!empty($error_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error',
                text: '<?php echo $error_message; ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
        <?php endif; ?>

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const amountInput = document.getElementById('amount');
            const emailInput = document.getElementById('receiver_email');
            const balance = <?php echo $sender_balance; ?>;
            
            if (!emailInput.value.trim()) {
                e.preventDefault();
                Swal.fire('Error', 'Please enter recipient email address', 'error');
                return;
            }
            
            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                e.preventDefault();
                Swal.fire('Error', 'Amount must be greater than zero', 'error');
                return;
            }
            
            if (parseFloat(amountInput.value) > balance) {
                e.preventDefault();
                Swal.fire('Error', 'Insufficient balance for this transfer', 'error');
                return;
            }
        });
    </script>
</body>
</html>