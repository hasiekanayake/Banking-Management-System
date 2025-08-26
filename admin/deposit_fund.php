<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// Fetch all users for the dropdown
$users = $conn->query("SELECT id, name, accno, email FROM users");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accno = $_POST['accno'];
    $amount = floatval($_POST['amount']);
    
    // Validate the amount
    if ($amount > 0) {
        // Check if the account exists
        $stmt = $conn->prepare("SELECT id, name, email, balance FROM users WHERE accno = ?");
        $stmt->bind_param("s", $accno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Account found, proceed with deposit
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
            // Update the user's balance
            $update_stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $update_stmt->bind_param("di", $amount, $user_id);
            
            // Log the transaction
            $log_stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (?, 'credit', ?)");
            $log_stmt->bind_param("id", $user_id, $amount);

            if ($update_stmt->execute() && $log_stmt->execute()) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Success',
                                text: 'Funds deposited successfully!',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = 'dashboard.php';
                            });
                        });
                      </script>";
            } else {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error: " . $conn->error . "',
                                icon: 'error'
                            });
                        });
                      </script>";
            }
        } else {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Account not found.',
                            icon: 'error'
                        });
                    });
                  </script>";
        }
    } else {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Amount must be greater than zero.',
                        icon: 'error'
                    });
                });
              </script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deposit Funds | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
        }
        
        .brand p {
            font-size: 12px;
            opacity: 0.8;
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
        }
        
        .admin-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .admin-info p {
            font-size: 12px;
            opacity: 0.8;
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
        
        /* Deposit Form */
        .deposit-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: var(--primary);
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .form-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .deposit-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
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
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eaeaea;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--success), #2ecc71);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #2ecc71, var(--success));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .submit-btn i {
            margin-left: 8px;
        }
        
        .cancel-btn {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .cancel-btn:hover {
            background: #e9ecef;
            color: var(--dark);
        }
        
        .cancel-btn i {
            margin-right: 8px;
        }
        
        /* User Info Card */
        .user-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--accent);
        }
        
        .user-info-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .user-detail {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--dark);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .user-details {
                grid-template-columns: 1fr;
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
            
            .deposit-container {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .submit-btn, .cancel-btn {
                width: 100%;
                justify-content: center;
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
                <a href="deposit_fund.php" class="nav-item active">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Deposit Funds</span>
                </a>
                <a href="transactions.php" class="nav-item">
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
                <h1 class="page-title">Deposit Funds</h1>
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <div class="deposit-container">
                <div class="form-header">
                    <h2>Fund Deposit</h2>
                    <p>Add funds to a customer's account</p>
                </div>
                
                <form class="deposit-form" method="POST" action="">
                    <div class="form-group">
                        <label for="accno" class="form-label">Account Number</label>
                        <input type="text" id="accno" name="accno" class="form-input" 
                               placeholder="Enter account number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount" class="form-label">Amount to Deposit</label>
                        <div class="amount-input">
                            <span class="currency-symbol">LKR </span>
                            <input type="number" id="amount" name="amount" class="form-input" 
                                   placeholder="0.00" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reference" class="form-label">Reference (Optional)</label>
                        <input type="text" id="reference" name="reference" class="form-input" 
                               placeholder="Transaction reference">
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php" class="cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="submit-btn">
                            Process Deposit <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>
                
                <!-- PHP Integration for Account Information Display -->
                <?php
                require '../db.php';
                
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $accno = $_POST['accno'];
                    $amount = floatval($_POST['amount']);
                    
                    // Validate the amount
                    if ($amount > 0) {
                        // Check if the account exists
                        $stmt = $conn->prepare("SELECT id, name, email, balance, status FROM users WHERE accno = ?");
                        $stmt->bind_param("s", $accno);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            // Account found, proceed with deposit
                            $user = $result->fetch_assoc();
                            $user_id = $user['id'];
                            
                            // Update the user's balance
                            $update_stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                            $update_stmt->bind_param("di", $amount, $user_id);
                            
                            // Log the transaction
                            $reference = isset($_POST['reference']) ? $_POST['reference'] : 'Admin Deposit';
                            $log_stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, ?)");
                            $log_stmt->bind_param("ids", $user_id, $amount, $reference);
                
                            if ($update_stmt->execute() && $log_stmt->execute()) {
                                echo "<script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            Swal.fire({
                                                title: 'Success',
                                                text: 'Funds deposited successfully!',
                                                icon: 'success'
                                            }).then(() => {
                                                window.location.href = 'dashboard.php';
                                            });
                                        });
                                      </script>";
                            } else {
                                echo "<script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            Swal.fire({
                                                title: 'Error',
                                                text: 'Error: " . $conn->error . "',
                                                icon: 'error'
                                            });
                                        });
                                      </script>";
                            }
                        } else {
                            echo "<script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Account not found.',
                                            icon: 'error'
                                        });
                                    });
                                  </script>";
                        }
                    } else {
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Amount must be greater than zero.',
                                        icon: 'error'
                                    });
                                });
                              </script>";
                    }
                }
                
                // Display account information if account number is provided
                if (isset($_POST['accno']) && !empty($_POST['accno'])) {
                    $accno = $_POST['accno'];
                    $stmt = $conn->prepare("SELECT name, email, balance, status FROM users WHERE accno = ?");
                    $stmt->bind_param("s", $accno);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        ?>
                        <div class="user-info-card" id="userInfoCard">
                            <h3>Account Information</h3>
                            <div class="user-details">
                                <div class="user-detail">
                                    <span class="detail-label">Account Holder</span>
                                    <span class="detail-value" id="userName"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                                <div class="user-detail">
                                    <span class="detail-label">Current Balance</span>
                                    <span class="detail-value" id="userBalance">LKR <?php echo number_format($user['balance'], 2); ?></span>
                                </div>
                                <div class="user-detail">
                                    <span class="detail-label">Email Address</span>
                                    <span class="detail-value" id="userEmail"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="user-detail">
                                    <span class="detail-label">Account Status</span>
                                    <span class="detail-value" id="userStatus"><?php echo htmlspecialchars(ucfirst($user['status'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>

            </div>
        </div>
    </div>

    <script>
        // Account number lookup functionality
        const accnoInput = document.getElementById('accno');
        const userInfoCard = document.getElementById('userInfoCard');
        const userName = document.getElementById('userName');
        const userBalance = document.getElementById('userBalance');
        const userEmail = document.getElementById('userEmail');
        const userStatus = document.getElementById('userStatus');

        accnoInput.addEventListener('blur', function() {
            const accountNumber = this.value.trim();
            if (accountNumber.length > 0) {
                fetch('get_account_info.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'accno=' + encodeURIComponent(accountNumber)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userName.textContent = data.name;
                        userBalance.textContent = 'LKR ' + data.balance;
                        userEmail.textContent = data.email;
                        userStatus.textContent = data.status;
                        userInfoCard.style.display = 'block';
                    } else {
                        userInfoCard.style.display = 'none';
                    }
                });
            } else {
                userInfoCard.style.display = 'none';
            }
        });
        
        // Form validation
        const form = document.querySelector('.deposit-form');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const amountInput = document.getElementById('amount');
            const accnoInput = document.getElementById('accno');
            
            if (!accnoInput.value.trim()) {
                isValid = false;
                accnoInput.style.borderColor = '#e74c3c';
            } else {
                accnoInput.style.borderColor = '#ddd';
            }
            
            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                isValid = false;
                amountInput.style.borderColor = '#e74c3c';
            } else {
                amountInput.style.borderColor = '#ddd';
            }
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please check your inputs. Account number and a valid amount are required.',
                });
            }
        });
        
        // Add active class to clicked nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(nav => {
                    nav.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>