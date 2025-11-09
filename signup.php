<?php
session_start();
require_once 'classes/UserDatabaseUtil.php';
require_once 'classes/User.php';
require_once 'auth.php';

$error = "";
$success = "";

// If already logged in, redirect to main menu
if (isLoggedIn()) {
    header('Location: main_menu.php');
    exit();
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } else {
        // Check if username already exists (case-insensitive check)
        $existingUser = UserDatabaseUtil::getUser($username);
        if ($existingUser !== null) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Create new user
            try {
                $user = new User($username, $password);
                if (UserDatabaseUtil::addUser($user)) {
                    $success = "Account created successfully! You can now login.";
                    // Clear form
                    $username = '';
                    $password = '';
                    $confirmPassword = '';
                } else {
                    // Double check if username was added by another process
                    if (UserDatabaseUtil::usernameExists($username)) {
                        $error = "Username already exists. Please choose a different username.";
                    } else {
                        $error = "Failed to create account. Please try again.";
                    }
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <div class="card-header">
                <h2 class="card-title">Create Account</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin: 15px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin: 15px;">
                    <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 10px;">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="signup.php" style="padding: 20px;">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           class="form-control" minlength="3">
                    <small style="color: #666;">Must be at least 3 characters</small>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required 
                           class="form-control" minlength="4">
                    <small style="color: #666;">Must be at least 4 characters</small>
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="form-control" minlength="4">
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px; flex-direction: column;">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <a href="login.php" class="btn btn-secondary" style="text-align: center;">Already have an account? Login</a>
                    <a href="index.php" class="btn btn-secondary" style="text-align: center;">Back to Home</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

