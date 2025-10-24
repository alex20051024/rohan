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
    if (isset($_POST['add_tournament'])) {
        $name = sanitize($_POST['name']);
        $format = sanitize($_POST['format']);
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        
        $stmt = $conn->prepare("INSERT INTO tournaments (name, format, start_date, end_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $format, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $message = 'Tournament added successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['update_tournament'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $format = sanitize($_POST['format']);
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        
        $stmt = $conn->prepare("UPDATE tournaments SET name = ?, format = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $format, $start_date, $end_date, $id);
        
        if ($stmt->execute()) {
            $message = 'Tournament updated successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['delete_tournament'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM tournaments WHERE id = $id");
        $message = 'Tournament deleted successfully';
        $action = 'list';
    }
}

// Fetch tournament for editing
$edit_tournament = null;
if ($action == 'edit' && $edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_tournament = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manage Tournaments';
include '../common/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">
        <i class="fas fa-trophy text-yellow-500 mr-3"></i>Manage Tournaments
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
    <h2 class="text-xl font-bold mb-6"><?php echo $action == 'edit' ? 'Edit' : 'Add New'; ?> Tournament</h2>
    <form method="POST" action="">
        <?php if ($action == 'edit'): ?>
        <input type="hidden" name="id" value="<?php echo $edit_tournament['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Tournament Name</label>
                <input type="text" name="name" required
                    value="<?php echo $edit_tournament ? htmlspecialchars($edit_tournament['name']) : ''; ?>"
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Format</label>
                <select name="format" required
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
                    <option value="T20" <?php echo ($edit_tournament && $edit_tournament['format'] == 'T20') ? 'selected' : ''; ?>>T20</option>
                    <option value="ODI" <?php echo ($edit_tournament && $edit_tournament['format'] == 'ODI') ? 'selected' : ''; ?>>ODI</option>
                    <option value="Test" <?php echo ($edit_tournament && $edit_tournament['format'] == 'Test') ? 'selected' : ''; ?>>Test</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                <input type="date" name="start_date" required
                    value="<?php echo $edit_tournament ? $edit_tournament['start_date'] : ''; ?>"
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                <input type="date" name="end_date" required
                    value="<?php echo $edit_tournament ? $edit_tournament['end_date'] : ''; ?>"
                    class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
            </div>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" name="<?php echo $action == 'edit' ? 'update_tournament' : 'add_tournament'; ?>"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $action == 'edit' ? 'Update' : 'Add'; ?> Tournament
            </button>
            <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
<!-- Tournaments List -->
<div class="mb-6">
    <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg inline-block transition">
        <i class="fas fa-plus mr-2"></i>Add New Tournament
    </a>
</div>

<div class="bg-dark-card border border-dark-border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-bg">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Tournament Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Format</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Start Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">End Date</th>
                    <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tournaments = $conn->query("SELECT * FROM tournaments ORDER BY start_date DESC");
                if ($tournaments->num_rows > 0):
                    while ($tournament = $tournaments->fetch_assoc()):
                ?>
                <tr class="border-t border-dark-border hover:bg-dark-hover transition">
                    <td class="px-6 py-4 text-sm"><?php echo $tournament['id']; ?></td>
                    <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($tournament['name']); ?></td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-500 rounded-full text-xs">
                            <?php echo htmlspecialchars($tournament['format']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo date('d M Y', strtotime($tournament['start_date'])); ?></td>
                    <td class="px-6 py-4 text-sm"><?php echo date('d M Y', strtotime($tournament['end_date'])); ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="?action=edit&id=<?php echo $tournament['id']; ?>" 
                               class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs transition">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this tournament?');">
                                <input type="hidden" name="id" value="<?php echo $tournament['id']; ?>">
                                <button type="submit" name="delete_tournament" 
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
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                        No tournaments found. Click "Add New Tournament" to create one.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include '../common/bottom.php'; ?>
