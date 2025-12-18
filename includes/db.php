<?php
/**
 * Database Class for ISU Lost & Found System
 * 
 * Implements Singleton pattern for database connection management.
 * Uses PDO with prepared statements to prevent SQL injection.
 * 
 * Features:
 * - User CRUD operations (create, read, update)
 * - Lost items management with soft delete
 * - Found items management with soft delete  
 * - Category management with foreign key relationships
 * - Activity logging for admin audit trail
 * - Statistics aggregation for dashboard
 * 
 * Security measures:
 * - PDO::ATTR_EMULATE_PREPARES = false (true prepared statements)
 * - All queries use named parameters (:param)
 * - Proper error handling with PDO::ERRMODE_EXCEPTION
 * 
 * @package ISU_Lost_Found
 * @since 1.0.0
 */
class Database
{
    /** @var Database|null Singleton instance */
    private static $instance = null;
    
    /** @var PDO Database connection */
    private $conn;

    private function __construct()
    {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    // User operations
    public function createUser($full_name, $username, $email, $student_id, $phone, $password_hash)
    {
        $sql = "INSERT INTO users (full_name, username, email, student_staff_id, phone, password) 
                VALUES (:full_name, :username, :email, :student_id, :phone, :password)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name' => $full_name,
            ':username' => $username,
            ':email' => $email,
            ':student_id' => $student_id,
            ':phone' => $phone,
            ':password' => $password_hash
        ]);
    }

    public function getUserByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':username' => $username, ':email' => $username]);
        return $stmt->fetch();
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateUserLastLogin($id)
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function updateUserPassword($id, $password_hash)
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':password' => $password_hash]);
    }

    public function updateUserProfile($id, $full_name, $email, $phone)
    {
        $sql = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone
        ]);
    }

    // Lost items operations
    public function createLostItem($user_id, $item_name, $category_id, $description, $date_lost, $location, $image_path, $contact_email, $contact_phone)
    {
        $sql = "INSERT INTO lost_items (user_id, item_name, category_id, description, date_lost, location, image_path, contact_email, contact_phone) 
                VALUES (:user_id, :item_name, :category_id, :description, :date_lost, :location, :image_path, :contact_email, :contact_phone)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':item_name' => $item_name,
            ':category_id' => $category_id,
            ':description' => $description,
            ':date_lost' => $date_lost,
            ':location' => $location,
            ':image_path' => $image_path,
            ':contact_email' => $contact_email,
            ':contact_phone' => $contact_phone
        ]);
    }

    public function getLostItems($filters = [], $limit = 50, $offset = 0, $includeDeleted = false)
    {
        $sql = "SELECT l.*, u.full_name, u.username, c.name AS category FROM lost_items l 
                JOIN users u ON l.user_id = u.id
                JOIN categories c ON l.category_id = c.id WHERE 1=1";
        $params = [];

        if (!$includeDeleted) {
            $sql .= " AND l.is_deleted = 0";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.item_name LIKE :search OR l.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND l.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY l.date_posted DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLostItemById($id)
    {
        $sql = "SELECT l.*, u.full_name, u.username, c.name AS category FROM lost_items l 
                JOIN users u ON l.user_id = u.id
                JOIN categories c ON l.category_id = c.id WHERE l.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateLostItem($id, $item_name, $category_id, $description, $date_lost, $location, $contact_email, $contact_phone)
    {
        $sql = "UPDATE lost_items SET item_name = :item_name, category_id = :category_id, description = :description, 
                date_lost = :date_lost, location = :location, contact_email = :contact_email, contact_phone = :contact_phone 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':item_name' => $item_name,
            ':category_id' => $category_id,
            ':description' => $description,
            ':date_lost' => $date_lost,
            ':location' => $location,
            ':contact_email' => $contact_email,
            ':contact_phone' => $contact_phone
        ]);
    }

    public function updateLostItemStatus($id, $status)
    {
        $sql = "UPDATE lost_items SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function deleteLostItem($id)
    {
        $sql = "UPDATE lost_items SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function hardDeleteLostItem($id)
    {
        $sql = "DELETE FROM lost_items WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function restoreLostItem($id)
    {
        $sql = "UPDATE lost_items SET is_deleted = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getUserLostItems($user_id, $limit = 50)
    {
        $sql = "SELECT l.*, c.name AS category FROM lost_items l
                JOIN categories c ON l.category_id = c.id
                WHERE l.user_id = :user_id ORDER BY l.date_posted DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Found items operations
    public function createFoundItem($user_id, $item_name, $category_id, $description, $date_found, $location, $image_path, $contact_email, $contact_phone)
    {
        $sql = "INSERT INTO found_items (user_id, item_name, category_id, description, date_found, location, image_path, contact_email, contact_phone) 
                VALUES (:user_id, :item_name, :category_id, :description, :date_found, :location, :image_path, :contact_email, :contact_phone)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':item_name' => $item_name,
            ':category_id' => $category_id,
            ':description' => $description,
            ':date_found' => $date_found,
            ':location' => $location,
            ':image_path' => $image_path,
            ':contact_email' => $contact_email,
            ':contact_phone' => $contact_phone
        ]);
    }

    public function getFoundItems($filters = [], $limit = 50, $offset = 0, $includeDeleted = false)
    {
        $sql = "SELECT f.*, u.full_name, u.username, c.name AS category FROM found_items f 
                JOIN users u ON f.user_id = u.id
                JOIN categories c ON f.category_id = c.id WHERE 1=1";
        $params = [];

        if (!$includeDeleted) {
            $sql .= " AND f.is_deleted = 0";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (f.item_name LIKE :search OR f.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND f.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY f.date_posted DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFoundItemById($id)
    {
        $sql = "SELECT f.*, u.full_name, u.username, c.name AS category FROM found_items f 
                JOIN users u ON f.user_id = u.id
                JOIN categories c ON f.category_id = c.id WHERE f.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function updateFoundItemStatus($id, $status)
    {
        $sql = "UPDATE found_items SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    public function updateFoundItem($id, $item_name, $category_id, $description, $date_found, $location, $contact_email, $contact_phone)
    {
        $sql = "UPDATE found_items SET item_name = :item_name, category_id = :category_id, description = :description, 
                date_found = :date_found, location = :location, contact_email = :contact_email, contact_phone = :contact_phone 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':item_name' => $item_name,
            ':category_id' => $category_id,
            ':description' => $description,
            ':date_found' => $date_found,
            ':location' => $location,
            ':contact_email' => $contact_email,
            ':contact_phone' => $contact_phone
        ]);
    }

    public function deleteFoundItem($id)
    {
        $sql = "UPDATE found_items SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function hardDeleteFoundItem($id)
    {
        $sql = "DELETE FROM found_items WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function restoreFoundItem($id)
    {
        $sql = "UPDATE found_items SET is_deleted = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getUserFoundItems($user_id, $limit = 50)
    {
        $sql = "SELECT f.*, c.name AS category FROM found_items f
                JOIN categories c ON f.category_id = c.id
                WHERE f.user_id = :user_id ORDER BY f.date_posted DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Categories
    public function getCategories()
    {
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function getCategoryIdByName($name)
    {
        $sql = "SELECT id FROM categories WHERE name = :name AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':name' => $name]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Statistics
    public function getStats()
    {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total FROM lost_items WHERE status = 'active' AND is_deleted = 0";
        $stmt = $this->conn->query($sql);
        $stats['lost_active'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM found_items WHERE status = 'available' AND is_deleted = 0";
        $stmt = $this->conn->query($sql);
        $stats['found_available'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM lost_items WHERE status = 'claimed' AND is_deleted = 0";
        $stmt = $this->conn->query($sql);
        $stats['reunited'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM found_items WHERE status = 'claimed' AND is_deleted = 0";
        $stmt = $this->conn->query($sql);
        $stats['claimed'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
        $stmt = $this->conn->query($sql);
        $stats['total_users'] = $stmt->fetch()['total'];

        return $stats;
    }

    public function getRecentUsers($limit = 5)
    {
        $sql = "SELECT id, full_name, username, email, created_at, is_admin FROM users ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentActivity($limit = 8)
    {
        $sql = "SELECT a.*, u.full_name, u.username FROM activity_log a 
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentLostItemsForAdmin($limit = 5)
    {
        $sql = "SELECT l.*, u.full_name, c.name AS category FROM lost_items l 
                JOIN users u ON l.user_id = u.id
                JOIN categories c ON l.category_id = c.id
                ORDER BY (l.status = 'active') DESC, l.date_posted DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentFoundItemsForAdmin($limit = 5)
    {
        $sql = "SELECT f.*, u.full_name, c.name AS category FROM found_items f 
                JOIN users u ON f.user_id = u.id
                JOIN categories c ON f.category_id = c.id
                ORDER BY (f.status = 'available') DESC, f.date_posted DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoryBreakdown()
    {
        $sql = "SELECT c.name AS category, COUNT(*) as total FROM (
                    SELECT category_id FROM lost_items WHERE is_deleted = 0
                    UNION ALL
                    SELECT category_id FROM found_items WHERE is_deleted = 0
                ) as combined
                JOIN categories c ON combined.category_id = c.id
                GROUP BY c.id, c.name
                ORDER BY total DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    // Activity logging
    public function logActivity($user_id, $action, $details, $ip_address)
    {
        $sql = "INSERT INTO activity_log (user_id, action, details, ip_address) 
                VALUES (:user_id, :action, :details, :ip_address)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => $ip_address
        ]);
    }

    // Count methods for pagination
    public function countLostItems($filters = [], $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM lost_items l WHERE 1=1";
        $params = [];

        if (!$includeDeleted) {
            $sql .= " AND l.is_deleted = 0";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.item_name LIKE :search OR l.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND l.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = :status";
            $params[':status'] = $filters['status'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function countFoundItems($filters = [], $includeDeleted = false)
    {
        $sql = "SELECT COUNT(*) as total FROM found_items f WHERE 1=1";
        $params = [];

        if (!$includeDeleted) {
            $sql .= " AND f.is_deleted = 0";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (f.item_name LIKE :search OR f.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND f.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
}
