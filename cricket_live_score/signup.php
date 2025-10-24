<?php
require_once 'common/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? intval($_POST['role']) : 1;
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                redirect('login.php?registered=1');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$page_title = 'Sign Up';
include 'common/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-8">
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 w-full max-w-md">
        
        <!-- Icon -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Create Account</h1>
            <p class="text-gray-400 text-sm mt-2">Join Cricket Live Score</p>
        </div>
        
        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="bg-red-500/10 border border-red-500 text-red-500 rounded-lg p-4 mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Signup Form -->
        <form method="POST" action="">
            
            <!-- Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-user mr-2"></i>Full Name
                </label>
                <input type="text" name="name" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition"
                    placeholder="Enter your full name">
            </div>
            
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
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition"
                    placeholder="Enter your password">
            </div>
            
            <!-- Confirm Password -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-lock mr-2"></i>Confirm Password
                </label>
                <input type="password" name="confirm_password" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition"
                    placeholder="Confirm your password">
            </div>
            
            <!-- Role Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-id-badge mr-2"></i>Register as
                </label>
                <select name="role" class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition">
                    <option value="1">User</option>
                    <option value="2">Scorer</option>
                </select>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </button>
            
        </form>
        
        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">
                Already have an account? 
                <a href="login.php" class="text-blue-500 hover:text-blue-400 font-semibold">Login</a>
            </p>
        </div>
        
    </div>
</div>

<?php include 'common/bottom.php'; ?>
