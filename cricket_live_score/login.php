<?php
require_once 'common/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = isset($_GET['registered']) ? 'Registration successful! Please login.' : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role_id'];
                redirect('index.php');
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$page_title = 'Login';
include 'common/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center">
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 w-full max-w-md">
        
        <!-- Icon -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-cricket text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Welcome Back</h1>
            <p class="text-gray-400 text-sm mt-2">Login to Cricket Live Score</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500 text-red-500 rounded-lg p-4 mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Success Message -->
        <?php if ($success): ?>
        <div class="bg-green-500/10 border border-green-500 text-green-500 rounded-lg p-4 mb-4">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="">
            
            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input type="email" name="email" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition"
                    placeholder="Enter your email">
            </div>
            
            <!-- Password -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition"
                    placeholder="Enter your password">
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
            
        </form>
        
        <!-- Signup Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">
                Don't have an account? 
                <a href="signup.php" class="text-blue-500 hover:text-blue-400 font-semibold">Sign Up</a>
            </p>
        </div>
        
        <!-- Admin Login Link -->
        <div class="mt-4 text-center">
            <a href="admin/login.php" class="text-purple-500 hover:text-purple-400 text-sm">
                <i class="fas fa-user-shield mr-1"></i>Admin Login
            </a>
        </div>
        
    </div>
</div>

<?php include 'common/bottom.php'; ?>
