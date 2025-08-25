<?php
    // PHP code for form processing
    require '../db.php';
    session_start();
    
    if (!isset($_SESSION['admin'])) {
        header("Location: index.php");
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlentities($_POST['fname']);
        $accno = htmlentities($_POST['accno']);
        $address = htmlentities($_POST['address']);
        $email = htmlentities($_POST['email']);
        $nic = htmlentities($_POST['nic']);
        $phone = htmlentities($_POST['phone']);
        $date_of_birth = htmlentities($_POST['date_of_birth']);
        $password = 123; // Default password
        $balance = isset($_POST['initial_deposit']) ? (float)$_POST['initial_deposit'] : 0;
        $status = 'active';
    
        $stmt = $conn->prepare("INSERT INTO users (name, accno, address, email, nic, phone, date_of_birth, password, balance, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssds", $name, $accno, $address, $email, $nic, $phone, $date_of_birth, $password, $balance, $status);
        
        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Success', 
                        text: 'User registered successfully!', 
                        icon: 'success',
                        confirmButtonColor: 'var(--primary)'
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
                        text: 'Error: " . addslashes($conn->error) . "', 
                        icon: 'error',
                        confirmButtonColor: 'var(--primary)'
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
    <title>Register New User | Admin Dashboard</title>
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
        
        /* Registration Form */
        .registration-container {
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
        
        .registration-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
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
        
        .form-input::placeholder {
            color: #bbb;
        }
        
        .submit-btn {
            grid-column: span 2;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 6px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .submit-btn i {
            margin-left: 8px;
        }
        
        /* Form Notes */
        .form-note {
            grid-column: span 2;
            background: #f8f9fa;
            border-left: 4px solid var(--accent);
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .registration-form {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width, .submit-btn {
                grid-column: 1;
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
            
            .registration-container {
                padding: 20px;
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
                <a href="register_user.php" class="nav-item active">
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
                <h1 class="page-title">Register New User</h1>
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <div class="registration-container">
                <div class="form-header">
                    <h2>Create New Account</h2>
                    <p>Fill in the details below to register a new banking customer</p>
                </div>
                
                <form class="registration-form" method="POST" action="">
                    <div class="form-group">
                        <label for="fname" class="form-label">Full Name</label>
                        <input type="text" id="fname" name="fname" class="form-input" placeholder="Enter full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="accno" class="form-label">Account Number</label>
                        <input type="text" id="accno" name="accno" class="form-input" placeholder="Enter account number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter email address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-input" placeholder="Enter phone number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nic" class="form-label">NIC Number</label>
                        <input type="text" id="nic" name="nic" class="form-input" placeholder="Enter NIC number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-input" placeholder="Enter full address" required>
                    </div>
                    
                    <div class="form-note">
                        <p><strong>Note:</strong> The default password for new users is "123". Users will be prompted to change their password upon first login.</p>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        Register User <i class="fas fa-user-plus"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Add active class to clicked nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(nav => {
                    nav.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        
        // Form validation
        const form = document.querySelector('.registration-form');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#e74c3c';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields',
                });
            }
        });
        
        // Input validation styling
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#ddd';
                }
            });
        });
    </script>
</body>
</html>