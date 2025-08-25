

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
        height:600px;
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
            margin-left:600px;
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
      
    
  </body>
</html>

<?php
require '../db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// Handle form submission for date range filter
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Fetch transactions based on the selected date range
$stmt = $conn->prepare("SELECT t.id, u.name, t.type, t.amount, t.timestamp 
                        FROM transactions t
                        JOIN users u ON t.user_id = u.id
                        WHERE t.timestamp BETWEEN ? AND ?
                        ORDER BY t.timestamp DESC");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transactions by Time Period</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        form input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 50px;
            margin-left: 50px;
            margin-top: 25px;
        }
        form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            width : 150px;
        }
        form button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            
        }
        table th, table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #071330;
            color: #ffffff;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #e9e9e9;
        }
        .back-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            width:25%;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Transactions for Selected Period</h1>

        <!-- Date Range Filter Form -->
        <form method="POST" action="">
            <input type="date" name="start_date" value="<?= $start_date ?>" required>
            <input type="date" name="end_date" value="<?= $end_date ?>" required><br><br>
            <button type="submit">SEARCH</button>
        </form>

        <!-- Transactions Table -->
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>User Name</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= ucfirst($row['type']) ?></td>
                        <td>LKR <?= number_format($row['amount'], 2) ?></td>
                        <td><?= $row['timestamp'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
