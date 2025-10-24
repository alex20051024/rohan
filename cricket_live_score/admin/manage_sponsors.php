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
    if (isset($_POST['add_sponsor'])) {
        $name = sanitize($_POST['name']);
        $logo_url = sanitize($_POST['logo_url']);
        
        $stmt = $conn->prepare("INSERT INTO sponsors (name, logo_url) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $logo_url);
        
        if ($stmt->execute()) {
            $message = 'Sponsor added successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['update_sponsor'])) {
        $id = intval($_POST['id']);
        $name = sanitize($_POST['name']);
        $logo_url = sanitize($_POST['logo_url']);
        
        $stmt = $conn->prepare("UPDATE sponsors SET name = ?, logo_url = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $logo_url, $id);
        
        if ($stmt->execute()) {
            $message = 'Sponsor updated successfully';
            $action = 'list';
        }
    }
    elseif (isset($_POST['delete_sponsor'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM sponsors WHERE id = $id");
        $message = 'Sponsor deleted successfully';
        $action = 'list';
    }
}

// Fetch sponsor for editing
$edit_sponsor = null;
if ($action == 'edit' && $edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM sponsors WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_sponsor = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manage Sponsors';
include '../common/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-2">
        <i class="fas fa-handshake text-yellow-500 mr-3"></i>Manage Sponsors
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
    <h2 class="text-xl font-bold mb-6"><?php echo $action == 'edit' ? 'Edit' : 'Add New'; ?> Sponsor</h2>
    <form method="POST" action="">
        <?php if ($action == 'edit'): ?>
        <input type="hidden" name="id" value="<?php echo $edit_sponsor['id']; ?>">
        <?php endif; ?>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Sponsor Name</label>
            <input type="text" name="name" required
                value="<?php echo $edit_sponsor ? htmlspecialchars($edit_sponsor['name']) : ''; ?>"
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Logo URL (Optional)</label>
            <input type="url" name="logo_url"
                value="<?php echo $edit_sponsor ? htmlspecialchars($edit_sponsor['logo_url']) : ''; ?>"
                placeholder="https://example.com/sponsor-logo.png"
                class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-blue-500">
            <p class="text-xs text-gray-400 mt-2">Enter the full URL of the sponsor logo image</p>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" name="<?php echo $action == 'edit' ? 'update_sponsor' : 'add_sponsor'; ?>"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $action == 'edit' ? 'Update' : 'Add'; ?> Sponsor
            </button>
            <a href="?action=list" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($action == 'list'): ?>
<!-- Sponsors List -->
<div class="mb-6">
    <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg inline-block transition">
        <i class="fas fa-plus mr-2"></i>Add New Sponsor
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php
    $sponsors = $conn->query("SELECT * FROM sponsors ORDER BY name");
    if ($sponsors->num_rows > 0):
        while ($sponsor = $sponsors->fetch_assoc()):
    ?>
    <div class="bg-dark-card border border-dark-border rounded-lg p-6">
        <div class="text-center mb-4">
            <?php if ($sponsor['logo_url']): ?>
            <img src="<?php echo htmlspecialchars($sponsor['logo_url']); ?>" alt="" class="h-16 object-contain mx-auto mb-3">
            <?php else: ?>
            <div class="h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-handshake text-3xl"></i>
            </div>
            <?php endif; ?>
            <h3 class="text-lg font-bold"><?php echo htmlspecialchars($sponsor['name']); ?></h3>
        </div>
        <div class="flex space-x-2">
            <a href="?action=edit&id=<?php echo $sponsor['id']; ?>" 
               class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white text-center py-2 rounded transition">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST" class="flex-1" onsubmit="return confirm('Delete this sponsor?');">
                <input type="hidden" name="id" value="<?php echo $sponsor['id']; ?>">
                <button type="submit" name="delete_sponsor" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded transition">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
    <?php 
        endwhile;
    else:
    ?>
    <div class="col-span-4 bg-dark-card border border-dark-border rounded-lg p-8 text-center">
        <i class="fas fa-handshake text-4xl text-gray-600 mb-4 block"></i>
        <p class="text-gray-400">No sponsors found. Click "Add New Sponsor" to create one.</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include '../common/bottom.php'; ?>
