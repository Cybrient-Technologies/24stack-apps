# Template Module for 24stack (PHP)

## Overview
This template enables rapid app development on the 24stack platform with a dynamic, non-coder-friendly approach. It supports three-pane (like StackNotes), single-pane (like StackInvoices, StackTests), and grid layouts (like Add Apps) using helper functions in `helpers.php`.

- **Module Name**: Template Module
- **Version**: 1.0
- **Author**: Cybrient Technologies
- **License**: Free (configurable for paid modules)
- **Description**: A flexible template for rapid 24stack app development.
- **Repository**: [github.com/cybrient-technologies/24stack-apps](https://github.com/cybrient-technologies/24stack-apps)

## Features
- **Dynamic Layouts**: Three-pane, single-pane, or grid layouts.
- **Non-Coder-Friendly**: Define your app in `config.php` with simple arrays.
- **Pre-built UI**:
  - Headers with actions and dropdowns.
  - Categories, paginated lists, forms with TinyMCE.
  - Grid tiles for apps.
- **Secure**: PDO-based queries, sanitized inputs, CSRF protection.

## Installation
1. **Copy Module Files**:
   - Place the `template_module` directory in your 24stack installation’s `app/modules/` directory.
   - Directory structure after copying:
     ```
     app/modules/template_module/
     ├── config.php
     ├── module.php
     ├── post.php
     ├── css/
     │   └── module.css.php
     ├── js/
     │   └── module.js.php
     ├── sql/
     │   └── install.sql
     ├── models/
     │   └── Item.php
     ├── helpers.php
     ├── README.md
     ├── CHANGELOG.md
     └── LICENSE
     ```

2. **Set Up Database**:
   - Run `sql/install.sql` in `24stack.<workspace_slug>`:
     ```sql
     CREATE TABLE `modules_template_module` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `item_title` VARCHAR(255) NOT NULL,
         `item_content` TEXT,
         `item_category` VARCHAR(255) DEFAULT 'Uncategorized',
         `item_added` DATETIME NOT NULL,
         `item_edited` DATETIME,
         `item_userid` INT NOT NULL
     );

     CREATE TABLE `modules_template_module_categories` (
         `id` INT AUTO_INCREMENT PRIMARY KEY,
         `category` VARCHAR(255) NOT NULL,
         `category_slug` VARCHAR(255) NOT NULL,
         `category_userid` INT NOT NULL
     );
     ```

3. **Configure Module**:
   - Edit `config.php`:
     ```php
     $module_display_name = "My App";
     $module_slug = "my_app";
     $layout = 'three-pane';
     $layout_columns = 3;
     $layout_widths = [20, 30, 50];
     $layout_contents = ['generateCategoryPane', 'generateListPane', 'generateFormPane'];
     ```

4. **Verify Dependencies**:
   - The template uses CDNs for jQuery and TinyMCE:
     - jQuery 3.7.1 from `https://code.jquery.com`
     - TinyMCE 7 from `https://cdn.tiny.cloud`
   - Ensure your server allows external scripts (no restrictive Content Security Policy blocking `code.jquery.com` or `cdn.tiny.cloud`).
   - If you prefer to host locally, download jQuery and TinyMCE and update `module.php` accordingly.

5. **Test Installation**:
   - Access at `https://<workspace>.24stack.com/app/dashboard.php?app=template_module`.

## Versioning
- The template follows semantic versioning (e.g., `1.0`, `1.1`, `2.0`).
- Check the [CHANGELOG.md](../CHANGELOG.md) for updates in each version.
- Download specific versions from GitHub Releases: [github.com/cybrient-technologies/24stack-apps/releases](https://github.com/cybrient-technologies/24stack-apps/releases).

## Configuration
- **config.php**:
  - **Metadata**: Set `$module_display_name`, `$module_slug`, etc.
  - **Layout**:
    ```php
    $layout = 'three-pane'; // or 'single-pane', 'grid'
    $layout_columns = 3;
    $layout_widths = [20, 30, 50];
    $layout_contents = ['generateCategoryPane', 'generateListPane', 'generateFormPane'];
    ```
  - **Header**:
    ```php
    $header = [
        'title' => 'All',
        'dropdown' => ['label' => 'Categories', 'items' => []],
        'actions' => [
            ['label' => 'Add a new item', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&action=add", 'class' => 'btn btn-success btn-sm']
        ]
    ];
    ```
  - **Categories**:
    ```php
    $categories = [
        ['name' => 'All', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug"],
        ['name' => 'Uncategorized', 'url' => "https://$workspace_slug.$app_domain/app/dashboard.php?app=$module_slug&category=uncategorized"]
    ];
    ```
  - **Fields**:
    ```php
    $fields = [
        'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
        'content' => ['label' => 'Content', 'type' => 'textarea', 'rich_text' => true]
    ];
    ```
  - **TinyMCE**:
    ```php
    $enable_tinymce = true;
    ```

## Examples
1. **Three-Pane App (like StackNotes)**:
   - `config.php`:
     ```php
     $layout = 'three-pane';
     $layout_columns = 3;
     $layout_widths = [20, 30, 50];
     $layout_contents = ['generateCategoryPane', 'generateListPane', 'generateFormPane'];
     ```
   - Result: Categories, item list, and rich-text editor.

2. **Single-Pane App (like StackInvoices)**:
   - `config.php`:
     ```php
     $layout = 'single-pane';
     $layout_columns = 1;
     $layout_widths = [100];
     $layout_contents = ['generateFormPane'];
     ```
   - Result: Full-width form or content.

3. **Grid App (like Add Apps)**:
   - `config.php`:
     ```php
     $layout = 'grid';
     ```
   - Result: Tiled layout with app cards.

## Security Best Practices
- **Input Sanitization**: Always sanitize user inputs using `filter_input()`. For rich text (e.g., TinyMCE), consider client-side sanitization with DOMPurify.
- **CSRF Protection**: Included in forms and verified in `post.php`.
- **Database Security**: Use prepared statements for all queries.
- **CDN Security**: The template uses CDNs for jQuery and TinyMCE. jQuery includes an SRI hash for integrity verification. For TinyMCE, replace the `no-api-key` CDN URL with your own TinyMCE API key to avoid potential throttling (get a free key at https://www.tiny.cloud).
- **Server Configuration**: Disable directory indexing (e.g., via `.htaccess`) to prevent exposure of module structure.

## Extending the Module
- **Add Fields**:
  - Update `$fields` in `config.php`:
    ```php
    $fields['new_field'] = ['label' => 'New Field', 'type' => 'text'];
    ```
  - Update `sql/install.sql`:
    ```sql
    ALTER TABLE `modules_template_module` ADD `new_field` VARCHAR(255);
    ```

- **Custom Content**:
  - Add to `helpers.php`:
    ```php
    function myCustomPane($options = []) {
        return "<h5>My Content</h5><p>" . ($options['content'] ?? 'Custom content') . "</p>";
    }
    ```
  - Update `config.php`:
    ```php
    $layout_contents = ['myCustomPane'];
    ```

## Logging
- **Template Logging**: PDO-based `template_insertLog`. Replace with core `insertLog` (MySQLi-based) if needed.
- **Logs Table**:
  ```sql
  CREATE TABLE `logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `verb` VARCHAR(50),
      `action` VARCHAR(255),
      `view` VARCHAR(50),
      `date` DATETIME NOT NULL
  );
  ```

## Licensing
- **Module-Specific**: Define `verifyLicense($db, $workspace_slug)` in `config.php` with `function_exists`.
- **Core Licensing**: Optionally use `verifyLicense($db, $workspace_slug, $module_slug)` in `functions.apps.php`.

## Developer Notes
- **Security**:
  - Sanitized inputs with `filter_input`.
  - PDO prepared statements.
  - CSRF protection implemented.
- **Dependencies**:
  - Uses CDNs for jQuery and TinyMCE.
  - Bootstrap assumed via `init.php`.

## Contributing
Submit pull requests or issues at [github.com/cybrient-technologies/24stack-apps](https://github.com/cybrient-technologies/24stack-apps).

## License
Free to use within 24stack. See [LICENSE](../LICENSE) for details.

---

*Generated by the 24stack team as a template for rapid app development.*