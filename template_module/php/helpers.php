<?php
// Helper functions for generating UI components

/**
 * Generate a dynamic Bootstrap grid layout
 * @param int $columns Number of columns
 * @param array $widths Array of percentage widths
 * @param array $contents Array of content (strings, callbacks, or function names)
 * @param array $options Additional options (e.g., class, id)
 */
function generateView($columns, $widths, $contents, $options = []) {
    global $workspace_slug, $app_domain, $module_slug;
    if (count($widths) !== $columns || count($contents) !== $columns) {
        return "<div class='alert alert-danger'>Error: Mismatched columns, widths, or contents</div>";
    }

    // Calculate Bootstrap column classes
    $col_classes = [];
    foreach ($widths as $width) {
        $col_width = round($width / 8.3333); // Convert percentage to Bootstrap's 12-column grid
        $col_width = max(1, min(12, $col_width)); // Clamp between 1 and 12
        $col_classes[] = "col-md-$col_width";
    }

    // Generate HTML
    $output = "<div class='container-fluid' " . (isset($options['id']) ? "id='{$options['id']}'" : "") . ">";
    $output .= "<div class='row'>";
    for ($i = 0; $i < $columns; $i++) {
        $output .= "<div class='{$col_classes[$i]} pane pane-" . ($i + 1) . "'>";
        $content = $contents[$i];
        if (is_callable($content)) {
            $output .= call_user_func($content, $options);
        } elseif (is_string($content) && function_exists($content)) {
            $output .= $content($options);
        } else {
            $output .= htmlspecialchars($content);
        }
        $output .= "</div>";
    }
    $output .= "</div></div>";
    return $output;
}

/**
 * Generate a grid layout (like Add Apps)
 * @param array $items Items to display
 * @param array $options Options (e.g., columns, fields)
 */
function generateGridView($items, $options = []) {
    global $workspace_slug, $app_domain, $module_slug;
    $columns = $options['columns'] ?? 4; // Default to 4 columns
    $fields = $options['fields'] ?? ['title' => 'Title', 'icon' => 'Icon', 'level' => 'Level'];
    $col_width = 12 / $columns;

    $output = "<div class='container-fluid'><div class='row'>";
    foreach ($items as $item) {
        $output .= "<div class='col-md-$col_width mb-3'>";
        $output .= "<div class='card text-center'>";
        if (isset($fields['icon']) && !empty($item['icon'])) {
            $output .= "<img src='{$item['icon']}' class='card-img-top' style='max-height: 100px; margin: 10px auto;' alt='{$item['title']}'>";
        }
        $output .= "<div class='card-body'>";
        if (isset($fields['title'])) {
            $title = htmlspecialchars($item['title']);
            $output .= "<h5 class='card-title'>$title</h5>";
        }
        if (isset($fields['level'])) {
            $level = htmlspecialchars($item['level']);
            $output .= "<span class='badge bg-success'>$level</span>";
        }
        $output .= "</div></div></div>";
    }
    $output .= "</div></div>";
    return $output;
}

/**
 * Generate a header (like StackNotes, StackInvoices)
 * @param array $options Options (e.g., title, actions, dropdown)
 */
function generateHeader($options = []) {
    global $workspace_slug, $app_domain, $module_slug, $module_display_name;
    $title = $options['title'] ?? $module_display_name;
    $actions = $options['actions'] ?? [];
    $dropdown = $options['dropdown'] ?? null;
    $item_count = $options['item_count'] ?? null;

    $output = "<div class='header d-flex justify-content-between align-items-center p-2 bg-light border-bottom'>";
    $output .= "<div>";
    if ($dropdown) {
        $output .= "<div class='dropdown d-inline-block'>";
        $output .= "<button class='btn btn-outline-secondary btn-sm dropdown-toggle' type='button' data-toggle='dropdown'>{$dropdown['label']}</button>";
        $output .= "<div class='dropdown-menu'>";
        foreach ($dropdown['items'] as $item) {
            $output .= "<a class='dropdown-item' href='{$item['url']}'>{$item['label']}</a>";
        }
        $output .= "</div></div> ";
    }
    $output .= "<span class='badge bg-primary mx-2'>$title";
    if ($item_count !== null) {
        $output .= " <span class='badge bg-secondary'>$item_count</span>";
    }
    $output .= "</span></div>";
    $output .= "<div>";
    foreach ($actions as $action) {
        $class = $action['class'] ?? 'btn btn-primary btn-sm';
        $output .= "<a href='{$action['url']}' class='$class mx-1'>{$action['label']}</a>";
    }
    $output .= "</div></div>";
    return $output;
}

/**
 * Generate a category pane (like StackNotes Pane 1)
 * @param array $options Options (e.g., categories, add_url)
 */
function generateCategoryPane($options = []) {
    global $workspace_slug, $app_domain, $module_slug;
    $categories = $options['categories'] ?? [];
    $add_url = $options['add_url'] ?? "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&action=add_category";
    $active_category = $options['active_category'] ?? 'All';

    $output = "<div class='d-flex justify-content-between align-items-center mb-2'>";
    $output .= "<h5>Categories</h5>";
    $output .= "<a href='$add_url' class='btn btn-outline-primary btn-sm'>+</a>";
    $output .= "</div>";
    $output .= "<ul class='list-group'>";
    foreach ($categories as $category) {
        $name = htmlspecialchars($category['name']);
        $url = htmlspecialchars($category['url']);
        $active = $name === $active_category ? 'active' : '';
        $output .= "<a href='$url' class='list-group-item list-group-item-action $active'>$name</a>";
    }
    $output .= "</ul>";
    return $output;
}

/**
 * Generate a list pane (like StackNotes Pane 2)
 * @param array $options Options (e.g., items, fields)
 */
function generateListPane($options = []) {
    global $workspace_slug, $app_domain, $module_slug, $items, $page, $total_pages, $search;
    $items = $options['items'] ?? $items;
    $fields = $options['fields'] ?? ['title' => 'Title', 'excerpt' => 'Excerpt', 'date' => 'Date'];
    $items_per_page = $options['items_per_page'] ?? 10;

    $output = "<div class='list-group'>";
    if (empty($items)) {
        $output .= "<p>No items found.</p>";
    } else {
        foreach ($items as $item) {
            $active = isset($_GET['item_id']) && $_GET['item_id'] == $item['id'] ? 'active' : '';
            $output .= "<a href='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&item_id={$item['id']}' class='list-group-item list-group-item-action $active' data-item-id='{$item['id']}'>";
            foreach ($fields as $field => $label) {
                if ($field === 'date') {
                    $value = date('Y-m-d H:i', strtotime($item[$field]));
                    $output .= "<small>$value</small>";
                } elseif ($field === 'excerpt') {
                    $value = htmlspecialchars(substr($item[$field], 0, 50)) . (strlen($item[$field]) > 50 ? '...' : '');
                    $output .= "<p class='mb-1'>$value</p>";
                } else {
                    $value = htmlspecialchars($item[$field]);
                    $output .= "<h6>$value</h6>";
                }
            }
            $output .= "</a>";
        }
    }
    $output .= "</div>";

    // Pagination
    if ($total_pages > 1) {
        $output .= "<nav aria-label='Page navigation'><ul class='pagination mt-3'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $page == $i ? 'active' : '';
            $search_param = $search ? "&search=" . urlencode($search) : '';
            $output .= "<li class='page-item $active'><a class='page-link' href='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&page=$i$search_param'>$i</a></li>";
        }
        $output .= "</ul></nav>";
    }
    return $output;
}

/**
 * Generate a form pane (like StackNotes Pane 3, StackInvoices, StackTests)
 * @param array $options Options (e.g., item, action, fields, csrf_token)
 */
function generateFormPane($options = []) {
    global $workspace_slug, $app_domain, $module_slug, $module_post_url;
    $item = $options['item'] ?? null;
    $action = $options['action'] ?? 'view';
    $fields = $options['fields'] ?? [
        'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
        'content' => ['label' => 'Content', 'type' => 'textarea', 'rich_text' => true]
    ];
    $csrf_token = $options['csrf_token'] ?? '';

    if ($action === 'add' || ($action === 'edit' && $item)) {
        $is_edit = $action === 'edit';
        $output = "<h5>" . ($is_edit ? "Edit Item" : "Add New Item") . "</h5>";
        $output .= "<form action='$module_post_url' method='POST' id='form-" . ($is_edit ? "edit" : "add") . "-item'>";
        foreach ($fields as $name => $field) {
            $value = $is_edit ? htmlspecialchars($item[$name]) : '';
            $output .= "<div class='form-group'>";
            $output .= "<label for='$name'>{$field['label']}</label>";
            if ($field['type'] === 'textarea') {
                if (isset($field['rich_text']) && $field['rich_text']) {
                    $output .= "<textarea name='$name' id='$name' class='form-control tinymce' " . ($field['required'] ? " required" : "") . ">$value</textarea>";
                } else {
                    $output .= "<textarea name='$name' id='$name' class='form-control' " . ($field['required'] ? " required" : "") . ">$value</textarea>";
                }
            } else {
                $output .= "<input type='{$field['type']}' name='$name' id='$name' class='form-control' value='$value' " . ($field['required'] ? " required" : "") . ">";
            }
            $output .= "</div>";
        }
        $output .= "<input type='hidden' name='action' value='" . ($is_edit ? "edit-item" : "add-item") . "'>";
        $output .= "<input type='hidden' name='csrf_token' value='$csrf_token'>";
        if ($is_edit) {
            $output .= "<input type='hidden' name='item_id' value='{$item['id']}'>";
        }
        $output .= "<input type='hidden' name='workspace_slug' value='$workspace_slug'>";
        $output .= "<input type='hidden' name='referrer' value='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug'>";
        $output .= "<div class='form-group'>";
        $output .= "<button type='submit' class='btn btn-success btn-sm mr-2'>Save</button>";
        $output .= "<a href='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug' class='btn btn-secondary btn-sm'>Cancel</a>";
        $output .= "</div></form>";
    } elseif ($item) {
        $output = "<h5>" . htmlspecialchars($item['title']) . "</h5>";
        foreach ($fields as $name => $field) {
            $value = $name === 'content' ? $item[$name] : htmlspecialchars($item[$name]); // Allow HTML for content
            $output .= "<div class='form-group'>";
            $output .= "<label>{$field['label']}</label>";
            $output .= "<div>$value</div>";
            $output .= "</div>";
        }
        $output .= "<div class='form-group'>";
        $output .= "<a href='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&item_id={$item['id']}&action=edit' class='btn btn-primary btn-sm mr-2'>Edit</a>";
        $output .= "<form action='$module_post_url' method='POST' style='display:inline;'>";
        $output .= "<input type='hidden' name='action' value='delete-item'>";
        $output .= "<input type='hidden' name='csrf_token' value='$csrf_token'>";
        $output .= "<input type='hidden' name='item_id' value='{$item['id']}'>";
        $output .= "<input type='hidden' name='workspace_slug' value='$workspace_slug'>";
        $output .= "<input type='hidden' name='referrer' value='https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug'>";
        $output .= "<button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this item?\");'>Delete</button>";
        $output .= "</form></div>";
    } else {
        $output = "<p>Select an item or click 'Add' to create a new one.</p>";
    }
    return $output;
}

/**
 * Generate a custom single-pane view (like StackTests, StackInvoices)
 * @param array $options Options (e.g., content)
 */
function generateCustomPane($options = []) {
    $content = $options['content'] ?? '<h5>Welcome</h5><p>Customize this content in config.php or helpers.php.</p>';
    return "<div class='single-pane'>$content</div>";
}

/**
 * Developer Notes:
 * - Use generateView() for dynamic layouts (e.g., three-pane, single-pane).
 * - Use generateGridView() for tiled layouts (e.g., Add Apps).
 * - Use generateHeader() to create app headers with actions and dropdowns.
 * - Use generateCategoryPane(), generateListPane(), generateFormPane() for common UI patterns.
 * - Customize in config.php with simple arrays.
 */
?>