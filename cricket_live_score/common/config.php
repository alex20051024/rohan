<?php
// Cricket Live Score - Database Configuration

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'cricket_live_score');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Helper function to get current user
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Helper function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to calculate strike rate
function calculateStrikeRate($runs, $balls) {
    if ($balls == 0) return 0;
    return round(($runs / $balls) * 100, 2);
}

// Helper function to calculate economy rate
function calculateEconomy($runs, $overs) {
    if ($overs == 0) return 0;
    return round($runs / $overs, 2);
}

// Helper function to format overs (e.g., 5.3 means 5 overs and 3 balls)
function formatOvers($balls) {
    $overs = floor($balls / 6);
    $remaining_balls = $balls % 6;
    return $overs . "." . $remaining_balls;
}

// Helper function to get team score
function getTeamScore($match_id, $team_id, $innings = 1) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(runs_scored), 0) + COALESCE(SUM(extra_runs), 0) as total_runs,
            COUNT(DISTINCT CASE WHEN wicket_type IS NOT NULL THEN out_player_id END) as wickets,
            COUNT(*) as total_balls
        FROM scoring 
        WHERE match_id = ? AND batting_team_id = ? AND innings = ?
        AND extra_type NOT IN ('Wd', 'Nb')
    ");
    $stmt->bind_param("iii", $match_id, $team_id, $innings);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');
?>
