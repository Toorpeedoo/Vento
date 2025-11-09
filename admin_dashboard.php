<?php
session_start();
require_once 'auth.php';
requireAdmin();
require_once 'classes/UserDatabaseUtil.php';

$message = "";
$messageType = "";

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $usernameToDelete = isset($_POST['username']) ? trim($_POST['username']) : '';
    $currentUsername = getCurrentUsername();
    
    if (empty($usernameToDelete)) {
        $message = "Username is required.";
        $messageType = "error";
    } elseif ($usernameToDelete === $currentUsername) {
        $message = "You cannot delete your own account.";
        $messageType = "error";
    } else {
        if (UserDatabaseUtil::deleteUser($usernameToDelete)) {
            $message = "User and all their product data deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to delete user. User may not exist.";
            $messageType = "error";
        }
    }
}

// Get all users
$allUsers = UserDatabaseUtil::getAllUsers();
$regularUsers = UserDatabaseUtil::getAllRegularUsers();
$adminUsers = array_filter($allUsers, function($user) {
    return $user->getIsAdmin();
});

// Get product counts for each user
require_once 'classes/FileDatabaseUtil.php';
$userStats = [];
foreach ($allUsers as $user) {
    $products = FileDatabaseUtil::getAllProducts($user->getUsername());
    $userStats[$user->getUsername()] = count($products);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Admin Dashboard - VENTO</h1>
            <div class="nav-actions">
                <span style="color: var(--gray-700); margin-right: 1rem;">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</span>
                <a href="main_menu.php" class="btn btn-secondary btn-small">User Menu</a>
                <a href="logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 15px 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h2 class="card-title">User Statistics</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px;">
                <div style="text-align: center;">
                    <h3 style="font-size: 2em; color: var(--primary); margin: 0;"><?php echo count($allUsers); ?></h3>
                    <p style="color: var(--gray-600); margin: 5px 0 0 0;">Total Users</p>
                </div>
                <div style="text-align: center;">
                    <h3 style="font-size: 2em; color: var(--success); margin: 0;"><?php echo count($regularUsers); ?></h3>
                    <p style="color: var(--gray-600); margin: 5px 0 0 0;">Regular Users</p>
                </div>
                <div style="text-align: center;">
                    <h3 style="font-size: 2em; color: var(--warning); margin: 0;"><?php echo count($adminUsers); ?></h3>
                    <p style="color: var(--gray-600); margin: 5px 0 0 0;">Admin Users</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">User Management</h2>
            </div>

            <?php if (empty($allUsers)): ?>
                <div class="alert alert-info">
                    No users found.
                </div>
            <?php else: ?>
                <div class="search-container mb-3">
                    <div class="search-box">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search by username..." onkeyup="filterUsers()">
                        <button type="button" class="search-btn" onclick="filterUsers()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="search-info">
                        <span id="resultCount"><?php echo count($allUsers); ?></span> user(s) found
                    </div>
                </div>

                <div class="table-container">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user->getUsername()); ?></td>
                                    <td>
                                        <?php if ($user->getIsAdmin()): ?>
                                            <span style="color: var(--warning); font-weight: bold;">Admin</span>
                                        <?php else: ?>
                                            <span style="color: var(--gray-600);">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->getCreatedAt()); ?></td>
                                    <td><?php echo isset($userStats[$user->getUsername()]) ? $userStats[$user->getUsername()] : 0; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="edit_user.php?username=<?php echo urlencode($user->getUsername()); ?>" class="btn btn-primary btn-small">Edit</a>
                                            <?php if ($user->getUsername() !== getCurrentUsername()): ?>
                                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($user->getUsername()); ?>? This will also delete all their products and data. This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user->getUsername()); ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--gray-500); font-size: 0.9em;">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const allUsers = <?php echo json_encode(array_map(function($u) use ($userStats) {
            return [
                'username' => $u->getUsername(),
                'isAdmin' => $u->getIsAdmin(),
                'createdAt' => $u->getCreatedAt(),
                'products' => isset($userStats[$u->getUsername()]) ? $userStats[$u->getUsername()] : 0
            ];
        }, $allUsers)); ?>;
        
        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.getElementById('usersTableBody');
            const resultCount = document.getElementById('resultCount');
            let count = 0;
            
            tbody.innerHTML = '';
            
            allUsers.forEach(user => {
                const username = user.username.toLowerCase();
                
                if (username.includes(searchTerm)) {
                    const row = tbody.insertRow();
                    const roleHtml = user.isAdmin 
                        ? '<span style="color: var(--warning); font-weight: bold;">Admin</span>'
                        : '<span style="color: var(--gray-600);">User</span>';
                    
                    const actionsHtml = user.username === '<?php echo getCurrentUsername(); ?>'
                        ? '<span style="color: var(--gray-500); font-size: 0.9em;">(You)</span>'
                        : `<a href="edit_user.php?username=${encodeURIComponent(user.username)}" class="btn btn-primary btn-small">Edit</a>
                           <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user ${escapeHtml(user.username)}? This will also delete all their products and data. This action cannot be undone.');">
                               <input type="hidden" name="action" value="delete">
                               <input type="hidden" name="username" value="${escapeHtml(user.username)}">
                               <button type="submit" class="btn btn-danger btn-small">Delete</button>
                           </form>`;
                    
                    row.innerHTML = `
                        <td>${escapeHtml(user.username)}</td>
                        <td>${roleHtml}</td>
                        <td>${escapeHtml(user.createdAt)}</td>
                        <td>${user.products}</td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                ${actionsHtml}
                            </div>
                        </td>
                    `;
                    count++;
                }
            });
            
            resultCount.textContent = count;
            
            if (count === 0 && searchTerm !== '') {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray-500);">No users found matching your search</td></tr>';
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

