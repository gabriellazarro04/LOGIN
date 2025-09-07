<?php 
include 'config.php';

// Initialize dashboard data
$dashboardData = [
    'total_fuel_cost' => 0,
    'total_toll_fees' => 0,
    'total_trip_expenses' => 0,
    'potential_savings' => 0,
    'recent_activities' => [],
    'recommendations' => []
];

// Check if fuel_usage table exists and get fuel costs
$fuel_query = "SHOW TABLES LIKE 'fuel_usage'";
$fuel_table_exists = mysqli_query($Connections, $fuel_query) && mysqli_num_rows(mysqli_query($Connections, $fuel_query)) > 0;

if ($fuel_table_exists) {
    $fuel_result = mysqli_query($Connections, "SELECT SUM(cost) as total FROM fuel_usage");
    if ($fuel_result && $fuel_row = mysqli_fetch_assoc($fuel_result)) {
        $dashboardData['total_fuel_cost'] = floatval($fuel_row['total'] ?? 0);
    }
}

// Check if toll_fees table exists and get toll fees
$toll_query = "SHOW TABLES LIKE 'toll_fees'";
$toll_table_exists = mysqli_query($Connections, $toll_query) && mysqli_num_rows(mysqli_query($Connections, $toll_query)) > 0;

if ($toll_table_exists) {
    $toll_result = mysqli_query($Connections, "SELECT SUM(fee_amount) as total FROM toll_fees WHERE status = 'Active'");
    if ($toll_result && $toll_row = mysqli_fetch_assoc($toll_result)) {
        $dashboardData['total_toll_fees'] = floatval($toll_row['total'] ?? 0);
    }
}

// Check if trip_expenses table exists and get expenses
$expense_query = "SHOW TABLES LIKE 'trip_expenses'";
$expense_table_exists = mysqli_query($Connections, $expense_query) && mysqli_num_rows(mysqli_query($Connections, $expense_query)) > 0;

if ($expense_table_exists) {
    $expense_result = mysqli_query($Connections, "SELECT SUM(amount) as total FROM trip_expenses WHERE status = 'Approved'");
    if ($expense_result && $expense_row = mysqli_fetch_assoc($expense_result)) {
        $dashboardData['total_trip_expenses'] = floatval($expense_row['total'] ?? 0);
    }
}

// Check if recommendations table exists
$recommendations_query = "SHOW TABLES LIKE 'recommendations'";
$recommendations_table_exists = mysqli_query($Connections, $recommendations_query) && mysqli_num_rows(mysqli_query($Connections, $recommendations_query)) > 0;

if ($recommendations_table_exists) {
    $savings_result = mysqli_query($Connections, "SELECT SUM(estimated_savings) as total FROM recommendations WHERE status = 'Pending'");
    if ($savings_result && $savings_row = mysqli_fetch_assoc($savings_result)) {
        $dashboardData['potential_savings'] = floatval($savings_row['total'] ?? 0);
    }
    
    // Get optimization recommendations
    $recommendations_result = mysqli_query($Connections, "SELECT * FROM recommendations ORDER BY created_at DESC LIMIT 5");
    if ($recommendations_result) {
        while ($recommendation = mysqli_fetch_assoc($recommendations_result)) {
            $dashboardData['recommendations'][] = $recommendation;
        }
    }
}

// Get recent activities from available tables
$recent_activities = [];

if ($fuel_table_exists) {
    $fuel_activities = mysqli_query($Connections, 
        "SELECT 'Fuel' as type, CONCAT('Added fuel record: ', liters, 'L') as description, date as activity_date, cost as amount
         FROM fuel_usage ORDER BY created_at DESC LIMIT 3");
    if ($fuel_activities) {
        while ($activity = mysqli_fetch_assoc($fuel_activities)) {
            $recent_activities[] = $activity;
        }
    }
}

if ($toll_table_exists) {
    $toll_activities = mysqli_query($Connections, 
        "SELECT 'Toll' as type, CONCAT('Toll fee: ', toll_name) as description, effective_date as activity_date, fee_amount as amount
         FROM toll_fees ORDER BY created_at DESC LIMIT 3");
    if ($toll_activities) {
        while ($activity = mysqli_fetch_assoc($toll_activities)) {
            $recent_activities[] = $activity;
        }
    }
}

if ($expense_table_exists) {
    $expense_activities = mysqli_query($Connections, 
        "SELECT 'Expense' as type, CONCAT('Trip expense: ', expense_type) as description, expense_date as activity_date, amount
         FROM trip_expenses ORDER BY created_at DESC LIMIT 3");
    if ($expense_activities) {
        while ($activity = mysqli_fetch_assoc($expense_activities)) {
            $recent_activities[] = $activity;
        }
    }
}

// Check if cost_optimization_reports table exists
$reports_query = "SHOW TABLES LIKE 'cost_optimization_reports'";
$reports_table_exists = mysqli_query($Connections, $reports_query) && mysqli_num_rows(mysqli_query($Connections, $reports_query)) > 0;

if ($reports_table_exists) {
    $report_activities = mysqli_query($Connections, 
        "SELECT 'Report' as type, CONCAT('Cost report: ', category) as description, created_at as activity_date, NULL as amount
         FROM cost_optimization_reports ORDER BY created_at DESC LIMIT 3");
    if ($report_activities) {
        while ($activity = mysqli_fetch_assoc($report_activities)) {
            $recent_activities[] = $activity;
        }
    }
}

// Sort activities by date and limit to 8
usort($recent_activities, function($a, $b) {
    return strtotime($b['activity_date']) - strtotime($a['activity_date']);
});
$dashboardData['recent_activities'] = array_slice($recent_activities, 0, 8);

// Handle API request for dashboard data
if (isset($_GET['action']) && $_GET['action'] == 'get_dashboard_data') {
    header('Content-Type: application/json');
    echo json_encode($dashboardData);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trip Cost Analysis Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #3498db;
      --accent: #e74c3c;
      --light: #ecf0f1;
      --dark: #1d1d1d;
      --success: #2ecc71;
      --warning: #f39c12;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #f5f7fa;
      color: #333;
      display: flex;
      margin: 0;
    }

    /* --- Sidebar --- */
    .sidebar {
      width: 220px;
      background: var(--dark);
      color: white;
      height: 100vh;
      padding: 25px 0;
      position: fixed;
      overflow-y: auto;
    }

    .sidebar .logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .sidebar .logo img {
      width: 160px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      padding: 8px 20px;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .sidebar ul li:hover {
      background: #333;
      transform: translateX(5px);
    }

    .sidebar ul li a {
      color: white;
      text-decoration: none;
      display: block;
      transition: color 0.3s ease;
    }

    .sidebar ul li:hover a {
      color: #a875ff;
    }

    .sidebar ul li.active {
      background: #7a3ff2;
    }

    .bottom-links {
      display: flex;
      flex-direction: column;
      margin-top: 100px;
    }

    .bottom-links a {
      color: white;
      text-decoration: none;
      margin: 5px 0;
      padding: 10px 20px;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .bottom-links a:hover {
      color: #a875ff;
      background: #333;
      transform: translateX(5px);
    }

    /* --- Main Content --- */
    .main-content {
      margin-left: 220px;
      padding: 20px;
      width: calc(100% - 220px);
      min-height: 100vh;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .page-title {
      font-size: 24px;
      font-weight: 600;
      color: #2c3e50;
    }

    .refresh-btn {
      background-color: #9a66ff;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .refresh-btn:hover {
      background-color: #8253e0ff;
    }

    /* Dashboard Stats */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      }
      
    .stat-title {
      font-size: 16px;
      font-weight: 600;
      color: #7f8c8d;
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .stat-change {
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .positive {
      color: #2ecc71;
    }

    .negative {
      color: #e74c3c;
    }

    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      padding: 20px;
      transition: transform 0.3s ease;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .card-title {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
    }

    .view-all {
      color: #9a66ff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }

    .view-all:hover {
      text-decoration: underline;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #2c3e50;
    }

    tr:hover {
      background-color: #f8f9fa;
    }

    .status {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .completed {
      background-color: #e7f7ef;
      color: #27ae60;
    }

    .ongoing {
      background-color: #e8f4fd;
      color: #3498db;
    }

    .expired {
      background-color: #fdecea;
      color: #e74c3c;
    }

    .expiring-soon {
      background-color: #fef5e7;
      color: #f39c12;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 30px;
    }

    .action-btn {
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      color: #333;
    }

    .action-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      border-color: #9a66ff;
    }

    .action-icon {
      font-size: 24px;
      margin-bottom: 10px;
      color: #9a66ff;
    }

    .action-title {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .action-desc {
      font-size: 14px;
      color: #7f8c8d;
    }

    /* Chart container */
    .chart-container {
      height: 300px;
      margin-top: 20px;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }

      .main-content {
        margin-left: 0;
        width: 100%;
      }

      .dashboard-cards {
        grid-template-columns: 1fr;
      }
    }

    /* Loading spinner */
    .spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-left: 4px solid #3498db;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="viahale1.png" alt="Viahale Logo">
    </div>
    <ul>
      <li class="active"><a href="tcao-dashboard.php"><i class="fas fa-align-justify"></i> Dashboard </a></li>
      <li><a href="FU.php"><i class="fas fa-gas-pump me-2"></i> Fuel Usage</a></li>
      <li><a href="TF.php"><i class="fas fa-arrows-alt me-2"></i> Toll Fees</a></li>
      <li><a href="TE.php"><i class="fas fa-bolt me-2"></i> Trip Expenses</a></li>
      <li><a href="CO.php"><i class="fas fa-money-bill me-2"></i> Cost Optimization</a></li>
    </ul>
    <div class="bottom-links">
      <a href="#"><i class="fas fa-bell me-2"></i> Notifications</a>
      <a href="#"><i class="fas fa-user me-2"></i> Account</a>
      <a href="../login.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Trip Cost Analysis Dashboard</h1>
      <button class="refresh-btn" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>

    <!-- Stats Overview -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">TOTAL FUEL COST</div>
          <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">
            <i class="fas fa-gas-pump"></i>
          </div>
        </div>
        <div class="stat-value" id="total-fuel-cost">
          ₱<?php echo number_format($dashboardData['total_fuel_cost'], 2); ?>
        </div>
        <div class="stat-change negative">
          <i class="fas fa-arrow-up"></i> <span id="fuel-change">From fuel usage</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">TOLL FEES</div>
          <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">
            <i class="fas fa-road"></i>
          </div>
        </div>
        <div class="stat-value" id="total-toll-fees">
          ₱<?php echo number_format($dashboardData['total_toll_fees'], 2); ?>
        </div>
        <div class="stat-change positive">
          <i class="fas fa-arrow-down"></i> <span id="toll-change">From toll fees</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">TRIP EXPENSES</div>
          <div class="stat-icon" style="background-color: rgba(155, 102, 255, 0.1); color: #9a66ff;">
            <i class="fas fa-receipt"></i>
          </div>
        </div>
        <div class="stat-value" id="total-expenses">
          ₱<?php echo number_format($dashboardData['total_trip_expenses'], 2); ?>
        </div>
        <div class="stat-change negative">
          <i class="fas fa-arrow-up"></i> <span id="expense-change">From trip expenses</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-title">POTENTIAL SAVINGS</div>
          <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.1); color: #f39c12;">
            <i class="fas fa-piggy-bank"></i>
          </div>
        </div>
        <div class="stat-value" id="potential-savings">
          ₱<?php echo number_format($dashboardData['potential_savings'], 2); ?>
        </div>
        <div class="stat-change positive">
          <i class="fas fa-bullseye"></i> <span id="savings-source">From optimizations</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <a href="FU.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-gas-pump"></i>
        </div>
        <div class="action-title">Log Fuel</div>
        <div class="action-desc">Record fuel usage</div>
      </a>
      
      <a href="TF.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-road"></i>
        </div>
        <div class="action-title">Add Toll</div>
        <div class="action-desc">Record toll fee</div>
      </a>
      
      <a href="TE.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-receipt"></i>
        </div>
        <div class="action-title">Add Expense</div>
        <div class="action-desc">Record trip expense</div>
      </a>
      
      <a href="CO.php" class="action-btn">
        <div class="action-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="action-title">Generate Report</div>
        <div class="action-desc">Cost analysis report</div>
      </a>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
      <!-- Recent Activities Card -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Recent Activities</div>
          <a href="#" class="view-all">View All</a>
        </div>
        <div class="activities-list">
          <table>
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Date</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody id="recent-activities">
              <?php if (!empty($dashboardData['recent_activities'])): ?>
                <?php foreach ($dashboardData['recent_activities'] as $activity): ?>
                  <tr>
                    <td><span class="status <?php echo getStatusClass($activity['type']); ?>"><?php echo $activity['type']; ?></span></td>
                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                    <td><?php echo formatDate($activity['activity_date']); ?></td>
                    <td><?php echo $activity['amount'] ? '₱' . number_format($activity['amount'], 2) : '-'; ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center;">No recent activities found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Cost Optimization Recommendations -->
      

    <!-- Cost Distribution Chart -->
    

  <script>
    // Function to fetch dashboard data from the server
    async function fetchDashboardData() {
      try {
        const response = await fetch('tcao-dashboard.php?action=get_dashboard_data');
        const data = await response.json();
        return data;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        return null;
      }
    }

    // Function to update the dashboard with fetched data
    function updateDashboard(data) {
      if (!data) {
        // Display error message if data fetching failed
        showNotification('Error fetching dashboard data. Please try again.', 'error');
        return;
      }

      // Update stats cards
      document.getElementById('total-fuel-cost').textContent = '₱' + (data.total_fuel_cost || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
      document.getElementById('total-toll-fees').textContent = '₱' + (data.total_toll_fees || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
      document.getElementById('total-expenses').textContent = '₱' + (data.total_trip_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
      document.getElementById('potential-savings').textContent = '₱' + (data.potential_savings || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});

      // Update recent activities
      const activitiesContainer = document.getElementById('recent-activities');
      if (data.recent_activities && data.recent_activities.length > 0) {
        let activitiesHTML = '';
        data.recent_activities.forEach(activity => {
          const statusClass = getStatusClass(activity.type);
          activitiesHTML += `
            <tr>
              <td><span class="status ${statusClass}">${activity.type}</span></td>
              <td>${activity.description}</td>
              <td>${formatDate(activity.activity_date)}</td>
              <td>${activity.amount ? '₱' + parseFloat(activity.amount).toFixed(2) : '-'}</td>
            </tr>
          `;
        });
        activitiesContainer.innerHTML = activitiesHTML;
      } else {
        activitiesContainer.innerHTML = `
          <tr>
            <td colspan="4" style="text-align: center;">No recent activities found</td>
          </tr>
        `;
      }

      // Update optimization recommendations
      const recommendationsContainer = document.getElementById('optimization-recommendations');
      if (data.recommendations && data.recommendations.length > 0) {
        let recommendationsHTML = '';
        data.recommendations.forEach(rec => {
          const statusClass = getRecommendationStatusClass(rec.status);
          recommendationsHTML += `
            <tr>
              <td>${rec.category || 'N/A'}</td>
              <td>${rec.title || 'N/A'}</td>
              <td>₱${parseFloat(rec.estimated_savings || 0).toFixed(2)}</td>
              <td><span class="status ${statusClass}">${rec.status || 'Pending'}</span></td>
            </tr>
          `;
        });
        recommendationsContainer.innerHTML = recommendationsHTML;
      } else {
        recommendationsContainer.innerHTML = `
          <tr>
            <td colspan="4" style="text-align: center;">No recommendations found</td>
          </tr>
        `;
      }

      // Update cost distribution chart
      updateCostChart(data);
    }

    // Function to determine status class based on activity type
    function getStatusClass(type) {
      switch(type.toLowerCase()) {
        case 'fuel': return 'ongoing';
        case 'expense': return 'completed';
        case 'toll': return 'completed';
        case 'report': return 'expiring-soon';
        default: return 'ongoing';
      }
    }

    // Function to determine status class for recommendations
    function getRecommendationStatusClass(status) {
      switch(status.toLowerCase()) {
        case 'implemented': return 'completed';
        case 'approved': return 'ongoing';
        case 'pending': return 'expiring-soon';
        case 'rejected': return 'expired';
        default: return 'ongoing';
      }
    }

    // Function to format date
    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // Function to update cost distribution chart
    function updateCostChart(data) {
      const ctx = document.getElementById('costChart').getContext('2d');
      
      // Destroy existing chart if it exists
      if (window.costChartInstance) {
        window.costChartInstance.destroy();
      }
      
      // Create new chart
      window.costChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Fuel Costs', 'Toll Fees', 'Trip Expenses'],
          datasets: [{
            data: [
              data.total_fuel_cost || 0,
              data.total_toll_fees || 0,
              data.total_trip_expenses || 0
            ],
            backgroundColor: [
              'rgba(52, 152, 219, 0.7)',
              'rgba(46, 204, 113, 0.7)',
              'rgba(155, 102, 255, 0.7)'
            ],
            borderColor: [
              'rgba(52, 152, 219, 1)',
              'rgba(46, 204, 113, 1)',
              'rgba(155, 102, 255, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.label || '';
                  if (label) {
                    label += ': ';
                  }
                  label += '₱' + context.raw.toLocaleString('en-PH', {minimumFractionDigits: 2});
                  return label;
                }
              }
            }
          }
        }
      });
    }

    // Function to refresh dashboard data
    async function refreshData() {
      const refreshBtn = document.querySelector('.refresh-btn');
      refreshBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Refreshing...';
      refreshBtn.disabled = true;
      
      try {
        const data = await fetchDashboardData();
        updateDashboard(data);
        
        // Show notification that data was refreshed
        showNotification('Dashboard data refreshed successfully!', 'success');
      } catch (error) {
        console.error('Error refreshing data:', error);
        showNotification('Error refreshing data. Please try again.', 'error');
      } finally {
        // Reset refresh button
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        refreshBtn.disabled = false;
      }
    }

    // Function to show notification
    function showNotification(message, type) {
      // Create notification element
      const notification = document.createElement('div');
      notification.style.position = 'fixed';
      notification.style.top = '20px';
      notification.style.right = '20px';
      notification.style.padding = '15px 20px';
      notification.style.borderRadius = '5px';
      notification.style.color = 'white';
      notification.style.zIndex = '1000';
      notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15);
      notification.style.maxWidth = '300px';
      notification.style.opacity = '0';
      notification.style.transform = 'translateX(100%)';
      notification.style.transition = 'all 0.3s ease';
      
      if (type === 'success') {
        notification.style.backgroundColor = '#2ecc71';
      } else {
        notification.style.backgroundColor = '#e74c3c';
      }
      
      notification.textContent = message;
      document.body.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
      }, 10);
      
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }

    // Initialize dashboard when page loads
    document.addEventListener('DOMContentLoaded', async function() {
      // Load data immediately
      const data = await fetchDashboardData();
      updateDashboard(data);
      
      // Set up auto-refresh every 60 seconds
      setInterval(async () => {
        const data = await fetchDashboardData();
        updateDashboard(data);
      }, 60000);
    });
  </script>
</body>
</html>

<?php
// Helper functions for PHP rendering
function getStatusClass($type) {
  switch(strtolower($type)) {
    case 'fuel': return 'ongoing';
    case 'expense': return 'completed';
    case 'toll': return 'completed';
    case 'report': return 'expiring-soon';
    default: return 'ongoing';
  }
}

function getRecommendationStatusClass($status) {
  switch(strtolower($status)) {
    case 'implemented': return 'completed';
    case 'approved': return 'ongoing';
    case 'pending': return 'expiring-soon';
    case 'rejected': return 'expired';
    default: return 'ongoing';
  }
}

function formatDate($dateString) {
  $date = new DateTime($dateString);
  return $date->format('M j, Y');
}
?>