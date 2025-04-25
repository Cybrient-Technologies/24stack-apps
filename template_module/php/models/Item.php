<?php
// Model class for handling database operations
require_once __DIR__ . '/../config.php';

class Item {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getItems($user_id, $category = 'All', $search = '', $page = 1, $items_per_page = 10) {
        $offset = ($page - 1) * $items_per_page;
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
        $stmt = $this->db->prepare($query);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalItems($user_id, $category = 'All', $search = '') {
        $query = "SELECT COUNT(*) FROM `$module_table_prefix` WHERE item_userid = :user_id";
        $params = [':user_id' => $user_id];
        if ($category !== 'All') {
            $query .= " AND item_category = :category";
            $params[':category'] = $category;
        }
        if ($search) {
            $query .= " AND item_title LIKE :search";
            $params[':search'] = "%$search%";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        if ($category !== 'All') {
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        }
        if ($search) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getItem($user_id, $item_id) {
        $stmt = $this->db->prepare("SELECT * FROM `$module_table_prefix` WHERE item_userid = :user_id AND id = :item_id LIMIT 1");
        $stmt->execute([':user_id' => $user_id, ':item_id' => $item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addItem($user_id, $title, $content, $category) {
        $stmt = $this->db->prepare("INSERT INTO `$module_table_prefix` (`item_title`, `item_content`, `item_category`, `item_added`, `item_userid`) VALUES (:title, :content, :category, CURRENT_TIMESTAMP, :user_id)");
        return $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':user_id' => $user_id
        ]);
    }

    public function updateItem($user_id, $item_id, $title, $content, $category) {
        $stmt = $this->db->prepare("UPDATE `$module_table_prefix` SET item_title = :title, item_content = :content, item_category = :category, item_edited = CURRENT_TIMESTAMP WHERE id = :item_id AND item_userid = :user_id");
        return $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':item_id' => $item_id,
            ':user_id' => $user_id
        ]);
    }

    public function deleteItem($user_id, $item_id) {
        $stmt = $this->db->prepare("DELETE FROM `$module_table_prefix` WHERE id = :item_id AND item_userid = :user_id LIMIT 1");
        return $stmt->execute([':item_id' => $item_id, ':user_id' => $user_id]);
    }

    public function addCategory($user_id, $category, $category_slug) {
        $stmt = $this->db->prepare("INSERT INTO `modules_{$module_slug}_categories` (`category`, `category_slug`, `category_userid`) VALUES (:category, :category_slug, :user_id)");
        return $stmt->execute([
            ':category' => $category,
            ':category_slug' => $category_slug,
            ':user_id' => $user_id
        ]);
    }
}

/**
 * Developer Notes:
 * - Encapsulates database operations for items and categories.
 * - Customize for additional fields or queries.
 */
?>