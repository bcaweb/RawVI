<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: adminindex.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "rawvi");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// USER SUMMARY
$total_admins = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM users WHERE role = 'user'"))['count'];

// USERS REGISTERED MONTHLY
$monthly_users = mysqli_query($con, "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM users 
    GROUP BY month 
    ORDER BY month ASC
");

// CONTENT BY CATEGORY
$category_count = mysqli_query($con, "
    SELECT category, COUNT(*) as total 
    FROM content 
    GROUP BY category
");

// Chart data preparation
$monthly_labels = [];
$monthly_counts = [];
while ($row = mysqli_fetch_assoc($monthly_users)) {
    $monthly_labels[] = $row['month'];
    $monthly_counts[] = $row['count'];
}

$category_labels = [];
$category_totals = [];
while ($row = mysqli_fetch_assoc($category_count)) {
    $category_labels[] = $row['category'];
    $category_totals[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reports Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="reports.css" />
  <style>
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 0;
      color: #333;
    }
    .summary-cards {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
    }
    .card {
      background: #ffffff;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      font-size: 18px;
      font-weight: 600;
      flex: 1;
      text-align: center;
    }
    .chart-container {
      background: #fff;
      padding: 20px;
      margin-bottom: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      height: 350px;
    }
    h1, h3 {
      margin-bottom: 20px;
      font-weight: 700;
      color: #222;
    }
    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 14px;
    }
    .report-table th, .report-table td {
      border: 1px solid #ccc;
      padding: 12px 15px;
      text-align: left;
    }
    .report-table th {
      background-color: #f5f5f5;
    }
    .scrollable-content {
      padding: 20px 40px;
    }
    .layout-container {
      display: flex;
      min-height: 100vh;
    }
    .main-container {
      flex-grow: 1;
      background: #f9f9f9;
      overflow-y: auto;
    }
  </style>
</head>
<body>

<?php include('header.php'); ?>

<div class="layout-container">
  <?php include('sidebar.php'); ?>

  <div class="main-container">
    <div class="scrollable-content">

      <h1>📊 Reports Dashboard</h1>

      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="card">Total Admins: <strong><?php echo $total_admins; ?></strong></div>
        <div class="card">Total Users: <strong><?php echo $total_users; ?></strong></div>
      </div>

      <!-- Chart: Users Registered Monthly -->
      <div class="chart-container user-registered-monthly">
        <h3>Users Registered Monthly</h3>
        <canvas id="userChart"></canvas>
      </div>

      <!-- Pie Chart: Content by Category -->
      <div class="chart-container content-by-category">
        <h3>Content by Category</h3>
        <canvas id="categoryChart"></canvas>
      </div>

    </div> <!-- scrollable-content -->
  </div> <!-- main-container -->
</div> <!-- layout-container -->

<script>
  const userChartCtx = document.getElementById('userChart').getContext('2d');
  const categoryChartCtx = document.getElementById('categoryChart').getContext('2d');

  const userChart = new Chart(userChartCtx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($monthly_labels); ?>,
      datasets: [{
        label: 'Users Registered',
        data: <?php echo json_encode($monthly_counts); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.7)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  const categoryChart = new Chart(categoryChartCtx, {
    type: 'pie',
    data: {
      labels: <?php echo json_encode($category_labels); ?>,
      datasets: [{
        label: 'Content Count',
        data: <?php echo json_encode($category_totals); ?>,
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8BC34A', '#FF9800', '#9C27B0', '#00BCD4']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
</script>

</body>
</html>
