<?php
// Handles form submissions and CRUD operations
// Initialize session and load configuration
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_domain', $app_dot_domain);
session_start();

// Load core initialization and module configuration
require_once __DIR__ . '/../../../core/init.php';
require_once __DIR__ . '/config.php';

// Validate CSRF token
$csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
    $referrer = filter_input(INPUT_POST, 'referrer', FILTER_SANITIZE_URL) ?: "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug";
    $url_delimiter = strpos($referrer, '?') !== false ? "&" : "?";
    header("Location: $referrer{$url_delimiter}alert=Invalid+CSRF+token&alert_type=danger");
    exit;
}

// Placeholder for insertLog
function template_insertLog($db, $user_id, $workspace_slug, $verb, $action, $view) {
    try {
        if ($view) {
            $stmt = $db->prepare("INSERT INTO `24stack_$workspace_slug`.`logs` (`user_id`, `verb`, `action`, `view`, `date`) VALUES (:user_id, :verb, :action, :view, CURRENT_TIMESTAMP)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':verb' => $verb,
                ':action' => $action,
                ':view' => $view
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO `24stack_$workspace_slug`.`logs` (`user_id`, `verb`, `action`, `date`) VALUES (:user_id, :verb, :action, CURRENT_TIMESTAMP)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':verb' => $verb,
                ':action' => $action
            ]);
        }
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Validate user session
$user_array = isset($_SESSION['APPUser']) ? json_decode($_SESSION['APPUser'], true) : [];
if (!isset($user_array['id'], $user_array['email']) || empty($user_array)) {
    $redirect_url = $base_url . "$lang/login?alert=Invalid+session.+Please+log+in+again&alert_type=danger&source=$module_slug";
    header("Location: $redirect_url");
    exit;
}

// Initialize variables
$user_id = $user_array['id'];
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
$workspace_slug = filter_input(INPUT_POST, 'workspace_slug', FILTER_SANITIZE_STRING);
$referrer = filter_input(INPUT_POST, 'referrer', FILTER_SANITIZE_URL) ?: "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug";
$url_delimiter = strpos($referrer, '?') !== false ? "&" : "?";

// Initialize database connection
try {
    $db = Database::getConnectionForWorkspace($workspace_slug);
    $db->prepare("SET NAMES 'utf8'")->execute();
} catch (Exception $e) {
    header("Location: $referrer{$url_delimiter}alert=Database+connection+failed&alert_type=danger");
    exit;
}

// Process actions
switch ($action) {
    case 'add-item':
        $item_title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $item_content = filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW);
        $item_category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING) ?: 'Uncategorized';

        if (!empty($item_title)) {
            try {
                $stmt = $db->prepare("INSERT INTO `$module_table_prefix` (`item_title`, `item_content`, `item_category`, `item_added`, `item_userid`) VALUES (:title, :content, :category, CURRENT_TIMESTAMP, :user_id)");
                $stmt->execute([
                    ':title' => $item_title,
                    ':content' => $item_content,
                    ':category' => $item_category,
                    ':user_id' => $user_id
                ]);
                $item_id = $db->lastInsertId();

                if ($item_id) {
                    template_insertLog($db, $user_id, $workspace_slug, "added", "item $item_id", $module_slug);
                    header("Location: $referrer{$url_delimiter}item_id=$item_id&alert=Item+$item_id+successfully+added&alert_type=success");
                } else {
                    header("Location: $referrer{$url_delimiter}action=add&alert=Failed+to+add+item&alert_type=danger");
                }
            } catch (PDOException $e) {
                header("Location: $referrer{$url_delimiter}action=add&alert=Database+error+while+adding+item&alert_type=danger");
            }
        } else {
            header("Location: $referrer{$url_delimiter}action=add&alert=Title+cannot+be+empty&alert_type=danger");
        }
        break;

    case 'edit-item':
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
        $item_title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $item_content = filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW);
        $item_category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING) ?: 'Uncategorized';

        if ($item_id && !empty($item_title)) {
            try {
                $stmt = $db->prepare("UPDATE `$module_table_prefix` SET item_title = :title, item_content = :content, item_category = :category, item_edited = CURRENT_TIMESTAMP WHERE id = :item_id AND item_userid = :user_id");
                $stmt->execute([
                    ':title' => $item_title,
                    ':content' => $item_content,
                    ':category' => $item_category,
                    ':item_id' => $item_id,
                    ':user_id' => $user_id
                ]);

                template_insertLog($db, $user_id, $workspace_slug, "edited", "item #$item_id", $module_slug);
                header("Location: $referrer{$url_delimiter}item_id=$item_id&alert=Item+successfully+edited&alert_type=success");
            } catch (PDOException $e) {
                header("Location: $referrer{$url_delimiter}item_id=$item_id&action=edit&alert=Database+error+while+editing+item&alert_type=danger");
            }
        } else {
            header("Location: $referrer{$url_delimiter}item_id=$item_id&action=edit&alert=Item+ID+or+title+missing&alert_type=danger");
        }
        break;

    case 'delete-item':
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);

        if ($item_id) {
            try {
                $stmt = $db->prepare("DELETE FROM `$module_table_prefix` WHERE id = :item_id AND item_userid = :user_id LIMIT 1");
                $stmt->execute([':item_id' => $item_id, ':user_id' => $user_id]);

                template_insertLog($db, $user_id, $workspace_slug, "deleted", "item $item_id", $module_slug);
                header("Location: $referrer{$url_delimiter}alert=Item+successfully+deleted&alert_type=success");
            } catch (PDOException $e) {
                header("Location: $referrer{$url_delimiter}item_id=$item_id&alert=Database+error+while+deleting+item&alert_type=danger");
            }
        } else {
            header("Location: $referrer{$url_delimiter}alert=Item+ID+missing&alert_type=danger");
        }
        break;

    case 'add-category':
        $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING);
        $category_slug = strtolower(preg_replace('/\s+/', '-', $category_name));

        if (!empty($category_name)) {
            try {
                $stmt = $db->prepare("INSERT INTO `modules_{$module_slug}_categories` (`category`, `category_slug`, `category_userid`) VALUES (:category, :category_slug, :user_id)");
                $stmt->execute([
                    ':category' => $category_name,
                    ':category_slug' => $category_slug,
                    ':user_id' => $user_id
                ]);
                $category_id = $db->lastInsertId();

                if ($category_id) {
                    template_insertLog($db, $user_id, $workspace_slug, "added", "category $category_name", $module_slug);
                    header("Location: $referrer{$url_delimiter}alert=Category+successfully+added&alert_type=success");
                } else {
                    header("Location: $referrer{$url_delimiter}alert=Failed+to+add+category&alert_type=danger");
                }
            } catch (PDOException $e) {
                header("Location: $referrer{$url_delimiter}alert=Database+error+while+adding+category&alert_type=danger");
            }
        } else {
            header("Location: $referrer{$url_delimiter}alert=Category+name+missing&alert_type=danger");
        }
        break;

    default:
        header("Location: $referrer{$url_delimiter}alert=Invalid+action&alert_type=danger");
        break;
}

/**
 * Developer Notes:
 * - Added CSRF token verification.
 * - Handles CRUD actions for items and categories.
 * - Customize for additional actions or fields.
 */
?>