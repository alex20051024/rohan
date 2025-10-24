<?php
require_once 'common/config.php';

// Check if user is logged in and is a scorer
if (!isLoggedIn() || $_SESSION['user_role'] != 2) {
    redirect('login.php');
}

$match_id = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;

if ($match_id == 0) {
    redirect('index.php');
}

// Fetch match details
$stmt = $conn->prepare("
    SELECT m.*, 
           t1.name as team1_name, t1.logo_url as team1_logo,
           t2.name as team2_name, t2.logo_url as team2_logo
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    WHERE m.id = ?
");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

if (!$match) {
    redirect('index.php');
}

// Get current innings (default to 1)
$current_innings = 1;
$innings_check = $conn->query("SELECT MAX(innings) as max_innings FROM scoring WHERE match_id = $match_id");
if ($innings_check && $innings_check->num_rows > 0) {
    $innings_data = $innings_check->fetch_assoc();
    $current_innings = $innings_data['max_innings'] ?: 1;
}

// Determine batting and bowling teams based on innings
if ($current_innings == 1) {
    $batting_team_id = $match['team1_id'];
    $bowling_team_id = $match['team2_id'];
    $batting_team_name = $match['team1_name'];
    $bowling_team_name = $match['team2_name'];
} else {
    $batting_team_id = $match['team2_id'];
    $bowling_team_id = $match['team1_id'];
    $batting_team_name = $match['team2_name'];
    $bowling_team_name = $match['team1_name'];
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Undo last ball
    if (isset($_POST['undo'])) {
        $last_ball = $conn->query("SELECT * FROM scoring WHERE match_id = $match_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
        if ($last_ball) {
            // Delete the last ball
            $conn->query("DELETE FROM scoring WHERE id = " . $last_ball['id']);
            
            // Update batting stats
            $conn->query("
                UPDATE batting_stats 
                SET runs = runs - {$last_ball['runs_scored']}, 
                    balls = balls - 1,
                    fours = fours - " . ($last_ball['runs_scored'] == 4 ? 1 : 0) . ",
                    sixes = sixes - " . ($last_ball['runs_scored'] == 6 ? 1 : 0) . "
                WHERE match_id = $match_id AND player_id = {$last_ball['batsman_id']} AND innings = {$last_ball['innings']}
            ");
            
            // Update bowling stats
            $legal_ball = !in_array($last_ball['extra_type'], ['Wd', 'Nb']);
            $over_decrement = $legal_ball ? 0.1 : 0;
            $conn->query("
                UPDATE bowling_stats 
                SET overs_bowled = overs_bowled - $over_decrement,
                    runs_conceded = runs_conceded - {$last_ball['runs_scored']} - {$last_ball['extra_runs']},
                    wickets = wickets - " . ($last_ball['wicket_type'] ? 1 : 0) . "
                WHERE match_id = $match_id AND player_id = {$last_ball['bowler_id']} AND innings = {$last_ball['innings']}
            ");
            
            $message = 'Last ball undone successfully';
            $message_type = 'success';
        }
    }
    
    // Record a ball
    elseif (isset($_POST['record_ball'])) {
        $batsman_id = intval($_POST['batsman_id']);
        $bowler_id = intval($_POST['bowler_id']);
        $runs = intval($_POST['runs']);
        $extra_type = isset($_POST['extra_type']) ? sanitize($_POST['extra_type']) : null;
        $extra_runs = $extra_type ? 1 : 0;
        $wicket_type = isset($_POST['wicket_type']) ? sanitize($_POST['wicket_type']) : null;
        $out_player_id = $wicket_type ? $batsman_id : null;
        
        // Get current over and ball number
        $last_ball = $conn->query("
            SELECT over_number, ball_number 
            FROM scoring 
            WHERE match_id = $match_id AND innings = $current_innings
            ORDER BY id DESC LIMIT 1
        ")->fetch_assoc();
        
        $over_number = 0;
        $ball_number = 1;
        
        if ($last_ball) {
            $over_number = $last_ball['over_number'];
            $ball_number = $last_ball['ball_number'];
            
            // Legal ball (not wide or no-ball) increments the ball number
            if (!in_array($extra_type, ['Wd', 'Nb'])) {
                $ball_number++;
                if ($ball_number > 6) {
                    $over_number++;
                    $ball_number = 1;
                }
            }
        }
        
        // Generate commentary
        $commentary = "Over $over_number.$ball_number: ";
        if ($wicket_type) {
            $commentary .= "WICKET! $wicket_type";
        } elseif ($runs == 6) {
            $commentary .= "SIX! What a shot!";
        } elseif ($runs == 4) {
            $commentary .= "FOUR! Beautiful stroke!";
        } else {
            $commentary .= "$runs run" . ($runs != 1 ? 's' : '');
        }
        if ($extra_type) {
            $commentary .= " ($extra_type)";
        }
        
        // Insert ball into scoring table
        $stmt = $conn->prepare("
            INSERT INTO scoring 
            (match_id, innings, over_number, ball_number, batting_team_id, bowling_team_id, 
             batsman_id, bowler_id, runs_scored, extra_runs, extra_type, wicket_type, 
             out_player_id, commentary_text)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiiiiiiiissss", 
            $match_id, $current_innings, $over_number, $ball_number, $batting_team_id, $bowling_team_id,
            $batsman_id, $bowler_id, $runs, $extra_runs, $extra_type, $wicket_type, $out_player_id, $commentary
        );
        
        if ($stmt->execute()) {
            // Update batting stats
            $check_batsman = $conn->query("
                SELECT * FROM batting_stats 
                WHERE match_id = $match_id AND player_id = $batsman_id AND innings = $current_innings
            ");
            
            if ($check_batsman->num_rows > 0) {
                $conn->query("
                    UPDATE batting_stats 
                    SET runs = runs + $runs,
                        balls = balls + 1,
                        fours = fours + " . ($runs == 4 ? 1 : 0) . ",
                        sixes = sixes + " . ($runs == 6 ? 1 : 0) . ",
                        is_out = " . ($wicket_type ? 1 : 0) . ",
                        dismissal_type = " . ($wicket_type ? "'$wicket_type'" : "NULL") . "
                    WHERE match_id = $match_id AND player_id = $batsman_id AND innings = $current_innings
                ");
            } else {
                $conn->query("
                    INSERT INTO batting_stats 
                    (match_id, player_id, innings, runs, balls, fours, sixes, is_out, dismissal_type)
                    VALUES 
                    ($match_id, $batsman_id, $current_innings, $runs, 1, 
                     " . ($runs == 4 ? 1 : 0) . ", " . ($runs == 6 ? 1 : 0) . ", 
                     " . ($wicket_type ? 1 : 0) . ", " . ($wicket_type ? "'$wicket_type'" : "NULL") . ")
                ");
            }
            
            // Update bowling stats
            $check_bowler = $conn->query("
                SELECT * FROM bowling_stats 
                WHERE match_id = $match_id AND player_id = $bowler_id AND innings = $current_innings
            ");
            
            $legal_ball = !in_array($extra_type, ['Wd', 'Nb']);
            $over_increment = $legal_ball ? 0.1 : 0;
            $total_runs = $runs + $extra_runs;
            
            if ($check_bowler->num_rows > 0) {
                $conn->query("
                    UPDATE bowling_stats 
                    SET overs_bowled = overs_bowled + $over_increment,
                        runs_conceded = runs_conceded + $total_runs,
                        wickets = wickets + " . ($wicket_type ? 1 : 0) . "
                    WHERE match_id = $match_id AND player_id = $bowler_id AND innings = $current_innings
                ");
            } else {
                $conn->query("
                    INSERT INTO bowling_stats 
                    (match_id, player_id, innings, overs_bowled, maidens, runs_conceded, wickets)
                    VALUES 
                    ($match_id, $bowler_id, $current_innings, $over_increment, 0, $total_runs, 
                     " . ($wicket_type ? 1 : 0) . ")
                ");
            }
            
            $message = 'Ball recorded successfully';
            $message_type = 'success';
        }
    }
    
    // Change innings
    elseif (isset($_POST['change_innings'])) {
        $current_innings = 2;
        $message = 'Innings changed. Now scoring for ' . $batting_team_name;
        $message_type = 'info';
    }
    
    // End match
    elseif (isset($_POST['end_match'])) {
        $winner_id = intval($_POST['winner_id']);
        $conn->query("UPDATE matches SET status = 'Completed', winner_team_id = $winner_id WHERE id = $match_id");
        $message = 'Match ended successfully';
        $message_type = 'success';
    }
}

// Get current score
$team_score = getTeamScore($match_id, $batting_team_id, $current_innings);

// Fetch players
$batsmen = $conn->query("SELECT * FROM players WHERE team_id = $batting_team_id ORDER BY name");
$bowlers = $conn->query("SELECT * FROM players WHERE team_id = $bowling_team_id ORDER BY name");

$page_title = 'Match Scoring';
include 'common/header.php';
?>

<!-- Scoring Interface -->
<div class="max-w-4xl mx-auto">
    
    <!-- Match Info Header -->
    <div class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
        <div class="text-center">
            <div class="text-xs text-gray-400 mb-2">INNINGS <?php echo $current_innings; ?></div>
            <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($batting_team_name); ?></h2>
            <div class="text-4xl font-bold text-blue-500 mb-2">
                <?php echo $team_score['total_runs']; ?>/<?php echo $team_score['wickets']; ?>
            </div>
            <div class="text-sm text-gray-400">
                (<?php echo formatOvers($team_score['total_balls']); ?> overs)
            </div>
            <div class="text-xs text-gray-500 mt-2">vs <?php echo htmlspecialchars($bowling_team_name); ?></div>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php 
        echo $message_type == 'success' ? 'bg-green-500/10 border border-green-500 text-green-500' : 
             ($message_type == 'error' ? 'bg-red-500/10 border border-red-500 text-red-500' : 
              'bg-blue-500/10 border border-blue-500 text-blue-500');
    ?>">
        <i class="fas fa-info-circle mr-2"></i><?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <!-- Scoring Form -->
    <form method="POST" class="bg-dark-card border border-dark-border rounded-lg p-5 mb-6">
        <input type="hidden" name="record_ball" value="1">
        
        <!-- Player Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-baseball-ball mr-2"></i>Batsman
                </label>
                <select name="batsman_id" required 
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Select Batsman</option>
                    <?php while ($batsman = $batsmen->fetch_assoc()): ?>
                    <option value="<?php echo $batsman['id']; ?>">
                        <?php echo htmlspecialchars($batsman['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-bowling-ball mr-2"></i>Bowler
                </label>
                <select name="bowler_id" required 
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Select Bowler</option>
                    <?php while ($bowler = $bowlers->fetch_assoc()): ?>
                    <option value="<?php echo $bowler['id']; ?>">
                        <?php echo htmlspecialchars($bowler['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <!-- Runs Buttons -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-3">
                <i class="fas fa-running mr-2"></i>Runs
            </label>
            <div class="grid grid-cols-4 gap-3">
                <button type="button" onclick="selectRuns(0)" 
                    class="runs-btn bg-dark-bg hover:bg-dark-hover border-2 border-dark-border rounded-lg py-4 text-xl font-bold transition">
                    0
                </button>
                <button type="button" onclick="selectRuns(1)" 
                    class="runs-btn bg-dark-bg hover:bg-dark-hover border-2 border-dark-border rounded-lg py-4 text-xl font-bold transition">
                    1
                </button>
                <button type="button" onclick="selectRuns(2)" 
                    class="runs-btn bg-dark-bg hover:bg-dark-hover border-2 border-dark-border rounded-lg py-4 text-xl font-bold transition">
                    2
                </button>
                <button type="button" onclick="selectRuns(3)" 
                    class="runs-btn bg-dark-bg hover:bg-dark-hover border-2 border-dark-border rounded-lg py-4 text-xl font-bold transition">
                    3
                </button>
                <button type="button" onclick="selectRuns(4)" 
                    class="runs-btn bg-blue-600 hover:bg-blue-700 border-2 border-blue-600 rounded-lg py-4 text-xl font-bold transition">
                    4
                </button>
                <button type="button" onclick="selectRuns(6)" 
                    class="runs-btn bg-purple-600 hover:bg-purple-700 border-2 border-purple-600 rounded-lg py-4 text-xl font-bold transition">
                    6
                </button>
            </div>
            <input type="hidden" name="runs" id="runs_input" value="0">
        </div>
        
        <!-- Extras & Wicket -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Extra Type (Optional)</label>
                <select name="extra_type" 
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">No Extra</option>
                    <option value="Wd">Wide</option>
                    <option value="Nb">No Ball</option>
                    <option value="Lb">Leg Bye</option>
                    <option value="B">Bye</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Wicket Type (Optional)</label>
                <select name="wicket_type" 
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">No Wicket</option>
                    <option value="Bowled">Bowled</option>
                    <option value="Caught">Caught</option>
                    <option value="LBW">LBW</option>
                    <option value="Run Out">Run Out</option>
                    <option value="Stumped">Stumped</option>
                    <option value="Hit Wicket">Hit Wicket</option>
                </select>
            </div>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" 
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg transition">
            <i class="fas fa-check mr-2"></i>Record Ball
        </button>
    </form>
    
    <!-- Action Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <form method="POST" class="contents">
            <input type="hidden" name="undo" value="1">
            <button type="submit" 
                class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-undo mr-2"></i>Undo
            </button>
        </form>
        
        <?php if ($current_innings == 1): ?>
        <form method="POST" class="contents">
            <input type="hidden" name="change_innings" value="1">
            <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-exchange-alt mr-2"></i>Change Innings
            </button>
        </form>
        <?php endif; ?>
        
        <a href="scorecard.php?id=<?php echo $match_id; ?>" 
           class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition text-center">
            <i class="fas fa-file-alt mr-2"></i>Scorecard
        </a>
        
        <button type="button" onclick="document.getElementById('endMatchModal').classList.remove('hidden')" 
            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition">
            <i class="fas fa-stop mr-2"></i>End Match
        </button>
    </div>
    
    <!-- Recent Balls -->
    <div class="bg-dark-card border border-dark-border rounded-lg p-5">
        <h3 class="text-lg font-bold mb-4">Recent Balls</h3>
        <?php
        $recent_balls = $conn->query("
            SELECT s.*, 
                   b1.name as batsman_name,
                   b2.name as bowler_name
            FROM scoring s
            JOIN players b1 ON s.batsman_id = b1.id
            JOIN players b2 ON s.bowler_id = b2.id
            WHERE s.match_id = $match_id AND s.innings = $current_innings
            ORDER BY s.id DESC
            LIMIT 10
        ");
        
        if ($recent_balls->num_rows > 0):
        ?>
        <div class="space-y-2">
            <?php while ($ball = $recent_balls->fetch_assoc()): ?>
            <div class="flex items-center justify-between p-3 bg-dark-bg rounded text-sm">
                <span class="text-gray-400"><?php echo $ball['over_number']; ?>.<?php echo $ball['ball_number']; ?></span>
                <span><?php echo htmlspecialchars($ball['bowler_name']); ?> to <?php echo htmlspecialchars($ball['batsman_name']); ?></span>
                <span class="font-bold">
                    <?php if ($ball['wicket_type']): ?>
                    <span class="text-red-500">W</span>
                    <?php elseif ($ball['runs_scored'] == 6): ?>
                    <span class="text-purple-500">6</span>
                    <?php elseif ($ball['runs_scored'] == 4): ?>
                    <span class="text-blue-500">4</span>
                    <?php else: ?>
                    <?php echo $ball['runs_scored']; ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-400 text-center py-4">No balls recorded yet</p>
        <?php endif; ?>
    </div>
</div>

<!-- End Match Modal -->
<div id="endMatchModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
    <div class="bg-dark-card border border-dark-border rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">End Match</h3>
        <form method="POST">
            <input type="hidden" name="end_match" value="1">
            <label class="block text-sm font-medium text-gray-300 mb-2">Select Winner</label>
            <select name="winner_id" required 
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500 mb-4">
                <option value="">Select Winner</option>
                <option value="<?php echo $match['team1_id']; ?>"><?php echo htmlspecialchars($match['team1_name']); ?></option>
                <option value="<?php echo $match['team2_id']; ?>"><?php echo htmlspecialchars($match['team2_name']); ?></option>
            </select>
            <div class="flex space-x-3">
                <button type="button" onclick="document.getElementById('endMatchModal').classList.add('hidden')"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" 
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition">
                    End Match
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectRuns(runs) {
    document.getElementById('runs_input').value = runs;
    
    // Visual feedback
    const buttons = document.querySelectorAll('.runs-btn');
    buttons.forEach(btn => {
        btn.classList.remove('ring-4', 'ring-green-500');
    });
    event.target.classList.add('ring-4', 'ring-green-500');
}
</script>

<?php include 'common/bottom.php'; ?>
