<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberSphere Admin</title>
    <link rel="stylesheet" href="<?php echo isset($is_subdir) && $is_subdir ? '../assets/css/style.css' : 'assets/css/style.css'; ?>">
    <!-- Add any other common CSS/JS links here -->
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="<?php echo isset($is_subdir) && $is_subdir ? '../dashboard.php' : 'dashboard.php'; ?>" class="logo">CyberSphere</a>
            <nav>
                <ul>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? '../dashboard.php' : 'dashboard.php'; ?>">Dashboard</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'computers.php' : 'admin/computers.php'; ?>">Computers</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'users.php' : 'admin/users.php'; ?>">Users</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'search.php' : 'admin/search.php'; ?>">Search</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'reports.php' : 'admin/reports.php'; ?>">Reports</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'profile.php' : 'admin/profile.php'; ?>">Profile</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? 'change_password.php' : 'admin/change_password.php'; ?>">Change Password</a></li>
                    <li><a href="<?php echo isset($is_subdir) && $is_subdir ? '../logout.php' : 'logout.php'; ?>">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <!-- Page content will go here -->
