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
$stmt->close();

// Fetch recent transactions for the user (limit 5)
$transactions_stmt = $conn->prepare("SELECT type, amount, timestamp, 
    CASE 
        WHEN type = 'credit' THEN 'Deposit'
        WHEN type = 'debit' THEN 'Withdrawal'
        ELSE 'Transaction'
    END as description
    FROM transactions 
    WHERE user_id = ? 
    ORDER BY timestamp DESC 
    LIMIT 5");
$transactions_stmt->bind_param("i", $user_id);
$transactions_stmt->execute();
$transactions_result = $transactions_stmt->get_result();
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Banking System</title>
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
        
        .welcome-text h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .welcome-text p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .user-badge {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 15px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }
        
        .user-badge i {
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
        
        nav li a:hover, nav li a.active {
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
            margin-bottom: 25px;
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        
        /* Recent Transactions */
        .transactions-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .transactions-card h2 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .transactions-card h2 i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        .transactions-list {
            list-style: none;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-info {
            display: flex;
            flex-direction: column;
        }
        
        .transaction-type {
            font-weight: 600;
            color: var(--dark);
            text-transform: capitalize;
        }
        
        .transaction-desc {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .transaction-date {
            font-size: 12px;
            color: #95a5a6;
        }
        
        .transaction-amount {
            font-weight: 600;
            text-align: right;
        }
        
        .amount-credit {
            color: var(--success);
        }
        
        .amount-debit {
            color: var(--danger);
        }
        
        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: white;
        }
        
        .stat-icon.blue {
            background: var(--accent);
        }
        
        .stat-icon.green {
            background: var(--success);
        }
        
        .stat-info h3 {
            font-size: 20px;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .stat-info p {
            font-size: 14px;
            color: #7f8c8d;
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
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header>
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                <p>Manage your finances with ease and security</p>
            </div>
            <div class="user-badge">
                <i class="fas fa-user-circle"></i>
                <span>Account: <?php echo substr($_SESSION['name'], 0, 1) . str_repeat('*', strlen($_SESSION['name']) - 1); ?></span>
            </div>
        </header>
        
        <!-- Navigation -->
        <nav>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> Transactions</a></li>
                <li><a href="transfer.php"><i class="fas fa-money-bill-wave"></i> Transfer Funds</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>LKR <?php echo number_format($balance, 2); ?></h3>
                    <p>Available Balance</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <h3>5</h3>
                    <p>Total Transactions</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <main>
            <!-- Balance Card -->
            <section class="balance-card">
                <h2><i class="fas fa-chart-line"></i> Account Overview</h2>
                <div class="balance-amount">
                    LKR <?php echo number_format($balance, 2); ?>
                </div>
                <div class="action-buttons">
                    <a href="transfer.php" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Transfer Funds
                    </a>
                    <a href="transactions.php" class="btn btn-outline">
                        <i class="fas fa-history"></i> View History
                    </a>
                </div>
            </section>
            
            <!-- Recent Transactions -->
            <section class="transactions-card">
                <h2><i class="fas fa-clock"></i> Recent Transactions</h2>
                <ul class="transactions-list">
                    <?php if ($transactions_result->num_rows > 0): ?>
                        <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                        <li class="transaction-item">
                            <div class="transaction-info">
                                <span class="transaction-type"><?php echo htmlspecialchars($transaction['type']); ?></span>
                                <span class="transaction-desc"><?php echo htmlspecialchars($transaction['description']); ?></span>
                                <span class="transaction-date"><?php echo date('M j, Y g:i A', strtotime($transaction['timestamp'])); ?></span>
                            </div>
                            <div class="transaction-amount <?php echo $transaction['type'] === 'credit' ? 'amount-credit' : 'amount-debit'; ?>">
                                <?php echo ($transaction['type'] === 'credit' ? '+' : '-'); ?> LKR <?php echo number_format($transaction['amount'], 2); ?>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="transaction-item">
                            <div class="transaction-info">
                                <span class="transaction-type">No transactions yet</span>
                                <span class="transaction-desc">Your transactions will appear here</span>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </section>
        </main>
        
        <!-- Footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Banking System. All rights reserved. | Secure Banking</p>
        </footer>
    </div>
</body>
</html>