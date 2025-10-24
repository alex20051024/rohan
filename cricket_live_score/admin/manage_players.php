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
    if (isset($_POST['add_player'])) {
        $name = sanitize($_POST['name']);
        $team_id = intval($_POST['team_id']);
        $role = sanitize($_POST['role']);
        
        $stmt = $conn->prepare("INSERT INTO players (name, team_id, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $team_id, $role);
        
        if ($stmt->execute()) {
            $message = 'Player added successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['update_player'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $team_id = intval($_POST['team_id']);
        $role = sanitize($_POST['role']);
        
        $stmt = $conn->prepare("UPDATE players SET name = ?, team_id = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $team_id, $role, $id);
        
        if ($stmt->execute()) {
            $message = 'Player updated successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['delete_player'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM players WHERE id = $id");
        $message = 'Player deleted successfully';
        $action = 'list';
    }
}

// Fetch player for editing
$edit_player = null;
if ($action == 'edit' && $edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_player = $stmt->get_result()->fetch_assoc();
}

// Fetch all teams for dropdown
$teams = $conn->query("SELECT * FROM teams ORDER BY name");

$page_title = 'Manage Players';
include '../common/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">
        <i class="fas fa-users text-purple-500 mr-3"></i>Manage Players
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
    <h2 class="text-xl font-bold mb-6"><?php echo $action == 'edit' ? 'Edit' : 'Add New'; ?> Player</h2>
    <form method="POST" action="">
        <?php if ($action == 'edit'): ?>
        <input type="hidden" name="id" value="<?php echo $edit_player['id']; ?>">
        <?php endif; ?>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Player Name</label>
            <input type="text" name="name" required
                value="<?php echo $edit_player ? htmlspecialchars($edit_player['name']) : ''; ?>"
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Team</label>
            <select name="team_id" required
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                <option value="">Select Team</option>
                <?php while ($team = $teams->fetch_assoc()): ?>
                <option value="<?php echo $team['id']; ?>" 
                    <?php echo ($edit_player && $edit_player['team_id'] == $team['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($team['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Role</label>
            <select name="role" required
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                <option value="Batsman" <?php echo ($edit_player && $edit_player['role'] == 'Batsman') ? 'selected' : ''; ?>>Batsman</option>
                <option value="Bowler" <?php echo ($edit_player && $edit_player['role'] == 'Bowler') ? 'selected' : ''; ?>>Bowler</option>
                <option value="All-rounder" <?php echo ($edit_player && $edit_player['role'] == 'All-rounder') ? 'selected' : ''; ?>>All-rounder</option>
                <option value="Wicket-keeper" <?php echo ($edit_player && $edit_player['role'] == 'Wicket-keeper') ? 'selected' : ''; ?>>Wicket-keeper</option>
            </select>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" name="<?php echo $action == 'edit' ? 'update_player' : 'add_player'; ?>"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $action == 'edit' ? 'Update' : 'Add'; ?> Player
            </button>
            <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
<!-- Players List -->
<div class="mb-6">
    <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg inline-block transition">
        <i class="fas fa-plus mr-2"></i>Add New Player
    </a>
</div>

<div class="bg-dark-card border border-dark-border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Player Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Team</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Role</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $players = $conn->query("
                    SELECT p.*, t.name as team_name 
                    FROM players p 
                    JOIN teams t ON p.team_id = t.id 
                    ORDER BY t.name, p.name
                ");
                if ($players->num_rows > 0):
                    while ($player = $players->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border hover:bg-dark-hover transition">
                    <td class="px-6 py-4 text-sm"><?php echo $player['id']; ?></td>
                    <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($player['name']); ?></td>
                    <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($player['team_name']); ?></td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-500 rounded-full text-xs">
                            <?php echo htmlspecialchars($player['role']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="?action=edit&id=<?php echo $player['id']; ?>" 
                               class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this player?');">
                                <input type="hidden" name="id" value="<?php echo $player['id']; ?>">
                                <button type="submit" name="delete_player" 
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
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                        No players found. Click "Add New Player" to create one.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include '../common/bottom.php'; ?>
