<?php
session_start();
require_once 'classes/UserDatabaseUtil.php';
require_once 'auth.php';

$error = "";
$success = "";

// If already logged in, redirect based on user type
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: main_menu.php');
    }
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if (UserDatabaseUtil::verifyUser($username, $password)) {
            $_SESSION['username'] = $username;
            // Redirect admin to admin dashboard, regular users to main menu
            $user = UserDatabaseUtil::getUser($username);
            if ($user !== null && $user->getIsAdmin()) {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: main_menu.php');
            }
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <div class="card-header">
                <h2 class="card-title">Login</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin: 15px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" style="padding: 20px;">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           class="form-control">
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px; flex-direction: column;">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="signup.php" class="btn btn-secondary" style="text-align: center;">Create New Account</a>
                    <a href="index.php" class="btn btn-secondary" style="text-align: center;">Back to Home</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>


