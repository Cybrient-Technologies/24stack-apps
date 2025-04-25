<?php
/**
 * Configuration file for the template module
 *
 * @package 24stack
 * @description Configuration for a template module. Customize for your module.
 */

// Module metadata
$module_display_name = "Template Module"; // Name displayed in the UI
$module_display_version = "1.0"; // Module version (increment with updates, e.g., 1.1, 2.0)
$module_display_price = "0"; // Price (e.g., "9.99" for paid modules, "0" for free)
$module_display_icon = "cog"; // Font Awesome or other icon class
$module_display_author = "Cybrient Technologies"; // Author name
$module_display_author_url = "https://github.com/cybrient-technologies"; // Author website
$module_display_description = "A dynamic template for 24stack applications."; // Module description
$module_display_icon_color = "#000000"; // Icon color
$module_display_icon_background_color = "#ffffff"; // Icon background color

// Module paths and URLs
$module_slug = "template_module"; // Unique module identifier (lowercase, no spaces)
$module_root = $root . "app/modules/$module_slug/"; // Base path for module files
$module_post_url = $base_url . "app/modules/$module_slug/post.php"; // URL for form submissions
$module_css_url = $module_root . "css/module.css.php"; // CSS file URL
$module_js_url = $module_root . "js/module.js.php"; // JavaScript file URL
$module_sql_path = $module_root . "sql/install.sql"; // SQL schema path

// Database configuration
$module_table_prefix = "modules_$module_slug"; // Prefix for module-specific tables

// Layout configuration
$layout = 'three-pane'; // Options: 'three-pane', 'single-pane', 'grid'
$layout_columns = 3; // Number of columns (ignored for grid)
$layout_widths = [20, 30, 50]; // Width percentages (sum should be ~100)
$layout_contents = [
    'generateCategoryPane', // Pane 1: Categories
    'generateListPane',     // Pane 2: Item list
    'generateFormPane'      // Pane 3: Item details/add/edit
];

// Header configuration
$header = [
    'title' => 'All', // Default title
    'item_count' => 0, // Updated dynamically in module.php
    'dropdown' => [
        'label' => 'Categories',
        'items' => [] // Updated dynamically in module.php
    ],
    'actions' => [
        ['label' => 'Add a new item', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&action=add", 'class' => 'btn btn-success btn-sm']
    ]
];

// Categories (for three-pane layout)
$categories = [
    ['name' => 'All', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug"],
    ['name' => 'Uncategorized', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&category=uncategorized"]
];

// Fields for forms and lists
$fields = [
    'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
    'content' => ['label' => 'Content', 'type' => 'textarea', 'rich_text' => true]
];

// Enable TinyMCE (for rich text editing)
$enable_tinymce = true;

// Monetization settings
$module_is_paid = false; // Set to true for paid modules
$module_license_check = false; // Set to true to enable license verification
if (!function_exists('verifyLicense')) {
    function verifyLicense($db, $workspace_slug) {
        return true; // Default: Always valid (free module)
    }
}

/**
 * Developer Notes:
 * - Update metadata (name, version, etc.).
 * - Set $layout to 'three-pane', 'single-pane', or 'grid'.
 * - Configure $layout_columns, $layout_widths, and $layout_contents for your app.
 * - Define $header for app-specific headers.
 * - Define $categories for category pane (three-pane layout).
 * - Define $fields for forms and lists.
 * - Set $enable_tinymce to false to disable TinyMCE.
 * - Version updates are tracked in CHANGELOG.md.
 */
?>