<?php
    require '../db.php';
    session_start();
    
    if (!isset($_SESSION['admin'])) {
        header("Location: index.php");
        exit;
    }
    
    // Handle form submission for date range filter
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');
    
    // Fetch transactions based on the selected date range
    $stmt = $conn->prepare("SELECT t.id, u.name, t.type, t.amount, t.timestamp 
                            FROM transactions t
                            JOIN users u ON t.user_id = u.id
                            WHERE DATE(t.timestamp) BETWEEN ? AND ?
                            ORDER BY t.timestamp DESC");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions_count = $result->num_rows;
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transactions | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 40px);
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
            color: white;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            box-shadow: var(--card-shadow);
            z-index: 10;
        }
        
        .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .brand h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
            color: white;
        }
        
        .brand p {
            font-size: 12px;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
        
        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin-right: 15px;
            color: white;
        }
        
        .admin-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
            color: white;
        }
        
        .admin-info p {
            font-size: 12px;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .nav-links {
            flex: 1;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent);
        }
        
        .nav-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .logout {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            background: rgba(231, 76, 60, 0.2);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            font-weight: 600;
        }
        
        .logout-btn:hover {
            background: rgba(231, 76, 60, 0.3);
        }
        
        .logout-btn i {
            margin-right: 10px;
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background: #f9fafb;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .page-title {
            font-size: 24px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .transactions-count {
            background: var(--accent);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .back-link {
            display: flex;
            align-items: center;
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        /* Filter Section */
        .filter-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .filter-header h2 {
            font-size: 20px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .filter-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .filter-btn:hover {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            transform: translateY(-2px);
        }
        
        .filter-btn i {
            margin-right: 8px;
        }
        
        /* Transactions Table */
        .transactions-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-header h2 {
            font-size: 20px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .export-btn {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.3);
            border-radius: 6px;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .export-btn:hover {
            background: rgba(46, 204, 113, 0.2);
        }
        
        .export-btn i {
            margin-right: 5px;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .transactions-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #eaeaea;
        }
        
        .transactions-table td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            color: #555;
        }
        
        .transactions-table tr:last-child td {
            border-bottom: none;
        }
        
        .transactions-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .transaction-id {
            font-weight: 600;
            color: var(--primary);
        }
        
        .user-name {
            font-weight: 500;
            color: var(--dark);
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
        
        /* No Transactions Message */
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
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .filter-form {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .transactions-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
                height: auto;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px 0;
            }
            
            .brand {
                padding: 15px;
            }
            
            .nav-links {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .nav-item {
                border-left: none;
                border-bottom: 3px solid transparent;
                flex-direction: column;
                padding: 10px 15px;
                font-size: 12px;
            }
            
            .nav-item:hover, .nav-item.active {
                border-left-color: transparent;
                border-bottom-color: var(--accent);
            }
            
            .nav-item i {
                margin-right: 0;
                margin-bottom: 5px;
                font-size: 16px;
            }
            
            .admin-profile {
                display: none;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .filter-container, .transactions-container {
                padding: 15px;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="brand">
                <h1>Royal Trust Bank</h1>
                <p>Admin Dashboard</p>
            </div>
            
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <h3>Admin User</h3>
                    <p>System Administrator</p>
                </div>
            </div>
            
            <div class="nav-links">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="register_user.php" class="nav-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Register User</span>
                </a>
                <a href="manage_users.php" class="nav-item">
                    <i class="fas fa-users-cog"></i>
                    <span>Manage Users</span>
                </a>
                <a href="deposit_fund.php" class="nav-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Deposit Funds</span>
                </a>
                <a href="transactions.php" class="nav-item active">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            
            <div class="logout">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Transaction History</h1>
                    <span class="transactions-count"><?php echo $transactions_count; ?> Transactions</span>
                </div>
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-container">
                <div class="filter-header">
                    <h2>Filter Transactions by Date Range</h2>
                </div>
                
                <form class="filter-form" method="POST" action="">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-input" 
                               value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-input" 
                               value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>
            </div>
            
            <!-- Transactions Table -->
            <div class="transactions-container">
                <div class="table-header">
                    <h2>Transaction Records</h2>
                    <button class="export-btn">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
                
                <?php if ($transactions_count > 0): ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="transaction-id">#<?php echo $row['id']; ?></span></td>
                            <td><span class="user-name"><?php echo $row['name']; ?></span></td>
                            <td>
                                <span class="transaction-type <?php echo 'type-' . $row['type']; ?>">
                                    <?php echo ucfirst($row['type']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="transaction-amount <?php echo 'amount-' . $row['type']; ?>">
                                    LKR <?php echo number_format($row['amount'], 2); ?>
                                </span>
                            </td>
                            <td><span class="transaction-date"><?php echo $row['timestamp']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-transactions">
                    <i class="fas fa-exchange-alt"></i>
                    <p>No transactions found for the selected period.</p>
                    <p>Try adjusting your date range.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Set default dates to current month
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to clicked nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    document.querySelectorAll('.nav-item').forEach(nav => {
                        nav.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
            
            // Set end date to today if not set
            if (!document.getElementById('end_date').value) {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('end_date').value = today;
            }
            
            // Set start date to first day of month if not set
            if (!document.getElementById('start_date').value) {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                document.getElementById('start_date').value = firstDay;
            }
        });
    </script>
</body>
</html>