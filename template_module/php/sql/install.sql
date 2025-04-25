-- /template_module/sql/install.sql
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

/**
 * Developer Notes:
 * - Includes tables for items and categories.
 * - Customize schema for your module.
 * - Run in the workspace-specific database (24stack.<workspace_slug>).
 */
?>