<?php
include('functions.php');

if (!isLoggedIn()) {
  $_SESSION['msg'] = "You must log in first";
  header('location: login.php');
  exit();
}

include './php/dbconnect.php';
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Welcome to Homepage!</title>

  <link rel="stylesheet" type="text/css" href="./src/index.css">

  <style>
    table,
    td,
    th {
      border: 1px solid;
      margin: 0.5rem 0 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td,
    th {
      padding: 0.5rem;
      text-align: center;
    }

    .overdue-notice {
      display: inline-block;
      margin-top: 0.35rem;
      padding: 0.35rem 0.65rem;
      border-radius: 999px;
      background: #fee2e2;
      color: #b91c1c;
      font-size: 0.82rem;
      font-weight: 600;
    }
          <?php
          echo $_SESSION['success'];
          unset($_SESSION['success']);
          ?>
        </h3>
      </div>
    <?php endif ?>

    <div class="container">

      <!-- LOGGED IN USER -->
      <?php if (isset($_SESSION['user'])): ?>
        <h1>Hello,
          <?php echo $_SESSION['user']['username']; ?>
        </h1>
      <?php endif ?>

      <!-- SEARCH FORM -->
      <h3>Search Customer:</h3>

      <form method="post" action="">

        <div class="box">
          <input type="text" name="name" id="name">
          <span>Name</span>
        </div>

        <div class="box">
          <input type="text" name="mobile" id="mobile">
          <span>Mobile No</span>
        </div>

        <div class="box">
          <input class="submit" type="submit" name="submit" value="Search" />
        </div>

      </form>

      <?php

      // =========================
      // SEARCH CUSTOMER
      // =========================

      if (isset($_POST['submit'])) {

        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);

        $sql = "SELECT * FROM customer 
                WHERE Fullname LIKE '%$name%' 
                AND MobileNo LIKE '%$mobile%'";

        $result = mysqli_query($conn, $sql);

        // =========================
        // CUSTOMER FOUND
        // =========================

        if (mysqli_num_rows($result) > 0) {

          echo "<hr>";
          echo "<h1 style='margin:1rem 0;'>Search Result</h1>";

          while ($row = mysqli_fetch_assoc($result)) {

            $fullName = $row['Fullname'];
            $mobileNo = $row['MobileNo'];
            $address = $row['AddressName'];
            $demandType = $row['demand_type_id'];
            $userId = $row['CUSID'];

            // =========================
            // DEMAND TYPE
            // =========================

            $demandQuery = "SELECT descrip 
                            FROM demandtype 
                            WHERE demand_type_id = '$demandType'";

            $resultDemand = mysqli_query($conn, $demandQuery);

            $demand = mysqli_fetch_assoc($resultDemand);

            $demandDesc = $demand['descrip'];

            // =========================
            // CUSTOMER DETAILS TABLE
            // =========================

            echo "
            <table>

              <tr>
                <td><strong>Full Name</strong></td>
                <td>$fullName</td>
              </tr>

              <tr>
                <td><strong>Mobile No</strong></td>
                <td>$mobileNo</td>
              </tr>

              <tr>
                <td><strong>Address</strong></td>
                <td>$address</td>
              </tr>

              <tr>
                <td><strong>Demand Type</strong></td>
                <td>$demandDesc</td>
              </tr>

            </table>
            ";

            // =========================
            // BILL DETAILS QUERY
            // =========================

            $querybill = "
              SELECT 
                BID,
                BDate,
                BYear,
                Current_Reading,
                Prev_Reading,
                Bamount,
                payment_status
              FROM bill
              WHERE CUSID='$userId'
            ";

            $billResult = mysqli_query($conn, $querybill);

            // =========================
            // BILL FOUND
            // =========================

            if (mysqli_num_rows($billResult) > 0) {

              echo "<h2 style='margin:1rem 0;'>Bill Details</h2>";

              echo "
              <table class='center'>

                <tr>
                  <th>Bill Number</th>
                  <th>Bill Amount</th>
                  <th>Bill Date</th>
                  <th>Current Reading</th>
                  <th>Previous Reading</th>
                  <th>Payment Status</th>
                </tr>
              ";

              while ($bill = mysqli_fetch_assoc($billResult)) {

                $BID = $bill['BID'];
                $Bamount = $bill['Bamount'];
                $BDate = $bill['BDate'];
                $CReading = $bill['Current_Reading'];
                $PReading = $bill['Prev_Reading'];
                $status = $bill['payment_status'];
                $overdueNotice = getBillDueNotice($BDate, $status);

                echo "
                <tr>

                  <td>$BID</td>

                  <td>$Bamount</td>

                  <td>$BDate</td>

                  <td>$CReading</td>

                  <td>$PReading</td>

                  <td>
                ";

                if ($status) {

                  echo '<a href="./view.php">Paid</a>';

                } else {

                  echo '<a href="./payment.php?billid=' . urlencode($BID) . '&amount=' . urlencode($Bamount) . '">Pay</a>';

                }

                if ($overdueNotice) {
                    echo '<div class="overdue-notice">' . $overdueNotice . '</div>';
                }

                echo "
                  </td>

                </tr>
                ";
              }

              echo "</table>";

            } else {

              echo "<p>No Bill Details found for this customer.</p>";

            }
          }

        } else {

          // =========================
          // NO CUSTOMER FOUND
          // =========================

          echo "<p>No customer found with the provided Name and Number.</p>";

        }
      }

      ?>

    </div>
  </div>

</body>

</html>