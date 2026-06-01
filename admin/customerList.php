<?php
include '../php/sessionVerify.php';
include_once '../functions.php';
include '../php/dbconnect.php';

$query = "SELECT c.CUSID, c.SCND, c.Fullname, b.branch_name, d.descrip AS demand_type, GROUP_CONCAT(bi.BID ORDER BY bi.BYear DESC, bi.BMonth DESC SEPARATOR ', ') AS bills"
    . " FROM customer c"
    . " LEFT JOIN branch b ON c.BranchId = b.branch_id"
    . " LEFT JOIN demandtype d ON c.demand_type_id = d.demand_type_id"
    . " LEFT JOIN bill bi ON c.CUSID = bi.CUSID"
    . " GROUP BY c.CUSID"
    . " ORDER BY c.CUSID ASC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer List | NEA Billing System</title>
    <link rel="stylesheet" href="../src/customerList.css" />
</head>

<body>
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    include '../components/navbar.php';
    ?>

    <main class="main-content">
        <div class="page-container">
            <div class="back-nav">
                <a href="./home.php" class="back-button">
                    <svg class="back-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <section class="report-card">
                <div class="report-header">
                    <div class="report-icon">📋</div>
                    <div>
                        <h1>Customer Master List</h1>
                        <p>All customers with their unique ID, branch, demand type and related bill IDs.</p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>CUSID</th>
                                <th>SC Number</th>
                                <th>Full Name</th>
                                <th>Branch</th>
                                <th>Demand Type</th>
                        <th>Bill IDs</th>
                        <th>Notice</th>
                    </tr>
                </thead>
                <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['CUSID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['SCND']); ?></td>
                                        <td><?php echo htmlspecialchars($row['Fullname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['branch_name'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($row['demand_type'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($row['bills'] ?: 'No bills'); ?></td>
                                        <td>
                                            <?php
                                            $billCountQuery = "SELECT COUNT(*) AS bill_count FROM bill WHERE CUSID = '" . intval($row['CUSID']) . "'";
                                            $billCountResult = $conn->query($billCountQuery);
                                            $billCount = 0;
                                            if ($billCountResult) {
                                                $countRow = $billCountResult->fetch_assoc();
                                                $billCount = intval($countRow['bill_count']);
                                            }

                                            $notice = getBillCountNotice($billCount);
                                            if (!$notice) {
                                                $overdueQuery = "SELECT BDate FROM bill WHERE CUSID = '" . intval($row['CUSID']) . "' AND payment_status = 0";
                                                $overdueResult = $conn->query($overdueQuery);
                                                if ($overdueResult && $overdueResult->num_rows > 0) {
                                                    while ($billRow = $overdueResult->fetch_assoc()) {
                                                        if (isBillOverdue($billRow['BDate'], 0)) {
                                                            $notice = getBillDueNotice($billRow['BDate'], 0);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }

                                            echo $notice ? $notice : '—';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">No customers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
</body>

</html>
