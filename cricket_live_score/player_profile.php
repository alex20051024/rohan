<?php
require_once 'common/config.php';

$player_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($player_id == 0) {
    redirect('index.php');
}

// Fetch player details
$stmt = $conn->prepare("
    SELECT p.*, t.name as team_name, t.logo_url as team_logo 
    FROM players p
    JOIN teams t ON p.team_id = t.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$player = $stmt->get_result()->fetch_assoc();

if (!$player) {
    redirect('index.php');
}

$page_title = $player['name'];
include 'common/header.php';

// Calculate career stats
$career_batting = $conn->query("
    SELECT 
        COUNT(*) as matches_played,
        SUM(runs) as total_runs,
        SUM(balls) as total_balls,
        SUM(fours) as total_fours,
        SUM(sixes) as total_sixes,
        MAX(runs) as highest_score,
        AVG(runs) as average
    FROM batting_stats 
    WHERE player_id = $player_id
")->fetch_assoc();

$career_bowling = $conn->query("
    SELECT 
        SUM(overs_bowled) as total_overs,
        SUM(maidens) as total_maidens,
        SUM(runs_conceded) as total_runs,
        SUM(wickets) as total_wickets
    FROM bowling_stats 
    WHERE player_id = $player_id
")->fetch_assoc();
?>

<!-- Player Header -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 mb-6 text-center">
    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="text-4xl font-bold"><?php echo strtoupper(substr($player['name'], 0, 2)); ?></span>
    </div>
    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($player['name']); ?></h1>
    <div class="flex items-center justify-center space-x-4 text-sm">
        <span><i class="fas fa-user-tag mr-1"></i><?php echo htmlspecialchars($player['role']); ?></span>
        <span><i class="fas fa-shield-alt mr-1"></i><?php echo htmlspecialchars($player['team_name']); ?></span>
    </div>
</div>

<!-- Career Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    
    <!-- Batting Stats -->
    <div class="bg-dark-card border border-dark-border rounded-lg p-5">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <i class="fas fa-baseball-ball text-orange-500 mr-2"></i>
            Batting Statistics
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Matches Played</span>
                <span class="font-bold"><?php echo $career_batting['matches_played'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Total Runs</span>
                <span class="font-bold text-orange-500"><?php echo $career_batting['total_runs'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Highest Score</span>
                <span class="font-bold"><?php echo $career_batting['highest_score'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Average</span>
                <span class="font-bold"><?php echo $career_batting['average'] ? number_format($career_batting['average'], 2) : '0.00'; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Strike Rate</span>
                <span class="font-bold"><?php echo calculateStrikeRate($career_batting['total_runs'] ?: 0, $career_batting['total_balls'] ?: 0); ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Fours / Sixes</span>
                <span class="font-bold"><?php echo ($career_batting['total_fours'] ?: 0) . ' / ' . ($career_batting['total_sixes'] ?: 0); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Bowling Stats -->
    <div class="bg-dark-card border border-dark-border rounded-lg p-5">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <i class="fas fa-bowling-ball text-green-500 mr-2"></i>
            Bowling Statistics
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Total Wickets</span>
                <span class="font-bold text-green-500"><?php echo $career_bowling['total_wickets'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Overs Bowled</span>
                <span class="font-bold"><?php echo $career_bowling['total_overs'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Runs Conceded</span>
                <span class="font-bold"><?php echo $career_bowling['total_runs'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Maidens</span>
                <span class="font-bold"><?php echo $career_bowling['total_maidens'] ?: 0; ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Economy Rate</span>
                <span class="font-bold"><?php echo calculateEconomy($career_bowling['total_runs'] ?: 0, $career_bowling['total_overs'] ?: 0); ?></span>
            </div>
            <div class="flex justify-between p-3 bg-dark-bg rounded">
                <span class="text-gray-400">Average</span>
                <span class="font-bold">
                    <?php 
                    $bowling_avg = ($career_bowling['total_wickets'] > 0) ? 
                                   round($career_bowling['total_runs'] / $career_bowling['total_wickets'], 2) : 
                                   '0.00';
                    echo $bowling_avg;
                    ?>
                </span>
            </div>
        </div>
    </div>
    
</div>

<!-- Recent Matches -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-history text-blue-500 mr-2"></i>
        Recent Performances
    </h3>
    
    <?php
    $recent_matches = $conn->query("
        SELECT DISTINCT m.id, m.match_datetime,
               t1.name as team1, t2.name as team2,
               bs.runs, bs.balls, bs.fours, bs.sixes,
               bw.wickets, bw.overs_bowled, bw.runs_conceded
        FROM matches m
        LEFT JOIN batting_stats bs ON m.id = bs.match_id AND bs.player_id = $player_id
        LEFT JOIN bowling_stats bw ON m.id = bw.match_id AND bw.player_id = $player_id
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        WHERE (bs.player_id = $player_id OR bw.player_id = $player_id)
        ORDER BY m.match_datetime DESC
        LIMIT 10
    ");
    
    if ($recent_matches->num_rows > 0):
    ?>
    <div class="space-y-3">
        <?php while ($match = $recent_matches->fetch_assoc()): ?>
        <div class="p-3 bg-dark-bg rounded">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold">
                    <?php echo htmlspecialchars($match['team1']); ?> vs <?php echo htmlspecialchars($match['team2']); ?>
                </span>
                <span class="text-xs text-gray-400">
                    <?php echo date('d M Y', strtotime($match['match_datetime'])); ?>
                </span>
            </div>
            <div class="flex space-x-4 text-sm">
                <?php if ($match['runs'] !== null): ?>
                <div>
                    <span class="text-gray-400">Batting:</span>
                    <span class="font-bold text-orange-500"><?php echo $match['runs']; ?></span>
                    <span class="text-gray-500">(<?php echo $match['balls']; ?>)</span>
                </div>
                <?php endif; ?>
                <?php if ($match['wickets'] !== null): ?>
                <div>
                    <span class="text-gray-400">Bowling:</span>
                    <span class="font-bold text-green-500"><?php echo $match['wickets']; ?>-<?php echo $match['runs_conceded']; ?></span>
                    <span class="text-gray-500">(<?php echo $match['overs_bowled']; ?>)</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-400 text-center py-4">No recent performances</p>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
