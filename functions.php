<?php
session_start();

// API health-check configuration
const API_HEALTH_URL = 'http://localhost:3000/health';

function apiIsOnline()
{
    $ch = curl_init(API_HEALTH_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    return is_array($result) && !empty($result['success']);
}

function renderApiOfflinePage($title = 'System Offline', $message = 'The API server is not running.')
{
    http_response_code(503);
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . htmlspecialchars($title) . '</title>';
    echo '<style>body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;color:#333;display:flex;justify-content:center;align-items:center;height:100vh;text-align:center} .box{max-width:460px;padding:2rem;background:#fff;border-radius:12px;box-shadow:0 14px 40px rgba(0,0,0,.08)}h1{margin:0 0 1rem;font-size:2rem}p{margin:.5rem 0;color:#555}.retry{display:inline-block;margin-top:1rem;padding:.75rem 1.25rem;border:none;border-radius:8px;background:#0C6D9E;color:#fff;font-weight:600;text-decoration:none;}</style></head>';
    echo '<body><div class="box"><h1>' . htmlspecialchars($title) . '</h1><p>' . htmlspecialchars($message) . '</p><p>The login and registration system depends on the API server at <strong>http://localhost:3000</strong>.</p><a class="retry" href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">Retry</a></div></body></html>';
    exit();
}

function requireApiOnline()
{
    if (!apiIsOnline()) {
        renderApiOfflinePage();
    }
}

// connect to database
$db = mysqli_connect('localhost', 'root', '', 'billing_system');

// variable declaration
$username = "";
$email = "";
$errors = array();

// call the register() function if register_btn is clicked
if (isset($_POST['register_btn'])) {
    register();
}

// call the login() function if register_btn is clicked
if (isset($_POST['login_btn'])) {
    login();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user']);
    header("location: ../login.php");
}

function callApi($path, $data)
{
    $url = "http://localhost:3000" . $path;
    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'message' => 'API connection failed: ' . $error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        return ['success' => false, 'message' => 'Invalid API response'];
    }

    return $result;
}

// REGISTER USER
function register()
{
    global $db, $errors, $username, $email;

    // receive all input values from the form
    $username = e($_POST['username']);
    $email = e($_POST['email']);
    $password_1 = e($_POST['password_1']);
    $password_2 = e($_POST['password_2']);

    // form validation: ensure that the form is correctly filled
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    // register user if there are no errors in the form
    if (count($errors) == 0) {
        $apiResponse = callApi('/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password_1,
            'user_type' => 'user'
        ]);

        if ($apiResponse['success']) {
            $_SESSION['user'] = $apiResponse['user'];
            header('location: index.php');
            exit();
        }

        array_push($errors, $apiResponse['message']);
    }
}

// LOGIN USER
function login()
{
    global $db, $username, $errors;

    // grab form values
    $username = e($_POST['username']);
    $password = e($_POST['password']);

    // make sure form is filled properly
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    // attempt login if no errors on form
    if (count($errors) == 0) {
        $apiResponse = callApi('/login', [
            'username' => $username,
            'password' => $password
        ]);

        if ($apiResponse['success']) {
            $logged_in_user = $apiResponse['user'];
            $_SESSION['user'] = $logged_in_user;

            if ($logged_in_user['user_type'] == 'admin') {
                header('location: admin/home.php');
            } else {
                header('location: index.php');
            }
            exit();
        }

        array_push($errors, $apiResponse['message']);
    }
}

function isLoggedIn()
{
    if (isset($_SESSION['user'])) {
        return true;
    } else {
        return false;
    }
}

function isAdmin()
{
    if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin') {
        return true;
    } else {
        return false;
    }
}

// escape string
function e($val)
{
    global $db;
    return mysqli_real_escape_string($db, trim($val));
}

function display_error()
{
    global $errors;

    if (count($errors) > 0) {
        echo '<div class="error">';
        foreach ($errors as $error) {
            echo $error . '<br>';
        }
        echo '</div>';
    }
}

function isBillOverdue($billDate, $paymentStatus, $months = 2)
{
    if ($paymentStatus) {
        return false;
    }

    $billDateObj = DateTime::createFromFormat('Y-m-d', $billDate);
    if (!$billDateObj) {
        return false;
    }

    $dueDate = clone $billDateObj;
    $dueDate->modify("+{$months} months");
    $today = new DateTime('today');

    return $today > $dueDate;
}

function getBillDueNotice($billDate, $paymentStatus)
{
    if (!isBillOverdue($billDate, $paymentStatus)) {
        return '';
    }

    return '<span class="overdue-notice">⚠️ Overdue 2+ months — disconnection notice</span>';
}

?>