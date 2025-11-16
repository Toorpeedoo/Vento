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

// Get all users (optimized - single load instead of multiple)
$allUsers = UserDatabaseUtil::getAllUsers();
$regularUsers = array_filter($allUsers, function($user) {
    return !$user->getIsAdmin();
});
$adminUsers = array_filter($allUsers, function($user) {
    return $user->getIsAdmin();
});

// Get product counts for each user (optimized - just counts lines without parsing products)
require_once 'classes/ProductDatabaseUtil.php';
$userStats = [];
foreach ($allUsers as $user) {
    $userStats[$user->getUsername()] = ProductDatabaseUtil::getProductCount($user->getUsername());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VENTO</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .admin-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .admin-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--stat-color-1), var(--stat-color-2));
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.primary {
            --stat-color-1: #667eea;
            --stat-color-2: #764ba2;
        }
        
        .stat-card.success {
            --stat-color-1: #10b981;
            --stat-color-2: #059669;
        }
        
        .stat-card.warning {
            --stat-color-1: #f59e0b;
            --stat-color-2: #d97706;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
            color: white;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: #6b7280;
            margin: 0.5rem 0 0 0;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .user-table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 1.5rem;
            border-bottom: 2px solid #d1d5db;
        }
        
        .table-header h2 {
            margin: 0;
            color: #111827;
            font-size: 1.5rem;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-user {
            background: #e5e7eb;
            color: #6b7280;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        #usersTable tbody tr {
            transition: all 0.2s ease;
        }
        
        #usersTable tbody tr:hover {
            background-color: #f9fafb;
            transform: scale(1.01);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-bar">
            <h1 class="nav-title">Admin Dashboard - VENTO</h1>
            <div class="nav-actions">
                <span style="color: var(--gray-700); margin-right: 1rem;">Welcome, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</span>
                <a href="logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin: 15px 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <h1>👨‍💼 Admin Control Panel</h1>
            <p>Manage users and monitor system activity</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="stat-value"><?php echo count($allUsers); ?></h3>
                <p class="stat-label">Total Users</p>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <polyline points="17 11 19 13 23 9"></polyline>
                    </svg>
                </div>
                <h3 class="stat-value"><?php echo count($regularUsers); ?></h3>
                <p class="stat-label">Regular Users</p>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <h3 class="stat-value"><?php echo count($adminUsers); ?></h3>
                <p class="stat-label">Admin Users</p>
            </div>
        </div>

        <div class="user-table-card">
            <div class="table-header">
                <h2>👥 User Management</h2>
            </div>

            <?php if (empty($allUsers)): ?>
                <div class="alert alert-info">
                    No users found.
                </div>
            <?php else: ?>
                <div class="search-container mb-3" style="padding: 1.5rem;">
                    <div class="search-box">
                        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search by username..." onkeyup="filterUsers()">
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
                                            <span class="badge-admin">👑 Admin</span>
                                        <?php else: ?>
                                            <span class="badge-user">👤 User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user->getCreatedAt()); ?></td>
                                    <td><strong><?php echo isset($userStats[$user->getUsername()]) ? $userStats[$user->getUsername()] : 0; ?></strong> items</td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="edit_user.php?username=<?php echo urlencode($user->getUsername()); ?>" class="btn btn-primary btn-small">✏️ Edit</a>
                                            <?php if ($user->getUsername() !== getCurrentUsername()): ?>
                                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($user->getUsername()); ?>? This will also delete all their products and data. This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user->getUsername()); ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">🗑️ Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--gray-500); font-size: 0.9em; padding: 0.4rem 0.8rem; background: #f3f4f6; border-radius: 6px;">(Current User)</span>
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
                        ? '<span class="badge-admin">👑 Admin</span>'
                        : '<span class="badge-user">👤 User</span>';
                    
                    const actionsHtml = user.username === '<?php echo getCurrentUsername(); ?>'
                        ? '<span style="color: var(--gray-500); font-size: 0.9em; padding: 0.4rem 0.8rem; background: #f3f4f6; border-radius: 6px;">(Current User)</span>'
                        : `<a href="edit_user.php?username=${encodeURIComponent(user.username)}" class="btn btn-primary btn-small">✏️ Edit</a>
                           <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user ${escapeHtml(user.username)}? This will also delete all their products and data. This action cannot be undone.');">
                               <input type="hidden" name="action" value="delete">
                               <input type="hidden" name="username" value="${escapeHtml(user.username)}">
                               <button type="submit" class="btn btn-danger btn-small">🗑️ Delete</button>
                           </form>`;
                    
                    row.innerHTML = `
                        <td>${escapeHtml(user.username)}</td>
                        <td>${roleHtml}</td>
                        <td>${escapeHtml(user.createdAt)}</td>
                        <td><strong>${user.products}</strong> items</td>
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

