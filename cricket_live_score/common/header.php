<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['admin_id']);
$is_user = isset($_SESSION['user_id']);

// Get base path
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Cricket Live Score</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155',
                            hover: '#475569'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            padding-bottom: 80px; /* Space for fixed bottom nav */
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</head>
<body class="bg-dark-bg text-white">
    
    <!-- Top Navigation Bar -->
    <nav class="bg-dark-card border-b border-dark-border sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo and App Name -->
                <div class="flex items-center space-x-3">
                    <i class="fas fa-cricket text-2xl text-green-500"></i>
                    <a href="<?php echo $base_path; ?>index.php" class="text-xl font-bold text-white">
                        Cricket Live Score
                    </a>
                </div>
                
                <!-- Right side icons -->
                <div class="flex items-center space-x-4">
                    <?php if ($is_admin): ?>
                        <span class="text-sm text-gray-400">Admin Panel</span>
                        <a href="<?php echo $base_path; ?>admin/logout.php" class="text-red-400 hover:text-red-300">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </a>
                    <?php elseif ($is_user): ?>
                        <?php
                        $user = getCurrentUser();
                        ?>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-400 hidden sm:block"><?php echo htmlspecialchars($user['name']); ?></span>
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $base_path; ?>login.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main class="max-w-7xl mx-auto px-4 py-6">
