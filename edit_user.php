<?php
session_start();
require_once 'auth.php';
requireAdmin();
require_once 'classes/UserDatabaseUtil.php';
require_once 'classes/User.php';

$message = "";
$messageType = "";
$usernameToEdit = isset($_GET['username']) ? trim($_GET['username']) : '';

// Check if redirected after update
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = "User updated successfully!";
    $messageType = "success";
}

if (empty($usernameToEdit)) {
    header('Location: admin_dashboard.php');
    exit();
}

$userToEdit = UserDatabaseUtil::getUser($usernameToEdit);
if ($userToEdit === null) {
    header('Location: admin_dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
    $newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
    $isAdmin = isset($_POST['isAdmin']) && $_POST['isAdmin'] === '1';
    
    if (empty($newUsername)) {
        $message = "Username cannot be empty.";
        $messageType = "error";
    } elseif (strlen($newUsername) < 3) {
        $message = "Username must be at least 3 characters long.";
        $messageType = "error";
    } elseif (!empty($newPassword) && strlen($newPassword) < 4) {
        $message = "Password must be at least 4 characters long.";
        $messageType = "error";
    } else {
        // Check if new username already exists (if changed)
        if (strtolower($newUsername) !== strtolower($usernameToEdit)) {
            if (UserDatabaseUtil::usernameExists($newUsername)) {
                $message = "Username already exists. Please choose a different username.";
                $messageType = "error";
            }
        }
        
        if ($messageType !== "error") {
            try {
                // Create updated user object
                $updatedUser = new User($newUsername, "", $userToEdit->getCreatedAt(), false, $isAdmin);
                
                // Handle password: use the value from the password field
                $currentPlainPassword = $userToEdit->getPlainPassword();
                if (!empty($newPassword) && $newPassword !== '(encrypted - cannot view)') {
                    // Password field has a value (either unchanged or new password)
                    $updatedUser->setPlainPassword($newPassword);
                } else {
                    // Keep existing password (plain text if available)
                    if ($currentPlainPassword !== null) {
                        $updatedUser->setPlainPassword($currentPlainPassword);
                    } else {
                        // Fallback: for old encrypted accounts, we can't retrieve plain text
                        // In this case, we need to set a new password or keep the hash
                        // For now, keep the hash (user will need to set a new password)
                        $updatedUser->setPasswordHash($userToEdit->getPassword());
                    }
                }
                
                // Update the user
                if (UserDatabaseUtil::updateUser($usernameToEdit, $updatedUser)) {
                    // If admin edited their own username, update session
                    if (strtolower(getCurrentUsername()) === strtolower($usernameToEdit)) {
                        $_SESSION['username'] = $newUsername;
                    }
                    
                    // If username changed, redirect to new username
                    if (strtolower($usernameToEdit) !== strtolower($newUsername)) {
                        header('Location: edit_user.php?username=' . urlencode($newUsername) . '&updated=1');
                        exit();
                    }
                    
                    // Refresh user data from database
                    $userToEdit = UserDatabaseUtil::getUser($newUsername);
                    $message = "User updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update user.";
                    $messageType = "error";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = "error";
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
    <title>Edit User - VENTO Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Edit User</h1>
            <div class="nav-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary btn-small">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card-header">
                <h2 class="card-title">Edit User: <?php echo htmlspecialchars($userToEdit->getUsername()); ?></h2>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-input" id="username" name="username" 
                           value="<?php echo htmlspecialchars($userToEdit->getUsername()); ?>" 
                           required minlength="3">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="text" class="form-input" id="password" name="password" 
                           value="<?php echo htmlspecialchars($userToEdit->getPlainPassword() !== null ? $userToEdit->getPlainPassword() : '(encrypted - cannot view)'); ?>" 
                           placeholder="Enter new password to change, leave blank to keep current">
                    <small style="color: var(--gray-600); display: block; margin-top: 5px;">
                        Enter a new password to change it, or leave blank to keep the current password. Password is stored in plain text for admin visibility.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="isAdmin" value="1" 
                               <?php echo $userToEdit->getIsAdmin() ? 'checked' : ''; ?> 
                               style="margin-right: 8px;">
                        Admin User
                    </label>
                    <small style="color: var(--gray-600); display: block; margin-top: 5px;">
                        Admin users have access to the admin dashboard and can manage other users.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Created</label>
                    <input type="text" class="form-input" 
                           value="<?php echo htmlspecialchars($userToEdit->getCreatedAt()); ?>" 
                           disabled style="background-color: var(--gray-100);">
                    <small style="color: var(--gray-600);">This field cannot be modified</small>
                </div>

                <div class="flex gap-2" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

