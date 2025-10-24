<?php
require_once '../common/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('login.php');
}

// Fetch statistics
$total_tournaments = $conn->query("SELECT COUNT(*) as count FROM tournaments")->fetch_assoc()['count'];
$total_teams = $conn->query("SELECT COUNT(*) as count FROM teams")->fetch_assoc()['count'];
$total_players = $conn->query("SELECT COUNT(*) as count FROM players")->fetch_assoc()['count'];
$total_matches = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
$live_matches = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status = 'Live'")->fetch_assoc()['count'];

$page_title = 'Admin Dashboard';
include '../common/header.php';
?>

<!-- Dashboard Header -->
<div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg p-8 mb-8 text-center">
    <h1 class="text-3xl font-bold mb-2">
        <i class="fas fa-chart-line mr-3"></i>Admin Dashboard
    </h1>
    <p class="text-purple-100">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    
    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 text-center">
        <i class="fas fa-trophy text-4xl mb-3 opacity-80"></i>
        <div class="text-3xl font-bold mb-1"><?php echo $total_tournaments; ?></div>
        <div class="text-sm text-blue-100">Tournaments</div>
    </div>
    
    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 text-center">
        <i class="fas fa-shield-alt text-4xl mb-3 opacity-80"></i>
        <div class="text-3xl font-bold mb-1"><?php echo $total_teams; ?></div>
        <div class="text-sm text-green-100">Teams</div>
    </div>
    
    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg p-6 text-center">
        <i class="fas fa-users text-4xl mb-3 opacity-80"></i>
        <div class="text-3xl font-bold mb-1"><?php echo $total_players; ?></div>
        <div class="text-sm text-purple-100">Players</div>
    </div>
    
    <div class="bg-gradient-to-br from-orange-600 to-orange-700 rounded-lg p-6 text-center">
        <i class="fas fa-cricket text-4xl mb-3 opacity-80"></i>
        <div class="text-3xl font-bold mb-1"><?php echo $total_matches; ?></div>
        <div class="text-sm text-orange-100">Total Matches</div>
    </div>
    
    <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-lg p-6 text-center">
        <i class="fas fa-circle-dot text-4xl mb-3 opacity-80 animate-pulse"></i>
        <div class="text-3xl font-bold mb-1"><?php echo $live_matches; ?></div>
        <div class="text-sm text-red-100">Live Matches</div>
    </div>
    
</div>

<!-- Quick Actions -->
<div class="bg-dark-card border border-dark-border rounded-lg p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">
        <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="manage_tournaments.php?action=add" 
           class="bg-blue-600 hover:bg-blue-700 rounded-lg p-4 text-center transition">
            <i class="fas fa-plus-circle text-2xl mb-2 block"></i>
            <span class="font-semibold">Create Tournament</span>
        </a>
        <a href="manage_teams.php?action=add" 
           class="bg-green-600 hover:bg-green-700 rounded-lg p-4 text-center transition">
            <i class="fas fa-plus-circle text-2xl mb-2 block"></i>
            <span class="font-semibold">Add Team</span>
        </a>
        <a href="manage_matches.php?action=add" 
           class="bg-orange-600 hover:bg-orange-700 rounded-lg p-4 text-center transition">
            <i class="fas fa-plus-circle text-2xl mb-2 block"></i>
            <span class="font-semibold">Schedule Match</span>
        </a>
    </div>
</div>

<!-- Management Links -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <a href="manage_tournaments.php" class="bg-dark-card border border-dark-border hover:border-blue-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-trophy text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Tournaments</h3>
                <p class="text-sm text-gray-400">Manage tournaments</p>
            </div>
        </div>
    </a>
    
    <a href="manage_teams.php" class="bg-dark-card border border-dark-border hover:border-green-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Teams</h3>
                <p class="text-sm text-gray-400">Manage teams</p>
            </div>
        </div>
    </a>
    
    <a href="manage_players.php" class="bg-dark-card border border-dark-border hover:border-purple-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-purple-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Players</h3>
                <p class="text-sm text-gray-400">Manage players</p>
            </div>
        </div>
    </a>
    
    <a href="manage_matches.php" class="bg-dark-card border border-dark-border hover:border-orange-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-orange-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-cricket text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Matches</h3>
                <p class="text-sm text-gray-400">Schedule & manage matches</p>
            </div>
        </div>
    </a>
    
    <a href="manage_sponsors.php" class="bg-dark-card border border-dark-border hover:border-yellow-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-yellow-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-handshake text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">Sponsors</h3>
                <p class="text-sm text-gray-400">Manage sponsors</p>
            </div>
        </div>
    </a>
    
    <a href="../index.php" class="bg-dark-card border border-dark-border hover:border-pink-500 rounded-lg p-6 transition block">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-pink-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-globe text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">View Website</h3>
                <p class="text-sm text-gray-400">Go to public site</p>
            </div>
        </div>
    </a>
    
</div>

<?php include '../common/bottom.php'; ?>
