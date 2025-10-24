<?php
require_once 'common/config.php';

$match_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($match_id == 0) {
    redirect('index.php');
}

// Fetch match details
$stmt = $conn->prepare("
    SELECT m.*, 
           t1.name as team1_name, t1.logo_url as team1_logo,
           t2.name as team2_name, t2.logo_url as team2_logo,
           tr.name as tournament_name,
           tw.name as winner_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    LEFT JOIN teams tw ON m.winner_team_id = tw.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

if (!$match) {
    redirect('index.php');
}

$page_title = $match['team1_name'] . ' vs ' . $match['team2_name'];
include 'common/header.php';

// Get team scores
$team1_score = getTeamScore($match_id, $match['team1_id'], 1);
$team2_score = getTeamScore($match_id, $match['team2_id'], 2);
?>

<!-- Match Header -->
<div class="bg-dark-card border border-dark-border rounded-lg p-6 mb-6">
    <div class="text-center mb-4">
        <div class="text-xs text-gray-400 mb-2"><?php echo htmlspecialchars($match['tournament_name']); ?></div>
        <div class="flex items-center justify-center space-x-2 mb-2">
            <span class="text-xs px-3 py-1 rounded-full 
                <?php 
                if ($match['status'] == 'Live') echo 'bg-red-500/20 text-red-500 animate-pulse';
                elseif ($match['status'] == 'Completed') echo 'bg-green-500/20 text-green-500';
                else echo 'bg-blue-500/20 text-blue-500';
                ?>">
                <?php echo htmlspecialchars($match['status']); ?>
            </span>
        </div>
        <div class="text-xs text-gray-400">
            <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($match['venue']); ?>
        </div>
        <div class="text-xs text-gray-400">
            <i class="fas fa-calendar mr-1"></i><?php echo date('d M Y, h:i A', strtotime($match['match_datetime'])); ?>
        </div>
    </div>
    
    <!-- Score Display -->
    <div class="grid grid-cols-2 gap-4 mt-6">
        <!-- Team 1 -->
        <div class="text-center p-4 bg-dark-bg rounded-lg">
            <?php if ($match['team1_logo']): ?>
            <img src="<?php echo htmlspecialchars($match['team1_logo']); ?>" alt="" class="w-16 h-16 object-contain mx-auto mb-2">
            <?php endif; ?>
            <div class="font-bold mb-1"><?php echo htmlspecialchars($match['team1_name']); ?></div>
            <div class="text-2xl font-bold text-blue-500">
                <?php echo $team1_score['total_runs']; ?>/<?php echo $team1_score['wickets']; ?>
            </div>
            <div class="text-xs text-gray-400">(<?php echo formatOvers($team1_score['total_balls']); ?> overs)</div>
        </div>
        
        <!-- Team 2 -->
        <div class="text-center p-4 bg-dark-bg rounded-lg">
            <?php if ($match['team2_logo']): ?>
            <img src="<?php echo htmlspecialchars($match['team2_logo']); ?>" alt="" class="w-16 h-16 object-contain mx-auto mb-2">
            <?php endif; ?>
            <div class="font-bold mb-1"><?php echo htmlspecialchars($match['team2_name']); ?></div>
            <div class="text-2xl font-bold text-green-500">
                <?php echo $team2_score['total_runs']; ?>/<?php echo $team2_score['wickets']; ?>
            </div>
            <div class="text-xs text-gray-400">(<?php echo formatOvers($team2_score['total_balls']); ?> overs)</div>
        </div>
    </div>
    
    <!-- Match Result -->
    <?php if ($match['status'] == 'Completed' && $match['winner_team_id']): ?>
    <div class="mt-4 text-center p-3 bg-green-500/10 border border-green-500 rounded-lg">
        <i class="fas fa-trophy text-yellow-500 mr-2"></i>
        <span class="font-semibold text-green-500"><?php echo htmlspecialchars($match['winner_name']); ?> won the match</span>
    </div>
    <?php endif; ?>
</div>

<!-- Batting Scorecard - Team 1 -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-baseball-ball text-blue-500 mr-2"></i>
        <?php echo htmlspecialchars($match['team1_name']); ?> - Batting
    </h3>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-3 py-2 text-left">Batsman</th>
                    <th class="px-3 py-2 text-center">R</th>
                    <th class="px-3 py-2 text-center">B</th>
                    <th class="px-3 py-2 text-center">4s</th>
                    <th class="px-3 py-2 text-center">6s</th>
                    <th class="px-3 py-2 text-center">SR</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $batting_stmt = $conn->prepare("
                    SELECT bs.*, p.name as player_name 
                    FROM batting_stats bs
                    JOIN players p ON bs.player_id = p.id
                    WHERE bs.match_id = ? AND p.team_id = ? AND bs.innings = 1
                    ORDER BY bs.runs DESC
                ");
                $batting_stmt->bind_param("ii", $match_id, $match['team1_id']);
                $batting_stmt->execute();
                $batting_result = $batting_stmt->get_result();
                
                if ($batting_result->num_rows > 0):
                    while ($batsman = $batting_result->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border">
                    <td class="px-3 py-2">
                        <?php echo htmlspecialchars($batsman['player_name']); ?>
                        <?php if ($batsman['is_out']): ?>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($batsman['dismissal_type']); ?></div>
                        <?php else: ?>
                        <div class="text-xs text-green-500">Not Out</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-center font-bold"><?php echo $batsman['runs']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['balls']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['fours']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['sixes']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo calculateStrikeRate($batsman['runs'], $batsman['balls']); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-400">No batting data available</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bowling Scorecard - Team 2 -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-bowling-ball text-green-500 mr-2"></i>
        <?php echo htmlspecialchars($match['team2_name']); ?> - Bowling
    </h3>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-3 py-2 text-left">Bowler</th>
                    <th class="px-3 py-2 text-center">O</th>
                    <th class="px-3 py-2 text-center">M</th>
                    <th class="px-3 py-2 text-center">R</th>
                    <th class="px-3 py-2 text-center">W</th>
                    <th class="px-3 py-2 text-center">Eco</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bowling_stmt = $conn->prepare("
                    SELECT bw.*, p.name as player_name 
                    FROM bowling_stats bw
                    JOIN players p ON bw.player_id = p.id
                    WHERE bw.match_id = ? AND p.team_id = ? AND bw.innings = 1
                    ORDER BY bw.wickets DESC, bw.runs_conceded ASC
                ");
                $bowling_stmt->bind_param("ii", $match_id, $match['team2_id']);
                $bowling_stmt->execute();
                $bowling_result = $bowling_stmt->get_result();
                
                if ($bowling_result->num_rows > 0):
                    while ($bowler = $bowling_result->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border">
                    <td class="px-3 py-2"><?php echo htmlspecialchars($bowler['player_name']); ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['overs_bowled']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['maidens']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['runs_conceded']; ?></td>
                    <td class="px-3 py-2 text-center font-bold"><?php echo $bowler['wickets']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo calculateEconomy($bowler['runs_conceded'], $bowler['overs_bowled']); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-400">No bowling data available</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Batting Scorecard - Team 2 -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-baseball-ball text-green-500 mr-2"></i>
        <?php echo htmlspecialchars($match['team2_name']); ?> - Batting
    </h3>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-3 py-2 text-left">Batsman</th>
                    <th class="px-3 py-2 text-center">R</th>
                    <th class="px-3 py-2 text-center">B</th>
                    <th class="px-3 py-2 text-center">4s</th>
                    <th class="px-3 py-2 text-center">6s</th>
                    <th class="px-3 py-2 text-center">SR</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $batting_stmt->bind_param("ii", $match_id, $match['team2_id']);
                $batting_stmt->execute();
                $batting_result = $batting_stmt->get_result();
                
                if ($batting_result->num_rows > 0):
                    while ($batsman = $batting_result->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border">
                    <td class="px-3 py-2">
                        <?php echo htmlspecialchars($batsman['player_name']); ?>
                        <?php if ($batsman['is_out']): ?>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($batsman['dismissal_type']); ?></div>
                        <?php else: ?>
                        <div class="text-xs text-green-500">Not Out</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 text-center font-bold"><?php echo $batsman['runs']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['balls']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['fours']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $batsman['sixes']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo calculateStrikeRate($batsman['runs'], $batsman['balls']); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-400">No batting data available</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bowling Scorecard - Team 1 -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-bowling-ball text-blue-500 mr-2"></i>
        <?php echo htmlspecialchars($match['team1_name']); ?> - Bowling
    </h3>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-3 py-2 text-left">Bowler</th>
                    <th class="px-3 py-2 text-center">O</th>
                    <th class="px-3 py-2 text-center">M</th>
                    <th class="px-3 py-2 text-center">R</th>
                    <th class="px-3 py-2 text-center">W</th>
                    <th class="px-3 py-2 text-center">Eco</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bowling_stmt->bind_param("ii", $match_id, $match['team1_id']);
                $bowling_stmt->execute();
                $bowling_result = $bowling_stmt->get_result();
                
                if ($bowling_result->num_rows > 0):
                    while ($bowler = $bowling_result->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border">
                    <td class="px-3 py-2"><?php echo htmlspecialchars($bowler['player_name']); ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['overs_bowled']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['maidens']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo $bowler['runs_conceded']; ?></td>
                    <td class="px-3 py-2 text-center font-bold"><?php echo $bowler['wickets']; ?></td>
                    <td class="px-3 py-2 text-center"><?php echo calculateEconomy($bowler['runs_conceded'], $bowler['overs_bowled']); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-400">No bowling data available</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Live Commentary -->
<div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
    <h3 class="text-lg font-bold mb-4 flex items-center">
        <i class="fas fa-comment-dots text-purple-500 mr-2"></i>
        Live Commentary
    </h3>
    
    <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php
        $commentary = $conn->query("
            SELECT s.*, 
                   b1.name as batsman_name,
                   b2.name as bowler_name
            FROM scoring s
            JOIN players b1 ON s.batsman_id = b1.id
            JOIN players b2 ON s.bowler_id = b2.id
            WHERE s.match_id = $match_id
            ORDER BY s.id DESC
            LIMIT 50
        ");
        
        if ($commentary->num_rows > 0):
            while ($ball = $commentary->fetch_assoc()):
        ?>
        <div class="p-3 bg-dark-bg rounded">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold text-gray-400">
                    <?php echo $ball['over_number']; ?>.<?php echo $ball['ball_number']; ?>
                </span>
                <span class="text-xs text-gray-400">
                    <?php echo date('h:i A', strtotime($ball['created_at'])); ?>
                </span>
            </div>
            <div class="text-sm">
                <span class="font-semibold"><?php echo htmlspecialchars($ball['bowler_name']); ?></span> to 
                <span class="font-semibold"><?php echo htmlspecialchars($ball['batsman_name']); ?></span>
            </div>
            <?php if ($ball['commentary_text']): ?>
            <div class="text-sm text-gray-300 mt-1"><?php echo htmlspecialchars($ball['commentary_text']); ?></div>
            <?php endif; ?>
            <div class="mt-2">
                <?php if ($ball['wicket_type']): ?>
                <span class="inline-block px-2 py-1 bg-red-500/20 text-red-500 rounded text-xs font-bold">
                    <i class="fas fa-times mr-1"></i>WICKET
                </span>
                <?php elseif ($ball['runs_scored'] == 6): ?>
                <span class="inline-block px-2 py-1 bg-purple-500/20 text-purple-500 rounded text-xs font-bold">
                    <i class="fas fa-star mr-1"></i>SIX
                </span>
                <?php elseif ($ball['runs_scored'] == 4): ?>
                <span class="inline-block px-2 py-1 bg-blue-500/20 text-blue-500 rounded text-xs font-bold">
                    <i class="fas fa-fire mr-1"></i>FOUR
                </span>
                <?php else: ?>
                <span class="inline-block px-2 py-1 bg-dark-border text-gray-300 rounded text-xs">
                    <?php echo $ball['runs_scored']; ?> run<?php echo $ball['runs_scored'] != 1 ? 's' : ''; ?>
                </span>
                <?php endif; ?>
                
                <?php if ($ball['extra_runs'] > 0): ?>
                <span class="inline-block px-2 py-1 bg-yellow-500/20 text-yellow-500 rounded text-xs ml-1">
                    <?php echo $ball['extra_type']; ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <p class="text-gray-400 text-center py-4">No commentary available</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
