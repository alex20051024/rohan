<?php
require_once '../common/config.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$message = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_match'])) {
        $tournament_id = intval($_POST['tournament_id']);
        $team1_id = intval($_POST['team1_id']);
        $team2_id = intval($_POST['team2_id']);
        $venue = sanitize($_POST['venue']);
        $match_datetime = sanitize($_POST['match_datetime']);
        $status = sanitize($_POST['status']);
        
        $stmt = $conn->prepare("INSERT INTO matches (tournament_id, team1_id, team2_id, venue, match_datetime, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $tournament_id, $team1_id, $team2_id, $venue, $match_datetime, $status);
        
        if ($stmt->execute()) {
            $message = 'Match scheduled successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['update_match'])) {
        $id = intval($_POST['id']);
        $tournament_id = intval($_POST['tournament_id']);
        $team1_id = intval($_POST['team1_id']);
        $team2_id = intval($_POST['team2_id']);
        $venue = sanitize($_POST['venue']);
        $match_datetime = sanitize($_POST['match_datetime']);
        $status = sanitize($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE matches SET tournament_id = ?, team1_id = ?, team2_id = ?, venue = ?, match_datetime = ?, status = ? WHERE id = ?");
        $stmt->bind_param("iiisssi", $tournament_id, $team1_id, $team2_id, $venue, $match_datetime, $status, $id);
        
        if ($stmt->execute()) {
            $message = 'Match updated successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['delete_match'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM matches WHERE id = $id");
        $message = 'Match deleted successfully';
        $action = 'list';
    }
}

// Fetch match for editing
$edit_match = null;
if ($action == 'edit' && $edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_match = $stmt->get_result()->fetch_assoc();
}

// Fetch tournaments and teams for dropdowns
$tournaments = $conn->query("SELECT * FROM tournaments ORDER BY start_date DESC");
$teams = $conn->query("SELECT * FROM teams ORDER BY name");

$page_title = 'Manage Matches';
include '../common/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">
        <i class="fas fa-cricket text-orange-500 mr-3"></i>Manage Matches
    </h1>
    <a href="index.php" class="text-blue-500 hover:text-blue-400 text-sm">
        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
    </a>
</div>

<?php if ($message): ?>
<div class="bg-green-500/10 border border-green-500 text-green-500 rounded-lg p-4 mb-6">
    <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
</div>
<?php endif; ?>

<?php if ($action == 'add' || $action == 'edit'): ?>
<!-- Add/Edit Form -->
<div class="bg-dark-card border border-dark-border rounded-lg p-6 mb-6">
    <h2 class="text-xl font-bold mb-6"><?php echo $action == 'edit' ? 'Edit' : 'Schedule New'; ?> Match</h2>
    <form method="POST" action="">
        <?php if ($action == 'edit'): ?>
        <input type="hidden" name="id" value="<?php echo $edit_match['id']; ?>">
        <?php endif; ?>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Tournament</label>
            <select name="tournament_id" required
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                <option value="">Select Tournament</option>
                <?php 
                $tournaments->data_seek(0);
                while ($tournament = $tournaments->fetch_assoc()): 
                ?>
                <option value="<?php echo $tournament['id']; ?>" 
                    <?php echo ($edit_match && $edit_match['tournament_id'] == $tournament['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tournament['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Team 1</label>
                <select name="team1_id" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Select Team 1</option>
                    <?php 
                    $teams->data_seek(0);
                    while ($team = $teams->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $team['id']; ?>" 
                        <?php echo ($edit_match && $edit_match['team1_id'] == $team['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($team['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Team 2</label>
                <select name="team2_id" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="">Select Team 2</option>
                    <?php 
                    $teams->data_seek(0);
                    while ($team = $teams->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $team['id']; ?>" 
                        <?php echo ($edit_match && $edit_match['team2_id'] == $team['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($team['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Venue</label>
            <input type="text" name="venue" required
                value="<?php echo $edit_match ? htmlspecialchars($edit_match['venue']) : ''; ?>"
                placeholder="Stadium Name, City"
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Match Date & Time</label>
                <input type="datetime-local" name="match_datetime" required
                    value="<?php echo $edit_match ? date('Y-m-d\TH:i', strtotime($edit_match['match_datetime'])) : ''; ?>"
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="Scheduled" <?php echo ($edit_match && $edit_match['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="Live" <?php echo ($edit_match && $edit_match['status'] == 'Live') ? 'selected' : ''; ?>>Live</option>
                    <option value="Completed" <?php echo ($edit_match && $edit_match['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" name="<?php echo $action == 'edit' ? 'update_match' : 'add_match'; ?>"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $action == 'edit' ? 'Update' : 'Schedule'; ?> Match
            </button>
            <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
<!-- Matches List -->
<div class="mb-6">
    <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg inline-block transition">
        <i class="fas fa-plus mr-2"></i>Schedule New Match
    </a>
</div>

<div class="bg-dark-card border border-dark-border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Tournament</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Match</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Venue</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $matches = $conn->query("
                    SELECT m.*, 
                           t1.name as team1_name, t2.name as team2_name,
                           tr.name as tournament_name
                    FROM matches m
                    JOIN teams t1 ON m.team1_id = t1.id
                    JOIN teams t2 ON m.team2_id = t2.id
                    JOIN tournaments tr ON m.tournament_id = tr.id
                    ORDER BY m.match_datetime DESC
                ");
                if ($matches->num_rows > 0):
                    while ($match = $matches->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border hover:bg-dark-hover transition">
                    <td class="px-6 py-4 text-sm"><?php echo $match['id']; ?></td>
                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($match['tournament_name']); ?></td>
                    <td class="px-6 py-4 font-semibold">
                        <?php echo htmlspecialchars($match['team1_name']); ?> <span class="text-gray-500">vs</span> <?php echo htmlspecialchars($match['team2_name']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($match['venue']); ?></td>
                    <td class="px-6 py-4 text-sm"><?php echo date('d M Y, h:i A', strtotime($match['match_datetime'])); ?></td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 rounded-full text-xs 
                            <?php 
                            if ($match['status'] == 'Live') echo 'bg-red-500/20 text-red-500';
                            elseif ($match['status'] == 'Completed') echo 'bg-green-500/20 text-green-500';
                            else echo 'bg-blue-500/20 text-blue-500';
                            ?>">
                            <?php echo htmlspecialchars($match['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="?action=edit&id=<?php echo $match['id']; ?>" 
                               class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this match?');">
                                <input type="hidden" name="id" value="<?php echo $match['id']; ?>">
                                <button type="submit" name="delete_match" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No matches found. Click "Schedule New Match" to create one.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include '../common/bottom.php'; ?>
