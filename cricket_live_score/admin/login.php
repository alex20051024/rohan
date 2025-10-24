<?php
require_once '../common/config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;
                redirect('index.php');
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}

$page_title = 'Admin Login';
include '../common/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center">
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 w-full max-w-md">
        
        <!-- Icon -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-shield text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Admin Panel</h1>
            <p class="text-gray-400 text-sm mt-2">Login to manage Cricket Live Score</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500 text-red-500 rounded-lg p-4 mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="">
            
            <!-- Username -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-user mr-2"></i>Username
                </label>
                <input type="text" name="username" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                    placeholder="Enter admin username">
            </div>
            
            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition"
                    placeholder="Enter admin password">
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
            
        </form>
        
        <!-- Back to User Login -->
        <div class="mt-6 text-center">
            <a href="../login.php" class="text-blue-500 hover:text-blue-400 text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to User Login
            </a>
        </div>
        
    </div>
</div>

<?php include '../common/bottom.php'; ?>
