<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | Royal Trust Bank</title>
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
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
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
        }
        
        .welcome-banner {
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .welcome-text h2 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .welcome-text p {
            opacity: 0.9;
            max-width: 600px;
        }
        
        .date-display {
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 15px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            cursor: pointer;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: white;
        }
        
        .card-icon.user {
            background: linear-gradient(to right, #3498db, #2980b9);
        }
        
        .card-icon.manage {
            background: linear-gradient(to right, #2ecc71, #27ae60);
        }
        
        .card-icon.deposit {
            background: linear-gradient(to right, #9b59b6, #8e44ad);
        }
        
        .card-icon.transaction {
            background: linear-gradient(to right, #e67e22, #d35400);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .card-content p {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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
        
        .stat-icon.orange {
            background: var(--warning);
        }
        
        .stat-icon.red {
            background: var(--danger);
        }
        
        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .stat-info p {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        /* Recent Activity */
        .recent-activity {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            font-size: 20px;
            color: var(--dark);
            font-weight: 600;
        }
        
        .section-header a {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            color: white;
            background: var(--accent);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content h4 {
            font-size: 16px;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .activity-content p {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .activity-time {
            font-size: 12px;
            color: #95a5a6;
            text-align: right;
        }
        
        /* Footer */
        .dashboard-footer {
            background: white;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-cards,
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
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
            
            .dashboard-cards,
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
            }
            
            .date-display {
                margin-top: 15px;
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
                <a href="#" class="nav-item active">
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
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h2>Welcome back, Admin!</h2>
                    <p>Manage your banking system efficiently with this admin dashboard. Monitor transactions, manage users, and oversee financial operations.</p>
                </div>
                <div class="date-display">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="current-date">October 16, 2023</span>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>2,548</h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$8.2M</h3>
                        <p>Total Deposits</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12,487</h3>
                        <p>Transactions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>100%</h3>
                        <p>System Security</p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card" onclick="location.href='register_user.php'">
                    <div class="card-header">
                        <div class="card-icon user">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3 class="card-title">Register New User</h3>
                    </div>
                    <div class="card-content">
                        <p>Create new customer accounts with secure credentials and personalized banking options.</p>
                    </div>
                </div>
                
                <div class="card" onclick="location.href='manage_users.php'">
                    <div class="card-header">
                        <div class="card-icon manage">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="card-title">Manage Users</h3>
                    </div>
                    <div class="card-content">
                        <p>View, edit, or deactivate user accounts. Monitor user activity and manage permissions.</p>
                    </div>
                </div>
                
                <div class="card" onclick="location.href='deposit_fund.php'">
                    <div class="card-header">
                        <div class="card-icon deposit">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 class="card-title">Deposit Funds</h3>
                    </div>
                    <div class="card-content">
                        <p>Process deposits, verify transactions, and manage customer fund allocations.</p>
                    </div>
                </div>
                
                <div class="card" onclick="location.href='transactions.php'">
                    <div class="card-header">
                        <div class="card-icon transaction">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3 class="card-title">View Transactions</h3>
                    </div>
                    <div class="card-content">
                        <p>Monitor all financial transactions, filter by date, amount, or user for detailed analysis.</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <div class="section-header">
                    <h2>Recent Activity</h2>
                    <a href="#">View All</a>
                </div>
                
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h4>New User Registered</h4>
                            <p>John Doe created a new savings account</p>
                        </div>
                        <div class="activity-time">2 hours ago</div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Deposit Processed</h4>
                            <p>$2,500 deposited to account #XXXX7890</p>
                        </div>
                        <div class="activity-time">5 hours ago</div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Security Update</h4>
                            <p>System security patches applied successfully</p>
                        </div>
                        <div class="activity-time">Yesterday</div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Wire Transfer</h4>
                            <p>International transfer completed to UK bank</p>
                        </div>
                        <div class="activity-time">October 15, 2023</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <footer class="dashboard-footer">
        <p>© 2023 Royal Trust Bank. All rights reserved. | Secure Banking System</p>
    </footer>

    <script>
        // Set current date
        const now = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', options);
        
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