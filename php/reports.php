<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/reports.css">
    <title>ADMIN</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    <nav class="custom_nav">
        <div class="logo">
            <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="nav-links">
            <a href="admin.php" class="nav-item" data-text="Dashboard"><i class="fas fa-chart-line"></i>
                <p style="font-size:13px; padding-left:5px;">Home</p>
            </a>
            <a href="adminevents.php" class="nav-item" data-text="Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="adminusers.php" class="nav-item" data-text="Users"><i class="fas fa-users"></i>
                <p style="font-size:13px; padding-left:5px;">Users</p>
            </a>
            <a href="adminutil.php" class="nav-item" data-text="Resources"><i class="fas fa-cubes"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="reports.php" class="nav-item" data-text="Reports"><i class="fas fa-file-alt"></i>
                <p style="font-size:13px; padding-left:5px;">Reports</p>
            </a>
            <a href="adminlogs.php" class="nav-item" data-text="Analytics"><i class="fas fa-chart-bar"></i>
                <p style="font-size:13px; padding-left:5px;">Logs</p>
            </a>
            <a href="adminbudget.php" class="nav-item" data-text="Budget"><i class="fas fa-wallet"></i>
                <p style="font-size:13px; padding-left:5px;">Budget</p>
            </a>
            <a href="adminprofile.php" class="nav-item" data-text="Profile"><i class="fas fa-user-cog"></i>
                <p style="font-size:13px; padding-left:5px;">Profile</p>
            </a>
            <a href="adminalert.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">Alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>logout</p>
            </a>
        </div>
    </nav>
    <div class="container">
        <div class="logo" style="font-family:'poppins'; font-size:25px; color:#FF6D1F">
            <strong>Event Reports</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="reportholder">
            <div class="report">
                <h4>Budget Efficiency Report</h4> <br>
                <p>See how well budgeted funds were used per event.</p> <br>
                <div class="report-actions">
                    <a href="budget_efficiency.php" class="btn btn-primary btn-sm">View Report</a>
                    <a href="budget_pdf.php" class="btn btn-outline-secondary btn-sm">Export PDF</a>
                </div>
            </div>
            <div class="report">
                <h4>Task Completion Report</h4> <br>
                <p>Track the progress of tasks assigned across events.</p> <br>
                <div class="report-actions">
                    <a href="task_completion.php" class="btn btn-success btn-sm">View Report</a>
                    <a href="task_pdf.php" class="btn btn-outline-secondary btn-sm">Export PDF</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>