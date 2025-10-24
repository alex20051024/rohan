<?php
// Cricket Live Score - Installation Script
// This file will create database and tables automatically

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$dbname = 'cricket_live_score';

// Create connection without database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    $success_msg = "Database created successfully<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($dbname);

// Create tables
$tables = [];

// Users table
$tables[] = "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT 1 COMMENT '1=User, 2=Scorer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Admin table
$tables[] = "CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Tournaments table
$tables[] = "CREATE TABLE IF NOT EXISTS `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `format` varchar(50) NOT NULL COMMENT 'T20, ODI, Test',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Teams table
$tables[] = "CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Players table
$tables[] = "CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL COMMENT 'Batsman, Bowler, All-rounder, Wicket-keeper',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Matches table
$tables[] = "CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `match_datetime` datetime NOT NULL,
  `status` varchar(50) DEFAULT 'Scheduled' COMMENT 'Scheduled, Live, Completed',
  `winner_team_id` int(11) DEFAULT NULL,
  `toss_winner_id` int(11) DEFAULT NULL,
  `toss_decision` varchar(50) DEFAULT NULL COMMENT 'Bat, Bowl',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`team1_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`team2_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Sponsors table
$tables[] = "CREATE TABLE IF NOT EXISTS `sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Tournament Sponsors (Junction table)
$tables[] = "CREATE TABLE IF NOT EXISTS `tournament_sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `sponsor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sponsor_id`) REFERENCES `sponsors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Points Table
$tables[] = "CREATE TABLE IF NOT EXISTS `points_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `played` int(11) DEFAULT 0,
  `won` int(11) DEFAULT 0,
  `lost` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `nrr` decimal(5,3) DEFAULT 0.000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tournament_team` (`tournament_id`, `team_id`),
  FOREIGN KEY (`tournament_id`) REFERENCES `tournaments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Scoring table (Ball by ball)
$tables[] = "CREATE TABLE IF NOT EXISTS `scoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `innings` int(11) NOT NULL DEFAULT 1,
  `over_number` int(11) NOT NULL,
  `ball_number` int(11) NOT NULL,
  `batting_team_id` int(11) NOT NULL,
  `bowling_team_id` int(11) NOT NULL,
  `batsman_id` int(11) NOT NULL,
  `bowler_id` int(11) NOT NULL,
  `runs_scored` int(11) DEFAULT 0,
  `extra_runs` int(11) DEFAULT 0,
  `extra_type` varchar(20) DEFAULT NULL COMMENT 'Wd, Nb, Lb, B',
  `wicket_type` varchar(50) DEFAULT NULL COMMENT 'Bowled, Caught, LBW, Run Out, etc.',
  `out_player_id` int(11) DEFAULT NULL,
  `commentary_text` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`match_id`) REFERENCES `matches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Batting Stats
$tables[] = "CREATE TABLE IF NOT EXISTS `batting_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `innings` int(11) NOT NULL DEFAULT 1,
  `runs` int(11) DEFAULT 0,
  `balls` int(11) DEFAULT 0,
  `fours` int(11) DEFAULT 0,
  `sixes` int(11) DEFAULT 0,
  `is_out` tinyint(1) DEFAULT 0,
  `dismissal_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match_player_innings` (`match_id`, `player_id`, `innings`),
  FOREIGN KEY (`match_id`) REFERENCES `matches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Bowling Stats
$tables[] = "CREATE TABLE IF NOT EXISTS `bowling_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `innings` int(11) NOT NULL DEFAULT 1,
  `overs_bowled` decimal(3,1) DEFAULT 0.0,
  `maidens` int(11) DEFAULT 0,
  `runs_conceded` int(11) DEFAULT 0,
  `wickets` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_match_player_innings_bowling` (`match_id`, `player_id`, `innings`),
  FOREIGN KEY (`match_id`) REFERENCES `matches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Match Scorers (for double-input system)
$tables[] = "CREATE TABLE IF NOT EXISTS `match_scorers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `scorer_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`match_id`) REFERENCES `matches`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`scorer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create all tables
foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        $success_msg .= "Table created successfully<br>";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// Insert default admin credentials
$admin_username = 'admin';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "INSERT INTO `admin` (`username`, `password`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `username` = `username`";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_username, $admin_password);
$stmt->execute();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete - Cricket Live Score</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                            border: '#334155'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-4">Installation Complete!</h1>
        <p class="text-gray-400 mb-6">Cricket Live Score has been successfully installed.</p>
        <div class="bg-dark-bg border border-dark-border rounded p-4 mb-6 text-left">
            <p class="text-sm text-gray-300 mb-2"><strong>Admin Credentials:</strong></p>
            <p class="text-sm text-gray-400">Username: <span class="text-white font-mono">admin</span></p>
            <p class="text-sm text-gray-400">Password: <span class="text-white font-mono">admin123</span></p>
        </div>
        <div class="space-y-3">
            <a href="index.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                Go to Homepage
            </a>
            <a href="admin/login.php" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                Admin Login
            </a>
        </div>
    </div>
</body>
</html>
<?php
// Auto-delete install.php after successful installation
unlink(__FILE__);
?>
