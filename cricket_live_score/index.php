<?php
require_once 'common/config.php';

$page_title = 'Home';
include 'common/header.php';

// Fetch live matches
$live_matches = $conn->query("
    SELECT m.*, 
           t1.name as team1_name, t1.logo_url as team1_logo,
           t2.name as team2_name, t2.logo_url as team2_logo,
           tr.name as tournament_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    WHERE m.status = 'Live'
    ORDER BY m.match_datetime DESC
");

// Fetch upcoming matches
$upcoming_matches = $conn->query("
    SELECT m.*, 
           t1.name as team1_name, t1.logo_url as team1_logo,
           t2.name as team2_name, t2.logo_url as team2_logo,
           tr.name as tournament_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    JOIN tournaments tr ON m.tournament_id = tr.id
    WHERE m.status = 'Scheduled' AND m.match_datetime >= NOW()
    ORDER BY m.match_datetime ASC
    LIMIT 6
");

// Fetch tournaments
$tournaments = $conn->query("
    SELECT * FROM tournaments 
    WHERE end_date >= CURDATE()
    ORDER BY start_date ASC
    LIMIT 6
");

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 0;
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-8 mb-8 text-center">
    <h1 class="text-3xl md:text-4xl font-bold mb-4">
        <i class="fas fa-cricket mr-3"></i>Cricket Live Score
    </h1>
    <p class="text-lg text-blue-100">Track live cricket scores, tournaments, and player stats</p>
</div>

<!-- Live Matches Section -->
<section id="live" class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <i class="fas fa-circle-dot text-red-500 mr-3 animate-pulse"></i>
            Live Matches
        </h2>
    </div>
    
    <?php if ($live_matches->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php while ($match = $live_matches->fetch_assoc()): ?>
        <?php 
            $team1_score = getTeamScore($match['id'], $match['team1_id']);
            $team2_score = getTeamScore($match['id'], $match['team2_id']);
        ?>
        <a href="scorecard.php?id=<?php echo $match['id']; ?>" class="bg-dark-card border border-dark-border hover:border-blue-500 rounded-lg p-5 transition block">
            
            <!-- Tournament Name -->
            <div class="text-xs text-gray-400 mb-3"><?php echo htmlspecialchars($match['tournament_name']); ?></div>
            
            <!-- Team 1 -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <?php if ($match['team1_logo']): ?>
                    <img src="<?php echo htmlspecialchars($match['team1_logo']); ?>" alt="" class="w-10 h-10 object-contain">
                    <?php else: ?>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full"></div>
                    <?php endif; ?>
                    <span class="font-semibold"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                </div>
                <div class="text-xl font-bold">
                    <?php echo $team1_score['total_runs']; ?>/<?php echo $team1_score['wickets']; ?>
                    <span class="text-sm text-gray-400 ml-2">(<?php echo formatOvers($team1_score['total_balls']); ?>)</span>
                </div>
            </div>
            
            <!-- Team 2 -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <?php if ($match['team2_logo']): ?>
                    <img src="<?php echo htmlspecialchars($match['team2_logo']); ?>" alt="" class="w-10 h-10 object-contain">
                    <?php else: ?>
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full"></div>
                    <?php endif; ?>
                    <span class="font-semibold"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                </div>
                <div class="text-xl font-bold">
                    <?php echo $team2_score['total_runs']; ?>/<?php echo $team2_score['wickets']; ?>
                    <span class="text-sm text-gray-400 ml-2">(<?php echo formatOvers($team2_score['total_balls']); ?>)</span>
                </div>
            </div>
            
            <!-- Status -->
            <div class="flex items-center justify-between pt-3 border-t border-dark-border">
                <span class="text-xs text-red-500 font-semibold">
                    <i class="fas fa-circle text-xs mr-1"></i>LIVE
                </span>
                <span class="text-xs text-gray-400"><?php echo htmlspecialchars($match['venue']); ?></span>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 text-center">
        <i class="fas fa-cricket text-4xl text-gray-600 mb-4"></i>
        <p class="text-gray-400">No live matches at the moment</p>
    </div>
    <?php endif; ?>
</section>

<!-- Upcoming Matches Section -->
<section class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-3"></i>
            Upcoming Matches
        </h2>
    </div>
    
    <?php if ($upcoming_matches->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php while ($match = $upcoming_matches->fetch_assoc()): ?>
        <div class="bg-dark-card border border-dark-border rounded-lg p-5">
            
            <!-- Tournament Name -->
            <div class="text-xs text-gray-400 mb-3"><?php echo htmlspecialchars($match['tournament_name']); ?></div>
            
            <!-- Teams -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <?php if ($match['team1_logo']): ?>
                    <img src="<?php echo htmlspecialchars($match['team1_logo']); ?>" alt="" class="w-8 h-8 object-contain">
                    <?php endif; ?>
                    <span class="font-semibold text-sm"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                </div>
            </div>
            
            <div class="text-center text-gray-500 text-xs my-2">VS</div>
            
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <?php if ($match['team2_logo']): ?>
                    <img src="<?php echo htmlspecialchars($match['team2_logo']); ?>" alt="" class="w-8 h-8 object-contain">
                    <?php endif; ?>
                    <span class="font-semibold text-sm"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                </div>
            </div>
            
            <!-- Date & Venue -->
            <div class="pt-3 border-t border-dark-border space-y-1">
                <div class="text-xs text-gray-400">
                    <i class="fas fa-calendar mr-1"></i>
                    <?php echo date('d M Y, h:i A', strtotime($match['match_datetime'])); ?>
                </div>
                <div class="text-xs text-gray-400">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    <?php echo htmlspecialchars($match['venue']); ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 text-center">
        <i class="fas fa-calendar-times text-4xl text-gray-600 mb-4"></i>
        <p class="text-gray-400">No upcoming matches scheduled</p>
    </div>
    <?php endif; ?>
</section>

<!-- Tournaments Section -->
<section id="tournaments" class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold flex items-center">
            <i class="fas fa-trophy text-yellow-500 mr-3"></i>
            Tournaments
        </h2>
    </div>
    
    <?php if ($tournaments->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php while ($tournament = $tournaments->fetch_assoc()): ?>
        <a href="tournament_details.php?id=<?php echo $tournament['id']; ?>" class="bg-dark-card border border-dark-border hover:border-yellow-500 rounded-lg p-5 transition block">
            <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full mx-auto mb-4">
                <i class="fas fa-trophy text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-center mb-2"><?php echo htmlspecialchars($tournament['name']); ?></h3>
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-400">
                <span><i class="fas fa-flag mr-1"></i><?php echo htmlspecialchars($tournament['format']); ?></span>
                <span><i class="fas fa-calendar mr-1"></i><?php echo date('M Y', strtotime($tournament['start_date'])); ?></span>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="bg-dark-card border border-dark-border rounded-lg p-8 text-center">
        <i class="fas fa-trophy text-4xl text-gray-600 mb-4"></i>
        <p class="text-gray-400">No active tournaments</p>
    </div>
    <?php endif; ?>
</section>

<!-- Scorer Quick Access (Only for Scorer role) -->
<?php if ($user_role == 2): ?>
<section class="mb-8">
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-lg p-6 text-center">
        <h3 class="text-xl font-bold mb-2">
            <i class="fas fa-pencil-alt mr-2"></i>Scorer Panel
        </h3>
        <p class="text-sm text-blue-100 mb-4">You have scorer access. Start scoring live matches.</p>
        <?php
        // Get live matches for scoring
        $scorer_matches = $conn->query("
            SELECT m.id, t1.name as team1, t2.name as team2
            FROM matches m
            JOIN teams t1 ON m.team1_id = t1.id
            JOIN teams t2 ON m.team2_id = t2.id
            WHERE m.status = 'Live'
            LIMIT 3
        ");
        
        if ($scorer_matches->num_rows > 0):
        ?>
        <div class="space-y-2">
            <?php while ($sm = $scorer_matches->fetch_assoc()): ?>
            <a href="match_scoring.php?match_id=<?php echo $sm['id']; ?>" 
               class="block bg-white/10 hover:bg-white/20 rounded px-4 py-2 text-sm transition">
                Score: <?php echo htmlspecialchars($sm['team1']); ?> vs <?php echo htmlspecialchars($sm['team2']); ?>
            </a>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p class="text-sm text-gray-300">No live matches to score</p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php include 'common/bottom.php'; ?>
