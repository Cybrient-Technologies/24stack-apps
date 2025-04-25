<?php
// Main module file for rendering the app UI

// Ensure required variables
global $workspace_slug, $root, $user_id, $app_domain, $base_url;
if (!isset($workspace_slug, $user_id, $app_domain, $base_url)) {
    generateError("Internal server error: Module configuration missing");
    return;
}

// Load configuration and helpers
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Generate CSRF token
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Module parameters
$app = isset($_GET['app']) ? filter_var($_GET['app'], FILTER_SANITIZE_STRING) : $module_slug;
$item_id = isset($_GET['item_id']) ? filter_var($_GET['item_id'], FILTER_SANITIZE_NUMBER_INT) : '';
$action = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_STRING) : 'view';
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
$category = isset($_GET['category']) ? filter_var($_GET['category'], FILTER_SANITIZE_STRING) : 'All';
$search = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';

// Initialize database
try {
    $db = Database::getConnectionForWorkspace($workspace_slug);
    $db->prepare("SET NAMES 'utf8'")->execute();
} catch (Exception $e) {
    generateError("Failed to connect to database: " . htmlspecialchars($e->getMessage()));
    return;
}

// Load categories (for three-pane layout)
global $categories;
$categories_table = "modules_{$module_slug}_categories";
try {
    $stmt = $db->prepare("SELECT category, category_slug FROM `$categories_table` WHERE category_userid = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $db_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($db_categories as $cat) {
        $categories[] = [
            'name' => $cat['category'],
            'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&category=" . urlencode($cat['category_slug'])
        ];
    }
} catch (PDOException $e) {
    // Silently fail (categories table may not exist yet)
}

// Update header with dynamic data
global $header;
$header['item_count'] = 0; // Will be updated below
$header['dropdown']['items'] = array_map(function($cat) {
    return ['label' => $cat['name'], 'url' => $cat['url']];
}, $categories);

// Load items for list pane or grid view
$items = [];
$total_pages = 1;
if ($layout === 'three-pane' || $layout === 'grid') {
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;
    try {
        $query = "SELECT * FROM `$module_table_prefix` WHERE item_userid = :user_id";
        $params = [':user_id' => $user_id];
        if ($category !== 'All') {
            $query .= " AND item_category = :category";
            $params[':category'] = $category;
        }
        if ($search) {
            $query .= " AND item_title LIKE :search";
            $params[':search'] = "%$search%";
        }
        $query .= " ORDER BY item_added DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        if ($category !== 'All') {
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        }
        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count_query = "SELECT COUNT(*) FROM `$module_table_prefix` WHERE item_userid = :user_id";
        if ($category !== 'All') {
            $count_query .= " AND item_category = :category";
        }
        if ($search) {
            $count_query .= " AND item_title LIKE :search";
        }
        $count_stmt = $db->prepare($count_query);
        $count_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        if ($category !== 'All') {
            $count_stmt->bindValue(':category', $category, PDO::PARAM_STR);
        }
        if ($search) {
            $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $count_stmt->execute();
        $total_items = $count_stmt->fetchColumn();
        $header['item_count'] = $total_items;
        $total_pages = ceil($total_items / $items_per_page);
    } catch (PDOException $e) {
        generateError("Database query error: " . htmlspecialchars($e->getMessage()));
    }
}

// Load selected item for form pane
$selected_item = null;
if ($item_id && $action !== 'add' && in_array('generateFormPane', $layout_contents)) {
    try {
        $stmt = $db->prepare("SELECT * FROM `$module_table_prefix` WHERE item_userid = :user_id AND id = :item_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id]);
        $selected_item = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        generateError("Database query error: " . htmlspecialchars($e->getMessage()));
    }
}

// Render layout
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($module_css_url); ?>" media="screen">
<?php
// Render header
echo generateHeader($header);

// Render content based on layout
if ($layout === 'three-pane') {
    echo generateView($layout_columns, $layout_widths, $layout_contents, [
        'categories' => $categories,
        'active_category' => $category,
        'items' => $items,
        'action' => $action,
        'item' => $selected_item,
        'fields' => $fields,
        'items_per_page' => 10,
        'csrf_token' => $csrf_token
    ]);
} elseif ($layout === 'grid') {
    echo generateGridView($items, [
        'columns' => 4,
        'fields' => ['title' => 'Title', 'icon' => 'Icon', 'level' => 'Level'],
        'csrf_token' => $csrf_token
    ]);
} else {
    echo generateCustomPane(['csrf_token' => $csrf_token]);
}
?>
<!-- Load jQuery from CDN with SRI -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<?php if ($enable_tinymce): ?>
<!-- Load TinyMCE from CDN -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '.tinymce',
    plugins: 'lists link image table',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link image table',
    menubar: false,
    height: 300
});
</script>
<?php endif; ?>
<script src="<?php echo htmlspecialchars($module_js_url); ?>"></script>

/**
 * Developer Notes:
 * - Uses CDNs for jQuery and TinyMCE to simplify setup and shift liability.
 * - Includes SRI for jQuery to mitigate CDN risks.
 * - TinyMCE CDN uses a no-api-key version; developers can replace with their own API key.
 * - Added CSRF token generation.
 * - Renders the app with a header and dynamic layout.
 */
?>