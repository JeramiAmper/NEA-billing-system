<?php
include '../php/sessionVerify.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search Customer | NEA Billing System</title>

    <link rel="stylesheet" href="../src/search.css" />
</head>

<body>
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    include '../components/navbar.php';
    ?>

    <!-- notification message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <span class="alert-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
    <?php endif ?>

    <main class="main-content">
        <div class="page-container">
            <!-- Back Navigation -->
            <div class="back-nav">
                <a href="./home.php" class="back-button">
                    <svg class="back-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <!-- Search Card -->
            <div class="search-card">
                <div class="search-header">
                    <div class="search-icon-wrapper">
                        <div class="search-icon">🔍</div>
                    </div>
                    <h1>Search Customer</h1>
                    <p class="search-subtitle">Enter Customer ID to view billing and payment history</p>
                </div>

                <form class="search-form" method="POST" action="">
                    <div class="input-group">
                        <input type="text" name="cusid" id="cusid" required placeholder=" " autocomplete="off" />
                        <label for="cusid">Customer ID</label>
                        <span class="input-hint">Enter the unique customer identification number</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="submit" class="btn-search">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Search Customer
                        </button>
                    </div>
                </form>

                <?php
                include("../php/dbconnect.php");
                if (isset($_POST['submit'])) {
                    $cusid = mysqli_real_escape_string($conn, $_POST['cusid']);
                    $querycus = "SELECT FullName, MobileNo, AddressName, demand_type_id, BranchId FROM customer WHERE CUSID = '$cusid'";
                    $result = mysqli_query($conn, $querycus);

                    if (mysqli_num_rows($result) > 0) {
                        // ==================== CUSTOMER DETAILS SECTION ====================
                        echo '<div class="results-section">';
                        echo '<div class="section-header">';
                        echo '<div class="section-icon">👤</div>';
                        echo '<h2>Customer Details</h2>';
                        echo '</div>';

                        while ($row = mysqli_fetch_assoc($result)) {
                            $fullName = htmlspecialchars($row['FullName']);
                            $mobileNo = htmlspecialchars($row['MobileNo']);
                            $address = htmlspecialchars($row['AddressName']);
                            $DemandType = htmlspecialchars($row['demand_type_id']);
                            $Branch = htmlspecialchars($row['BranchId']);

                            $querybranch = "SELECT branch_name FROM branch WHERE branch_id = '$Branch'";
                            $resultquery = mysqli_query($conn, $querybranch);
                            $branch = mysqli_fetch_assoc($resultquery);
                            $branchname = htmlspecialchars($branch['branch_name']);

                            $demandquery = "SELECT descrip FROM demandtype WHERE demand_type_id = '$DemandType'";
                            $resultdemand = mysqli_query($conn, $demandquery);
                            $demand1 = mysqli_fetch_assoc($resultdemand);
                            $demanddescp = htmlspecialchars($demand1['descrip']);

                            echo '<div class="info-grid">';
                            echo '<div class="info-row"><span class="info-label">Full Name</span><span class="info-value">' . $fullName . '</span></div>';
                            echo '<div class="info-row"><span class="info-label">Mobile Number</span><span class="info-value">' . $mobileNo . '</span></div>';
                            echo '<div class="info-row"><span class="info-label">Address</span><span class="info-value">' . $address . '</span></div>';
                            echo '<div class="info-row"><span class="info-label">Demand Type</span><span class="info-value">' . $demanddescp . '</span></div>';
                            echo '<div class="info-row"><span class="info-label">Registered Branch</span><span class="info-value">' . $branchname . '</span></div>';
                            echo '</div>';
                        }
                        echo '</div>';

                        // ==================== BILL DETAILS SECTION ====================
                        $querybill = "SELECT BID, BDate, BYear, BMonth, Current_Reading, Prev_Reading, Bamount, payment_status FROM bill WHERE CUSID='$cusid' ORDER BY BYear DESC, FIELD(BMonth, 'Baisakh', 'Jestha', 'Aasadh', 'Shrawan', 'Bhadra', 'Asoj', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra') DESC";
                        $billResult = mysqli_query($conn, $querybill);

                        $unpaidCountResult = mysqli_query($conn, "SELECT COUNT(*) AS unpaid_count FROM bill WHERE CUSID='$cusid' AND payment_status = 0");
                        $unpaidCount = 0;
                        if ($unpaidCountResult) {
                            $countRow = mysqli_fetch_assoc($unpaidCountResult);
                            $unpaidCount = intval($countRow['unpaid_count']);
                        }
                        $paymentReminderNotice = getBillCountNotice($unpaidCount);

                        if (mysqli_num_rows($billResult) > 0) {
                            $myarray = array();
                            echo '<div class="results-section">';
                            echo '<div class="section-header">';
                            echo '<div class="section-icon">📄</div>';
                            echo '<h2>Bill Details</h2>';
                            echo '</div>';
                            if ($paymentReminderNotice) {
                                echo '<div style="margin-bottom:1rem;">' . $paymentReminderNotice . '</div>';
                            }
                            echo '<div class="table-wrapper">';
                            echo '<table class="data-table">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Bill ID</th>';
                            echo '<th>Bill Amount</th>';
                            echo '<th>Year</th>';
                            echo '<th>Month</th>';
                            echo '<th>Current Reading</th>';
                            echo '<th>Previous Reading</th>';
                            echo '<th>Status</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($row = mysqli_fetch_assoc($billResult)) {
                                $BID = $row['BID'];
                                array_push($myarray, $BID);
                                $Bamount = number_format($row['Bamount'], 2);
                                $Byear = $row['BYear'];
                                $Bmonth = $row['BMonth'];
                                $CReading = $row['Current_Reading'];
                                $PReading = $row['Prev_Reading'];
                                $paymentStatus = $row['payment_status'];
                                $statusBadge = $paymentStatus ? '<span class="badge paid">✓ Paid</span>' : '<span class="badge unpaid">⏳ Unpaid</span>';
                                $overdueNotice = getBillDueNotice($row['BDate'], $paymentStatus);

                                echo "<tr>
                                        <td>{$BID}</td>
                                        <td class=\"amount\">Rs. {$Bamount}</td>
                                        <td>{$Byear}</td>
                                        <td>{$Bmonth}</td>
                                        <td>{$CReading}</td>
                                        <td>{$PReading}</td>
                                        <td>{$statusBadge}";

                                if ($overdueNotice) {
                                    echo "<div class=\"overdue-notice\">{$overdueNotice}</div>";
                                }

                                echo "</td>
                                      </tr>";
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';

                            // ==================== PAYMENT DETAILS SECTION ====================
                            echo '<div class="results-section">';
                            echo '<div class="section-header">';
                            echo '<div class="section-icon">💳</div>';
                            echo '<h2>Payment History</h2>';
                            echo '</div>';
                            echo '<div class="table-wrapper">';
                            echo '<table class="data-table">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Payment ID</th>';
                            echo '<th>Bill ID</th>';
                            echo '<th>Payment Amount</th>';
                            echo '<th>Rebate</th>';
                            echo '<th>Fine</th>';
                            echo '<th>Payment Date</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            $hasPayments = false;
                            foreach ($myarray as $BIDE) {
                                $querypay = "SELECT PID, PDate, PAmount, Rebeat_Amt, Fine_Amt FROM payment WHERE BID='$BIDE'";
                                $payResult = mysqli_query($conn, $querypay);
                                if (mysqli_num_rows($payResult) > 0) {
                                    $hasPayments = true;
                                    while ($row = mysqli_fetch_assoc($payResult)) {
                                        $PID = $row['PID'];
                                        $PDate = $row['PDate'];
                                        $Pamount = number_format($row['PAmount'], 2);
                                        $Ramt = number_format($row['Rebeat_Amt'], 2);
                                        $Famt = number_format($row['Fine_Amt'], 2);
                                        echo "<tr>
                                                <td>{$PID}</td>
                                                <td>{$BIDE}</td>
                                                <td class=\"amount\">Rs. {$Pamount}</td>
                                                <td class=\"rebate\">Rs. {$Ramt}</td>
                                                <td class=\"fine\">Rs. {$Famt}</td>
                                                <td>{$PDate}</td>
                                              </tr>";
                                    }
                                }
                            }

                            if (!$hasPayments) {
                                echo '<tr><td colspan="6" class="no-data">No payment records found for this customer</td></tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<div class="no-results">';
                            echo '<div class="no-results-icon">📭</div>';
                            echo '<h3>No Bill Details Found</h3>';
                            echo '<p>No billing records exist for this customer.</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-results">';
                        echo '<div class="no-results-icon">🔍</div>';
                        echo '<h3>Customer Not Found</h3>';
                        echo '<p>No customer found with the provided Customer ID: <strong>' . htmlspecialchars($cusid) . '</strong></p>';
                        echo '<p>Please check the ID and try again.</p>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>

</html>