<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// Handle user deletion
if (isset($_GET['delete'])) {
  $user_id = intval($_GET['delete']);
  $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE accno = ?");
  $stmt->bind_param("i", $user_id);
  if ($stmt->execute()) {
      echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({title: 'Success', text: 'User deleted successfully!', icon: 'success'}).then(() => { window.location.href = 'manage_users.php'; }); });</script>";
  } else {
      echo "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire('Error', 'Error: " . $conn->error . "', 'error'); });</script>";
  }
}

// Fetch all active users
$result = $conn->query("SELECT accno, name, email, balance, status FROM users WHERE status = 'active'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Users | Admin Dashboard</title>
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
        
        .user-count {
            background: var(--accent);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 18px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: #f5f7fa;
            border-radius: 6px;
            padding: 8px 15px;
            width: 250px;
        }
        
        .search-box input {
            border: none;
            background: transparent;
            padding: 8px;
            width: 100%;
            outline: none;
        }
        
        .search-box i {
            color: #7f8c8d;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f5f7fa;
        }
        
        th {
            text-align: left;
            padding: 15px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #eaeaea;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.15);
            color: #27ae60;
        }
        
        .balance {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn-edit {
            background: rgba(52, 152, 219, 0.15);
            color: var(--accent);
        }
        
        .btn-edit:hover {
            background: rgba(52, 152, 219, 0.25);
        }
        
        .btn-deactivate {
            background: rgba(231, 76, 60, 0.15);
            color: var(--danger);
        }
        
        .btn-deactivate:hover {
            background: rgba(231, 76, 60, 0.25);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
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
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .users-table-container {
                padding: 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
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
            <a href="manage_users.php" class="nav-item active">
                <i class="fas fa-users-cog"></i>
                <span>Manage Users</span>
            </a>
            <a href="deposit_fund.php" class="nav-item">
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
            <h1 class="page-title">Manage Users</h1>
            <div class="user-count">
                <i class="fas fa-users"></i>
                <span id="user-count">5 Active Users</span>
            </div>
        </div>
        
        <div class="users-table-container">
            <div class="table-header">
                <h2 class="table-title">Active Users</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search users..." id="search-input">
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>100001</td>
                        <td>John Smith</td>
                        <td>john.smith@example.com</td>
                        <td class="balance">LKR 25,450.00</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-deactivate">
                                    <i class="fas fa-user-times"></i> Deactivate
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>100002</td>
                        <td>Emma Johnson</td>
                        <td>emma.j@example.com</td>
                        <td class="balance">LKR 18,720.50</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-deactivate">
                                    <i class="fas fa-user-times"></i> Deactivate
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>100003</td>
                        <td>Michael Brown</td>
                        <td>m.brown@example.com</td>
                        <td class="balance">LKR 52,890.75</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-deactivate">
                                    <i class="fas fa-user-times"></i> Deactivate
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>100004</td>
                        <td>Sarah Williams</td>
                        <td>sarahw@example.com</td>
                        <td class="balance">LKR 12,340.00</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-deactivate">
                                    <i class="fas fa-user-times"></i> Deactivate
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>100005</td>
                        <td>David Miller</td>
                        <td>d.miller@example.com</td>
                        <td class="balance">LKR 37,650.25</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-deactivate">
                                    <i class="fas fa-user-times"></i> Deactivate
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Search functionality
        document.getElementById('search-input').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
        
        // Deactivate user confirmation
        document.querySelectorAll('.btn-deactivate').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const accountNumber = row.querySelector('td:first-child').textContent;
                const userName = row.querySelector('td:nth-child(2)').textContent;
                
                Swal.fire({
                    title: 'Deactivate User?',
                    html: `Are you sure you want to deactivate <strong>${userName}</strong> (Account: ${accountNumber})?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#7f8c8d',
                    confirmButtonText: 'Yes, deactivate',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Simulate deactivation
                        row.style.opacity = '0.5';
                        Swal.fire(
                            'Deactivated!',
                            'User account has been deactivated.',
                            'success'
                        );
                    }
                });
            });
        });
        
        // Update user count
        function updateUserCount() {
            const visibleRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
            document.getElementById('user-count').textContent = `${visibleRows} Active Users`;
        }
        
        // Initial user count
        updateUserCount();
        
        // Update count when searching
        document.getElementById('search-input').addEventListener('input', updateUserCount);
    </script>
</body>
</html>