<?php
require 'db.php';
session_start();

// Handle Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role']; // Get role (user/admin) from form
    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);

    if ($role === 'user') {
        // User Login
        $stmt = $conn->prepare("SELECT id, name ,status FROM users WHERE name = ? AND password = ? ");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $status);
            $stmt->fetch();
            
            if ($status == 'active') {
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                header("Location: dashboard.php");
            } else {
                $error = "Currently Inactive";
            }
        } else {
            $error = "Invalid username or password for user!";
        }
        
// if($status='active'){
//         if ($stmt->num_rows > 0) {
//             $stmt->bind_result($id, $name);
//             $stmt->fetch();
//             $_SESSION['user_id'] = $id;
//             $_SESSION['name'] = $name;
//             header("Location: dashboard.php");
//         } else {
//             $error = "Invalid username or password for user!";
//         }}
//         else{
//             $error = "Currently Inactive";
//         }
    } elseif ($role === 'admin') {
        // Admin Login
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $_SESSION['admin'] = true;
            header("Location: admin\dashboard.php");
        } else {
            $error = "Invalid username or password for admin!";
        }
    } else {
        $error = "Invalid role selected!";
    }
}
?>



<?php
if (isset($error)) {
    // echo "<p style='color: red;'>$error</p>";
    // echo "<script type='text/javascript'>alert('$error');</script>";
    echo "<script type='text/javascript'>
           window.onload = function() {
               alert('$error');
           }
       </script>";


}
?>

<!-- <form method="POST" action="">
    
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br>
    
    <button type="submit">Login</button>
</form> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Royal Trust Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .lgnMain {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 80px);
        }
        
        /* Left Login Section - REDUCED WIDTH */
        .lg1 {
            flex: 0 0 550px; /* Changed from flex: 1 and added fixed width */
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        
        .lgmh1 {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .lgmh1 img {
            height: 50px;
            margin-right: 15px;
        }
        
        .lgmh1 h1 {
            font-size: 28px;
            color: #1a5276;
            font-weight: 700;
        }
        
        .lgmh1 span {
            display: block;
            font-size: 14px;
            color: #7f8c8d;
            font-weight: 400;
            margin-top: 5px;
        }
        
        .lg1 h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .lgfrm {
            max-width: 100%; /* Changed from 400px to take full width of container */
        }
        
        .lgfrm label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .lgfrm input, .lgfrm select {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .lgfrm input:focus, .lgfrm select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .lgfrm a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .lgfrm a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .lgfrm button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a5276, #2c3e50);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .lgfrm button:hover {
            background: linear-gradient(135deg, #2c3e50, #1a5276);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        /* Right Side - Video Showcase in a Row */
        .lg2 {
            flex: 1;
            display: flex;
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #1a5276 0%, #2c3e50 100%);
            overflow-x: auto;
        }
        
        .video-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
            cursor: pointer;
            min-width: 300px;
            flex: 1;
        }
        
        .video-card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .video-card:hover video {
            transform: scale(1.05);
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(26, 82, 118, 0.9) 0%, rgba(26, 82, 118, 0.4) 50%, rgba(26, 82, 118, 0.2) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            color: white;
            padding: 25px;
            opacity: 1;
        }
        
        .video-content {
            transform: translateY(0);
            transition: transform 0.4s ease;
        }
        
        .video-card:hover .video-content {
            transform: translateY(-5px);
        }
        
        .video-overlay h3 {
            font-size: 22px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .video-overlay p {
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .video-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            color: white;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .video-button i {
            margin-left: 5px;
            font-size: 12px;
        }
        
        .video-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .video-card:nth-child(2) .video-overlay {
            background: linear-gradient(to top, rgba(41, 128, 185, 0.9) 0%, rgba(41, 128, 185, 0.4) 50%, rgba(41, 128, 185, 0.2) 100%);
        }
        
        .video-card:nth-child(3) .video-overlay {
            background: linear-gradient(to top, rgba(39, 174, 96, 0.9) 0%, rgba(39, 174, 96, 0.4) 50%, rgba(39, 174, 96, 0.2) 100%);
        }
        
        /* Video controls */
        .video-controls {
            position: absolute;
            bottom: 15px;
            right: 15px;
            z-index: 10;
        }
        
        .control-btn {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-links {
            display: flex;
            gap: 20px;
        }
        
        .footer-links a {
            color: #ecf0f1;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #3498db;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icons a {
            color: white;
            font-size: 20px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: #3498db;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .lg1 {
                flex: 0 0 450px; /* Further reduce width on medium screens */
            }
        }
        
        @media (max-width: 1024px) {
            .lgnMain {
                flex-direction: column;
            }
            
            .lg1 {
                flex: 0 0 auto; /* Reset fixed width on smaller screens */
                width: 100%;
            }
            
            .lg2 {
                order: -1;
                height: 300px;
            }
            
            .video-card {
                min-width: 250px;
            }
        }
        
        @media (max-width: 768px) {
            .lg2 {
                flex-direction: column;
                height: auto;
                overflow-x: visible;
            }
            
            .video-card {
                min-width: auto;
                height: 250px;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 600px) {
            .lg1 {
                padding: 25px;
            }
            
            .lgmh1 {
                flex-direction: column;
                text-align: center;
            }
            
            .lgmh1 img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .lg2 {
                padding: 15px;
                gap: 10px;
            }
            
            .video-overlay {
                padding: 15px;
            }
            
            .video-overlay h3 {
                font-size: 18px;
            }
            
            .video-overlay p {
                font-size: 14px;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="lgnMain">
        <div class="lg1">
            <div class="lgmh1">
                <img src="img/Royal Trust Bank (1).png" alt="Royal Trust Bank Logo">
                <h1>Royal Trust Bank <span>Seamless Banking for a Digital World</span></h1>
            </div>
            
            <h1>Login</h1>
            
            <div class="lgfrm">
                <form method="POST" action="">
                    <label for="username">User Name</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    
                    <label><a href="test.html">Forgot Password?</a></label>
                    
                    <label for="role">Select Your Role</label>
                    <select name="role" id="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    
                    <button type="submit">Login <i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
        </div>
        
        <div class="lg2">
            <div class="video-card">
                <video autoplay loop muted>
                    <source src="img/1.mp4" type="video/mp4">
                </video>
                <div class="video-overlay">
                    <div class="video-content">
                        <h3>Digital Banking</h3>
                        <p>Access your accounts anytime, anywhere with our secure digital platform</p>
                    </div>
                </div>
            </div>
            
            <div class="video-card">
                <video autoplay loop muted>
                    <source src="img/2.mp4" type="video/mp4">
                </video>
                <div class="video-overlay">
                    <div class="video-content">
                        <h3>Secure Transactions</h3>
                        <p>Bank with confidence using our advanced security measures</p>
                    </div>
                </div>
            </div>
            
            <div class="video-card">
                <video autoplay loop muted>
                    <source src="img/3.mp4" type="video/mp4">
                </video>
                <div class="video-overlay">
                    <div class="video-content">
                        <h3>Financial Growth</h3>
                        <p>Grow your wealth with our investment and savings solutions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div>© 2023 Royal Trust Bank. All rights reserved.</div>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
                <a href="#">Security</a>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Add interactivity to video controls
        document.querySelectorAll('.control-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const video = this.closest('.video-card').querySelector('video');
                const icon = this.querySelector('i');
                
                if (video.muted) {
                    video.muted = false;
                    icon.classList.remove('fa-volume-mute');
                    icon.classList.add('fa-volume-up');
                } else {
                    video.muted = true;
                    icon.classList.remove('fa-volume-up');
                    icon.classList.add('fa-volume-mute');
                }
            });
        });
    </script>
</body>
</html>

