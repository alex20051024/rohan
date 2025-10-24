<?php
require_once 'common/config.php';

$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tournament_id == 0) {
    redirect('index.php');
}

// Fetch tournament details
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();

if (!$tournament) {
    redirect('index.php');
}

$page_title = $tournament['name'];
include 'common/header.php';

// Fetch sponsors for this tournament
$sponsors = $conn->query("
    SELECT s.* FROM sponsors s
    JOIN tournament_sponsors ts ON s.id = ts.sponsor_id
    WHERE ts.tournament_id = $tournament_id
");

// Active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'fixtures';
?>

<!-- Tournament Header -->
<div class="bg-gradient-to-r from-yellow-600 to-orange-600 rounded-lg p-6 mb-6">
    <div class="text-center">
        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trophy text-4xl"></i>
        </div>
        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($tournament['name']); ?></h1>
        <div class="flex items-center justify-center space-x-4 text-sm">
            <span><i class="fas fa-flag mr-1"></i><?php echo htmlspecialchars($tournament['format']); ?></span>
            <span><i class="fas fa-calendar mr-1"></i>
                <?php echo date('d M', strtotime($tournament['start_date'])); ?> - 
                <?php echo date('d M Y', strtotime($tournament['end_date'])); ?>
            </span>
        </div>
    </div>
    
    <!-- Sponsors -->
    <?php if ($sponsors->num_rows > 0): ?>
    <div class="mt-6 pt-4 border-t border-white/20">
        <div class="flex items-center justify-center flex-wrap gap-4">
            <span class="text-xs text-white/70 w-full text-center mb-2">Sponsors</span>
            <?php while ($sponsor = $sponsors->fetch_assoc()): ?>
                <?php if ($sponsor['logo_url']): ?>
                <img src="<?php echo htmlspecialchars($sponsor['logo_url']); ?>" 
                     alt="<?php echo htmlspecialchars($sponsor['name']); ?>" 
                     class="h-8 object-contain bg-white/10 rounded px-3 py-1">
                <?php else: ?>
                <span class="text-xs bg-white/10 rounded px-3 py-1"><?php echo htmlspecialchars($sponsor['name']); ?></span>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tabs -->
<div class="flex space-x-2 mb-6 overflow-x-auto">
    <a href="?id=<?php echo $tournament_id; ?>&tab=fixtures" 
       class="px-6 py-3 rounded-lg font-semibold whitespace-nowrap <?php echo $active_tab == 'fixtures' ? 'bg-blue-600' : 'bg-dark-card hover:bg-dark-hover'; ?> transition">
        <i class="fas fa-list mr-2"></i>Fixtures
    </a>
    <a href="?id=<?php echo $tournament_id; ?>&tab=points" 
       class="px-6 py-3 rounded-lg font-semibold whitespace-nowrap <?php echo $active_tab == 'points' ? 'bg-blue-600' : 'bg-dark-card hover:bg-dark-hover'; ?> transition">
        <i class="fas fa-table mr-2"></i>Points Table
    </a>
    <a href="?id=<?php echo $tournament_id; ?>&tab=stats" 
       class="px-6 py-3 rounded-lg font-semibold whitespace-nowrap <?php echo $active_tab == 'stats' ? 'bg-blue-600' : 'bg-dark-card hover:bg-dark-hover'; ?> transition">
        <i class="fas fa-chart-bar mr-2"></i>Stats
    </a>
</div>

<!-- Tab Content -->
<?php if ($active_tab == 'fixtures'): ?>
    <?php
    $fixtures = $conn->query("
        SELECT m.*, 
               t1.name as team1_name, t1.logo_url as team1_logo,
               t2.name as team2_name, t2.logo_url as team2_logo
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        WHERE m.tournament_id = $tournament_id
        ORDER BY m.match_datetime ASC
    ");
    ?>
    
    <div class="space-y-4">
        <?php if ($fixtures->num_rows > 0): ?>
            <?php while ($match = $fixtures->fetch_assoc()): ?>
            <div class="bg-dark-card border border-dark-border rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs px-3 py-1 rounded-full 
                        <?php 
                        if ($match['status'] == 'Live') echo 'bg-red-500/20 text-red-500';
                        elseif ($match['status'] == 'Completed') echo 'bg-green-500/20 text-green-500';
                        else echo 'bg-blue-500/20 text-blue-500';
                        ?>">
                        <?php echo htmlspecialchars($match['status']); ?>
                    </span>
                    <span class="text-xs text-gray-400">
                        <i class="fas fa-calendar mr-1"></i>
                        <?php echo date('d M Y, h:i A', strtotime($match['match_datetime'])); ?>
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <!-- Team 1 -->
                    <div class="flex items-center space-x-2">
                        <?php if ($match['team1_logo']): ?>
                        <img src="<?php echo htmlspecialchars($match['team1_logo']); ?>" alt="" class="w-10 h-10 object-contain">
                        <?php endif; ?>
                        <span class="font-semibold"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                    </div>
                    
                    <!-- Team 2 -->
                    <div class="flex items-center space-x-2 justify-end">
                        <span class="font-semibold"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                        <?php if ($match['team2_logo']): ?>
                        <img src="<?php echo htmlspecialchars($match['team2_logo']); ?>" alt="" class="w-10 h-10 object-contain">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="pt-3 border-t border-dark-border flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <?php echo htmlspecialchars($match['venue']); ?>
                    </span>
                    <?php if ($match['status'] == 'Live' || $match['status'] == 'Completed'): ?>
                    <a href="scorecard.php?id=<?php echo $match['id']; ?>" class="text-xs text-blue-500 hover:text-blue-400">
                        View Scorecard <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-dark-card border border-dark-border rounded-lg p-8 text-center">
                <i class="fas fa-calendar-times text-4xl text-gray-600 mb-4"></i>
                <p class="text-gray-400">No fixtures available</p>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($active_tab == 'points'): ?>
    <?php
    $points_table = $conn->query("
        SELECT pt.*, t.name as team_name, t.logo_url as team_logo
        FROM points_table pt
        JOIN teams t ON pt.team_id = t.id
        WHERE pt.tournament_id = $tournament_id
        ORDER BY pt.points DESC, pt.nrr DESC
    ");
    ?>
    
    <div class="bg-dark-card border border-dark-border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-bg">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Position</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Team</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400">Played</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400">Won</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400">Lost</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400">Points</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400">NRR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($points_table->num_rows > 0): ?>
                        <?php $position = 1; ?>
                        <?php while ($team = $points_table->fetch_assoc()): ?>
                        <tr class="border-t border-dark-border hover:bg-dark-hover transition">
                            <td class="px-4 py-3 text-sm font-bold"><?php echo $position++; ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <?php if ($team['team_logo']): ?>
                                    <img src="<?php echo htmlspecialchars($team['team_logo']); ?>" alt="" class="w-6 h-6 object-contain">
                                    <?php endif; ?>
                                    <span class="font-semibold text-sm"><?php echo htmlspecialchars($team['team_name']); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm"><?php echo $team['played']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-green-500"><?php echo $team['won']; ?></td>
                            <td class="px-4 py-3 text-center text-sm text-red-500"><?php echo $team['lost']; ?></td>
                            <td class="px-4 py-3 text-center text-sm font-bold"><?php echo $team['points']; ?></td>
                            <td class="px-4 py-3 text-center text-sm"><?php echo number_format($team['nrr'], 3); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                <i class="fas fa-table text-3xl mb-2 block"></i>
                                No points table data available
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($active_tab == 'stats'): ?>
    <?php
    // Top run scorers
    $top_batsmen = $conn->query("
        SELECT p.name, p.role, t.name as team_name, 
               SUM(bs.runs) as total_runs, 
               SUM(bs.balls) as total_balls,
               SUM(bs.fours) as total_fours,
               SUM(bs.sixes) as total_sixes
        FROM batting_stats bs
        JOIN players p ON bs.player_id = p.id
        JOIN teams t ON p.team_id = t.id
        JOIN matches m ON bs.match_id = m.id
        WHERE m.tournament_id = $tournament_id
        GROUP BY bs.player_id
        ORDER BY total_runs DESC
        LIMIT 10
    ");
    
    // Top wicket takers
    $top_bowlers = $conn->query("
        SELECT p.name, p.role, t.name as team_name,
               SUM(bw.wickets) as total_wickets,
               SUM(bw.overs_bowled) as total_overs,
               SUM(bw.runs_conceded) as total_runs,
               SUM(bw.maidens) as total_maidens
        FROM bowling_stats bw
        JOIN players p ON bw.player_id = p.id
        JOIN teams t ON p.team_id = t.id
        JOIN matches m ON bw.match_id = m.id
        WHERE m.tournament_id = $tournament_id
        GROUP BY bw.player_id
        ORDER BY total_wickets DESC, total_runs ASC
        LIMIT 10
    ");
    ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Run Scorers -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-5">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-baseball-ball text-orange-500 mr-2"></i>
                Top Run Scorers
            </h3>
            <div class="space-y-3">
                <?php if ($top_batsmen->num_rows > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while ($batsman = $top_batsmen->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-dark-bg rounded">
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-bold text-gray-500 w-6">#<?php echo $rank++; ?></span>
                            <div>
                                <div class="font-semibold text-sm"><?php echo htmlspecialchars($batsman['name']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($batsman['team_name']); ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-orange-500"><?php echo $batsman['total_runs']; ?></div>
                            <div class="text-xs text-gray-400">
                                SR: <?php echo calculateStrikeRate($batsman['total_runs'], $batsman['total_balls']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-400 text-sm text-center py-4">No batting statistics available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Top Wicket Takers -->
        <div class="bg-dark-card border border-dark-border rounded-lg p-5">
            <h3 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-bowling-ball text-green-500 mr-2"></i>
                Top Wicket Takers
            </h3>
            <div class="space-y-3">
                <?php if ($top_bowlers->num_rows > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while ($bowler = $top_bowlers->fetch_assoc()): ?>
                    <div class="flex items-center justify-between p-3 bg-dark-bg rounded">
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-bold text-gray-500 w-6">#<?php echo $rank++; ?></span>
                            <div>
                                <div class="font-semibold text-sm"><?php echo htmlspecialchars($bowler['name']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($bowler['team_name']); ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-500"><?php echo $bowler['total_wickets']; ?> Wkts</div>
                            <div class="text-xs text-gray-400">
                                Eco: <?php echo calculateEconomy($bowler['total_runs'], $bowler['total_overs']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-400 text-sm text-center py-4">No bowling statistics available</p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
<?php endif; ?>

<?php include 'common/bottom.php'; ?>
