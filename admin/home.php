<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Billing</title>

    <link rel="stylesheet" href="../src/home.css" />
</head>


<body>
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    include '../components/navbar.php';
    ?>

    <!-- notification message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="error success">
            <h3>
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </h3>
        </div>
    <?php endif ?>


<div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Admin Panel</h1>
            <p>Manage your electrical billing system from one central dashboard</p>
        </div>

        <div class="dashboard-grid">
            <!-- Add Bill -->
            <div class="dashboard-card">
                <div class="card-icon">📄</div>
                <h3>Add Bill</h3>
                <p>Create and manage customer electricity bills</p>
                <a href="./bill.php" class="card-link">Access →</a>
            </div>

            <!-- Add Branch -->
            <div class="dashboard-card">
                <div class="card-icon">🏢</div>
                <h3>Add Branch</h3>
                <p>Register new branch locations and offices</p>
                <a href="branch.php" class="card-link">Access →</a>
            </div>

            <!-- Add Customer Details -->
            <div class="dashboard-card">
                <div class="card-icon">👤</div>
                <h3>Add Customer</h3>
                <p>Register new customer information</p>
                <a href="customer.php" class="card-link">Access →</a>
            </div>

            <!-- Add Demand Type -->
            <div class="dashboard-card">
                <div class="card-icon">⚡</div>
                <h3>Demand Type</h3>
                <p>Configure electricity demand categories</p>
                <a href="demand.php" class="card-link">Access →</a>
            </div>

            <!-- Add Demand Rate -->
            <div class="dashboard-card">
                <div class="card-icon">💰</div>
                <h3>Demand Rate</h3>
                <p>Set pricing rates per demand type</p>
                <a href="demandRate.php" class="card-link">Access →</a>
            </div>

            <!-- Add Payment -->
            <div class="dashboard-card">
                <div class="card-icon">💳</div>
                <h3>Add Payment</h3>
                <p>Record customer payment transactions</p>
                <a href="payment.php" class="card-link">Access →</a>
            </div>

            <!-- Add Payment Option -->
            <div class="dashboard-card">
                <div class="card-icon">🏦</div>
                <h3>Payment Option</h3>
                <p>Manage available payment methods</p>
                <a href="paymentOption.php" class="card-link">Access →</a>
            </div>

            <!-- Customer List -->
            <div class="dashboard-card">
                <div class="card-icon">📋</div>
                <h3>Customer List</h3>
                <p>View all customers with branch, demand type, and bill IDs</p>
                <a href="customerList.php" class="card-link">View All →</a>
            </div>

            <!-- Search -->
            <div class="dashboard-card">
                <div class="card-icon">🔍</div>
                <h3>Search</h3>
                <p>Find customers, bills, and transactions</p>
                <a href="search.php" class="card-link">Access →</a>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>


</body>

</html>