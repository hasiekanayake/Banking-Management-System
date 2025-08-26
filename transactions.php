<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch transaction history
$stmt = $conn->prepare("SELECT type, amount, timestamp, 
    CASE 
        WHEN type = 'credit' THEN 'Deposit'
        WHEN type = 'debit' THEN 'Withdrawal'
        ELSE 'Transaction'
    END as description 
    FROM transactions WHERE user_id = ? ORDER BY timestamp DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_transactions = $result->num_rows;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Banking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .header-content p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .transaction-count {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 15px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }
        
        .transaction-count i {
            margin-right: 10px;
            font-size: 20px;
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
            margin-bottom: 30px;
        }
        
        /* Transactions Section */
        .transactions-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }
        
        .section-header h2 {
            color: var(--dark);
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section-header h2 i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        /* Transactions Table */
        .transaction-table {
            width: 100%;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #eaeaea;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            color: #555;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .transaction-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .type-credit {
            background: rgba(46, 204, 113, 0.15);
            color: var(--success);
        }
        
        .type-debit {
            background: rgba(231, 76, 60, 0.15);
            color: var(--danger);
        }
        
        .transaction-amount {
            font-weight: 600;
        }
        
        .amount-credit {
            color: var(--success);
        }
        
        .amount-debit {
            color: var(--danger);
        }
        
        .transaction-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .transaction-desc {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* No Transactions */
        .no-transactions {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-transactions i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .no-transactions p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
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
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
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
            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            nav ul {
                flex-direction: column;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .transaction-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 600px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <div class="header-content">
                <h1>Transaction History</h1>
                <p>View all your financial transactions in one place</p>
            </div>
            <div class="transaction-count">
                <i class="fas fa-exchange-alt"></i>
                <span><?php echo $total_transactions; ?> Transactions</span>
            </div>
        </header>
        
        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                <li><a href="transfer.php"><i class="fas fa-money-bill-wave"></i> Transfer Funds</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main>
            <section class="transactions-container">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> All Transactions</h2>
                </div>
                
                <div class="transaction-table">
                    <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount (LKR)</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="transaction-type type-<?php echo $row['type']; ?>">
                                        <?php echo ucfirst($row['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['description']); ?></div>
                                </td>
                                <td>
                                    <span class="transaction-amount amount-<?php echo $row['type']; ?>">
                                        <?php echo ($row['type'] === 'credit' ? '+' : '-'); ?>
                                        LKR <?php echo number_format($row['amount'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="transaction-date">
                                        <?php echo date('M j, Y g:i A', strtotime($row['timestamp'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-transactions">
                        <i class="fas fa-exchange-alt"></i>
                        <p>No transactions found</p>
                        <p>Your transactions will appear here once you make them</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="transfer.php" class="btn btn-outline">
                    <i class="fas fa-paper-plane"></i> Make a Transfer
                </a>
            </div>
        </main>
        
        <!-- Footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Banking System. All rights reserved. | Secure Banking</p>
        </footer>
    </div>
</body>
</html>