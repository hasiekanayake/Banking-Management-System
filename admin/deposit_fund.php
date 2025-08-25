

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <style>
      body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
       
      }
      .container1 {
        background-color: #000000;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 20%;
        max-width: 900px;
        text-align: center;
        margin: 20px;
      }
      .container2 {
        background-color: #000000;
        border-radius: 10px;
        box-shadow: 2px 4px 5px rgba(0, 0, 0, 0.1);
        padding: 30px;
        padding-top: 10px;
        width: 100%;
        max-width: 1000px;
        text-align: center;
        margin: 20px;
        color: #ebf5ff;
        font-family: "Times New Roman", Times, serif;
      }
      .container2 h1 {
        padding-top: 40px;
      }
      .container2 img {
        padding-top: 10px;
        width: 100%;
      }

      .header {
        margin-bottom: 20px;
        padding-bottom: 30px;
      }
      .header h1 {
        color: #6fb2fa;
        font-family: Cambria, Cochin, Georgia, Times, "Times New Roman", serif;
        text-shadow: 0px 1px 2px #1f1e1e;
      }
     
      .header h1 img {
        width: 20%;

        border-radius: 50px;
      }
      .card-container {
        display: block;
        flex-wrap: wrap;
        justify-content: space-around;
      }
      .card {
        background-color: #007bff;
        color: #fff;
        padding: 20px;
        border-radius: 10px;
        margin: 10px;
        width: 200px;
        text-align: center;
        transition: transform 0.3s, background-color 0.3s;
      }
      .card a {
        text-decoration: none;
        color: #fff;
        font-weight: bold;
      }
      .card:hover {
        transform: scale(1.05);
        background-color: #0056b3;
      }
      .footer {
        margin-top: 20px;
        font-size: 0.9em;
        color: #777;
      }
      .main_container {
        display: flex;
        width: 100%;
        margin: 100px;
        margin-top: 0px;
      }
   
    </style>
  </head>
  <body>
    <div class="main_container">
      <div class="container1">
        <div class="header">
          <?php include '../includes/admin_header.php'; ?>

          <h1>Welcome Admin</h1>
        </div>
        <div class="card-container">
          <div class="card">
            <a href="register_user.php">Register New User</a>
          </div>
          <div class="card">
            <a href="manage_users.php">Manage Users</a>
          </div>
          <div class="card">
            <a href="deposit_fund.php">Deposit Fund</a>
          </div>
          <div class="card">
            <a href="transactions.php">View Transactions</a>
          </div>
          <div class="card">
            <a href="logout.php">Logout</a>
          </div>
          <div class="footer">
      <?php include '../includes/admin_footer.php'; ?>
    </div>
        </div>
      </div>
      <div class="container2">
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
    <title>Admin Dashboard - Deposit Funds</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
           
        }

        .container {
            background-color: #ffffff;
            color:rgb(7, 31, 57);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 700px;
            margin: 20px;
            text-align: center;
            margin-left: 50px;
        }

        h1 {
            color: black;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: inline-block;
            text-align: left;
            width: 100%;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            background-color: #071330;
            color: white;
            padding: 12px 50px;
            margin-top: 30px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .card-container {
            display: block;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .card {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin: 10px;
            width: 200px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
        }

        .card a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
        }

        .card:hover {
            transform: scale(1.05);
            background-color: #0056b3;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }

        .back-btn {
            display: block;
            margin-top: 40px;
            text-align: center;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            width:25%;
            margin-left:500px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }
        h1{
            color: #6fb2fa;
        }
    </style>
</head>
<body>
<h1>Deposit Funds</h1>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container">
        
        
        <form method="POST" action="">
            <label for="accno">Account Number:</label>
            <input type="text" name="accno" required><br>

            <label for="name">Name:</label>
            <input type="text" name="name" required><br>

            <label for="email">Email:</label>
            <input type="text" name="email" required><br>

            <label for="amount">Amount (LKR):</label>
            <input type="number" name="amount" required><br>

            <button type="submit">Deposit</button>
        </form>

        <!-- Back to Dashboard -->
       
    </div>
</body>
</html>

    </div>
    
  </body>
</html>
