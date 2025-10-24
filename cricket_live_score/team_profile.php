<?php
require_once 'common/config.php';

$team_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($team_id == 0) {
    redirect('index.php');
}

// Fetch team details
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();

if (!$team) {
    redirect('index.php');
}

$page_title = $team['name'];
include 'common/header.php';

// Fetch team players
$players = $conn->query("
    SELECT * FROM players 
    WHERE team_id = $team_id
    ORDER BY role, name
");

// Fetch team stats
$team_stats = $conn->query("
    SELECT 
        COUNT(*) as total_matches,
        SUM(CASE WHEN winner_team_id = $team_id THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN winner_team_id != $team_id AND winner_team_id IS NOT NULL THEN 1 ELSE 0 END) as losses
    FROM matches
    WHERE (team1_id = $team_id OR team2_id = $team_id) AND status = 'Completed'
")->fetch_assoc();
?>

<!-- Team Header -->
<div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 mb-6 text-center">
    <?php if ($team['logo_url']): ?>
    <img src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="" class="w-24 h-24 object-contain mx-auto mb-4">
    <?php else: ?>
    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shield-alt text-4xl"></i>
    </div>
    <?php endif; ?>
    <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($team['name']); ?></h1>
    
    <!-- Team Stats Summary -->
    <div class="grid grid-cols-3 gap-4 max-w-md mx-auto">
        <div class="bg-white/10 rounded-lg p-3">
            <div class="text-2xl font-bold"><?php echo $team_stats['total_matches']; ?></div>
            <div class="text-xs text-gray-200">Matches</div>
        </div>
        <div class="bg-white/10 rounded-lg p-3">
            <div class="text-2xl font-bold text-green-400"><?php echo $team_stats['wins']; ?></div>
            <div class="text-xs text-gray-200">Wins</div>
        </div>
        <div class="bg-white/10 rounded-lg p-3">
            <div class="text-2xl font-bold text-red-400"><?php echo $team_stats['losses']; ?></div>
            <div class="text-xs text-gray-200">Losses</div>
        </div>
    </div>
</div>

<!-- Players Section -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-users text-blue-500 mr-2"></i>
        Team Squad
    </h3>
    
    <?php if ($players->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php while ($player = $players->fetch_assoc()): ?>
        <a href="player_profile.php?id=<?php echo $player['id']; ?>" 
           class="bg-dark-bg hover:bg-dark-hover border border-dark-border rounded-lg p-4 transition block">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="font-bold"><?php echo strtoupper(substr($player['name'], 0, 2)); ?></span>
                </div>
                <div>
                    <div class="font-semibold"><?php echo htmlspecialchars($player['name']); ?></div>
                    <div class="text-xs text-gray-400"><?php echo htmlspecialchars($player['role']); ?></div>
                </div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-400 text-center py-4">No players found</p>
    <?php endif; ?>
</div>

<!-- Recent Matches -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-history text-purple-500 mr-2"></i>
        Recent Matches
    </h3>
    
    <?php
    $recent_matches = $conn->query("
        SELECT m.*,
               t1.name as team1_name, t1.logo_url as team1_logo,
               t2.name as team2_name, t2.logo_url as team2_logo,
               tr.name as tournament_name
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        JOIN tournaments tr ON m.tournament_id = tr.id
        WHERE (m.team1_id = $team_id OR m.team2_id = $team_id)
        AND m.status = 'Completed'
        ORDER BY m.match_datetime DESC
        LIMIT 5
    ");
    
    if ($recent_matches->num_rows > 0):
    ?>
    <div class="space-y-3">
        <?php while ($match = $recent_matches->fetch_assoc()): ?>
        <a href="scorecard.php?id=<?php echo $match['id']; ?>" 
           class="block p-4 bg-dark-bg hover:bg-dark-hover rounded-lg transition">
            <div class="text-xs text-gray-400 mb-2"><?php echo htmlspecialchars($match['tournament_name']); ?></div>
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <?php if ($match['team1_logo']): ?>
                            <img src="<?php echo htmlspecialchars($match['team1_logo']); ?>" alt="" class="w-6 h-6 object-contain">
                            <?php endif; ?>
                            <span class="text-sm font-semibold"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                        </div>
                        <?php 
                        $t1_score = getTeamScore($match['id'], $match['team1_id'], 1);
                        ?>
                        <span class="text-sm font-bold">
                            <?php echo $t1_score['total_runs']; ?>/<?php echo $t1_score['wickets']; ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <?php if ($match['team2_logo']): ?>
                            <img src="<?php echo htmlspecialchars($match['team2_logo']); ?>" alt="" class="w-6 h-6 object-contain">
                            <?php endif; ?>
                            <span class="text-sm font-semibold"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                        </div>
                        <?php 
                        $t2_score = getTeamScore($match['id'], $match['team2_id'], 2);
                        ?>
                        <span class="text-sm font-bold">
                            <?php echo $t2_score['total_runs']; ?>/<?php echo $t2_score['wickets']; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php if ($match['winner_team_id'] == $team_id): ?>
            <div class="mt-2 text-xs text-green-500 font-semibold">
                <i class="fas fa-check-circle mr-1"></i>Won
            </div>
            <?php else: ?>
            <div class="mt-2 text-xs text-red-500 font-semibold">
                <i class="fas fa-times-circle mr-1"></i>Lost
            </div>
            <?php endif; ?>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-400 text-center py-4">No recent matches</p>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
