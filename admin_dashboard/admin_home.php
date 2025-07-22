<style>
    .card {
            background: rgba(255,255,255,0.13) !important;
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.13);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            backdrop-filter: blur(4px);
        }
        
        .card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 8px 32px rgba(166, 74, 255, 0.13);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .stat-card .card-body {
            text-align: center;
        }
        
        .stat-card .card-title {
            font-size: 1rem;
            color: #d1c4e9;
            letter-spacing: 0.5px;
        }
        
        .stat-card .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 2px 8px rgba(106,0,255,0.08);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background: rgba(44, 62, 80, 0.85);
            color: #fff;
        }
        
        .badge-active {
            background: linear-gradient(90deg, #2ecc71, #10b981);
            color: #fff;
        }
        
        .badge-inactive {
            background: linear-gradient(90deg, #e74c3c, #ff4d4d);
            color: #fff;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        /* Make sure all text is visible on transparent backgrounds */
        .card, .card * {
            color: #fff !important;
        }
        .table, .table th, .table td {
            color: #fff;
        }
        
</style>
<!-- Dashboard title -->
<h2 class="mb-4">Dashboard Overview</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row">
    <div class="col-md-4">
        <div class="card stat-card bg-white">
            <div class="card-body">
                <div class="card-icon text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="card-title">Total Users</h5>
                <p class="card-value"><?php echo $users_count; ?></p>
                <p class="text-muted small"><i class="fas fa-arrow-up text-success"></i> 12% from last month</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-white">
            <div class="card-body">
                <div class="card-icon text-success">
                    <i class="fas fa-utensils"></i>
                </div>
                <h5 class="card-title">Meals Logged</h5>
                <p class="card-value"><?php echo $meals_count; ?></p>
                <p class="text-muted small"><i class="fas fa-arrow-up text-success"></i> 8% from last week</p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-white">
            <div class="card-body">
                <div class="card-icon text-warning">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h5 class="card-title">Workouts Logged</h5>
                <p class="card-value"><?php echo $workouts_count; ?></p>
                <p class="text-muted small"><i class="fas fa-arrow-down text-danger"></i> 3% from last week</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card bg-white">
            <div class="card-header">
                <h5 class="card-title">User Activity (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-white">
            <div class="card-header">
                <h5 class="card-title">User Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="userDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart scripts -->
<script>
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Jan 1','Jan 5','Jan 10','Jan 15','Jan 20','Jan 25','Jan 30'],
            datasets: [
                {
                    label: 'Workouts',
                    data: [12,19,15,20,22,18,25],
                    borderColor: 'rgba(243,156,18,1)',
                    backgroundColor: 'rgba(243,156,18,0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Meals',
                    data: [25,30,28,35,32,40,38],
                    borderColor: 'rgba(46,204,113,1)',
                    backgroundColor: 'rgba(46,204,113,0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // User Distribution Chart
    const userDistCtx = document.getElementById('userDistributionChart').getContext('2d');
    new Chart(userDistCtx, {
        type: 'doughnut',
        data: {
            labels: ['Basic','Premium','Pro','Admin'],
            datasets: [{
                data: [65,25,8,2],
                backgroundColor: [
                    'rgba(52,152,219,0.8)',
                    'rgba(155,89,182,0.8)',
                    'rgba(46,204,113,0.8)',
                    'rgba(231,76,60,0.8)'
                ],
                borderColor: [
                    'rgba(52,152,219,1)',
                    'rgba(155,89,182,1)',
                    'rgba(46,204,113,1)',
                    'rgba(231,76,60,1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });
</script>