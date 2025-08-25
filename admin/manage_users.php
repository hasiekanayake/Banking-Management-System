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
        
        /* Users Table */
        .users-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: #f5f7fa;
            border-radius: 8px;
            padding: 8px 15px;
            width: 300px;
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
            margin-right: 8px;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #eaeaea;
        }
        
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            color: #555;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .users-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .account-number {
            font-weight: 600;
            color: var(--primary);
        }
        
        .user-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .user-email {
            color: #7f8c8d;
        }
        
        .balance {
            font-weight: 600;
            color: var(--success);
        }
        
        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background: rgba(39, 174, 96, 0.15);
            color: var(--success);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
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
        
        /* No Users Message */
        .no-users {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-users i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .no-users p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .users-table {
                display: block;
                overflow-x: auto;
            }
            
            .search-box {
                width: 250px;
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
            
            .users-container {
                padding: 15px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
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
                <div>
                    <h1 class="page-title">Manage Users</h1>
                    <span class="user-count"><?php echo $result->num_rows; ?> Active Users</span>
                </div>
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <div class="users-container">
                <div class="table-header">
                    <h2>Active Users</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search users..." id="searchInput">
                    </div>
                </div>
                
                <?php if ($result->num_rows > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="account-number"><?php echo $row['accno']; ?></span></td>
                            <td><span class="user-name"><?php echo $row['name']; ?></span></td>
                            <td><span class="user-email"><?php echo $row['email']; ?></span></td>
                            <td><span class="balance">LKR <?php echo number_format($row['balance'], 2); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_user.php?accno=<?php echo $row['accno']; ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $row['accno']; ?>" class="btn btn-deactivate" onclick="return confirmDeactivate()">
                                        <i class="fas fa-user-times"></i> Deactivate
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-users">
                    <i class="fas fa-users"></i>
                    <p>No active users found.</p>
                    <a href="register_user.php" class="btn btn-edit">
                        <i class="fas fa-user-plus"></i> Register New User
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('.user-name').textContent.toLowerCase();
                const email = row.querySelector('.user-email').textContent.toLowerCase();
                const account = row.querySelector('.account-number').textContent.toLowerCase();
                
                if (name.includes(searchText) || email.includes(searchText) || account.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Confirmation for deactivation
        function confirmDeactivate() {
            event.preventDefault();
            const href = event.target.getAttribute('href');
            
            Swal.fire({
                title: 'Deactivate User?',
                text: "This user will no longer be able to access their account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Yes, deactivate!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
            
            return false;
        }
        
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