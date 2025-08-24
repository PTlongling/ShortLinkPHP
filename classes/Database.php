<?php
class Database {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createUser($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password]);
    }
    
    public function getShortLinkByCode($code) {
        $stmt = $this->pdo->prepare("SELECT * FROM short_links WHERE short_code = ? AND is_active = TRUE AND (expires_at IS NULL OR expires_at > NOW())");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createShortLink($long_url, $user_id, $custom_code = null) {
        $code = $custom_code ?: $this->generateUniqueCode();
        
        // 获取用户等级限制
        $stmt = $this->pdo->prepare("
            SELECT ul.link_validity_days 
            FROM user_limits ul 
            JOIN users u ON u.user_level = ul.user_level 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $limit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $expires_at = date('Y-m-d H:i:s', strtotime('+' . ($limit['link_validity_days'] ?? 30) . ' days'));
        
        $stmt = $this->pdo->prepare("
            INSERT INTO short_links (short_code, long_url, user_id, expires_at) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([$code, $long_url, $user_id, $expires_at]) ? $code : false;
    }
    
    public function getUserLinks($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT *, 
                   (expires_at < NOW()) as is_expired,
                   (click_count) as clicks
            FROM short_links 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function incrementClickCount($id) {
        $stmt = $this->pdo->prepare("UPDATE short_links SET click_count = click_count + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public function getUserLinkCount($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM short_links WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['count'] : 0;
    }
    
    public function getUserLimit($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT ul.max_links 
            FROM user_limits ul 
            JOIN users u ON u.user_level = ul.user_level 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['max_links'] : 10;
    }
    
    public function deleteLink($id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM short_links WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $user_id]);
    }
    
    // 禁用/启用短链接
    public function toggleLinkStatus($id, $status) {
        // 确保状态值是整数 (0 或 1)
        $status_int = $status ? 1 : 0;
        $stmt = $this->pdo->prepare("UPDATE short_links SET is_active = ? WHERE id = ?");
        return $stmt->execute([$status_int, $id]);
    }
    
    // 删除短链接（管理员用）
    public function adminDeleteLink($id) {
        $stmt = $this->pdo->prepare("DELETE FROM short_links WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // 续期短链接
    public function renewLink($id, $user_id) {
        // 检查链接是否属于用户且处于可续期状态
        $stmt = $this->pdo->prepare("
            SELECT sl.*, 
                   (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(sl.expires_at)) as seconds_since_expiry
            FROM short_links sl 
            WHERE sl.id = ? AND sl.user_id = ?
        ");
        $stmt->execute([$id, $user_id]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link) {
            return false; // 链接不存在或不属于用户
        }
        
        // 检查是否在续期宽限期内
        if ($link['seconds_since_expiry'] > RENEWAL_GRACE_PERIOD) {
            return false; // 超过续期宽限期
        }
        
        // 获取用户等级对应的有效期
        $validity_days = $this->getUserValidityDays($user_id);
        
        // 更新过期时间
        $new_expires_at = date('Y-m-d H:i:s', strtotime("+$validity_days days"));
        $stmt = $this->pdo->prepare("UPDATE short_links SET expires_at = ?, is_active = TRUE WHERE id = ?");
        return $stmt->execute([$new_expires_at, $id]);
    }
    
    // 获取用户的有效期天数
    public function getUserValidityDays($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT ul.link_validity_days 
            FROM user_limits ul 
            JOIN users u ON u.user_level = ul.user_level 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['link_validity_days'] : 30;
    }
    
    // 检查自定义代码是否可用
    public function isCustomCodeAvailable($code) {
        $stmt = $this->pdo->prepare("SELECT id FROM short_links WHERE short_code = ?");
        $stmt->execute([$code]);
        return !$stmt->fetch();
    }
    
    public function storeCaptcha($session_id, $code) {
        // 先删除旧的验证码
        $this->pdo->prepare("DELETE FROM captchas WHERE session_id = ?")->execute([$session_id]);
        
        $stmt = $this->pdo->prepare("INSERT INTO captchas (session_id, captcha_text) VALUES (?, ?)");
        return $stmt->execute([$session_id, $code]);
    }
    
    public function verifyCaptcha($session_id, $code) {
        // 清除过期验证码（5分钟）
        $this->pdo->exec("DELETE FROM captchas WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        
        $stmt = $this->pdo->prepare("SELECT id FROM captchas WHERE session_id = ? AND captcha_text = ?");
        $stmt->execute([$session_id, $code]);
        
        if ($stmt->fetch()) {
            // 验证成功后删除验证码
            $this->pdo->prepare("DELETE FROM captchas WHERE session_id = ?")->execute([$session_id]);
            return true;
        }
        
        return false;
    }
    
    public function updateLastLogin($user_id) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$user_id]);
    }
    
    // 获取所有用户（管理员用）
    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users ORDER BY registration_date DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取所有短链接（管理员用）
    public function getAllLinks($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        $stmt = $this->pdo->prepare("
            SELECT sl.*, u.username, u.email 
            FROM short_links sl 
            JOIN users u ON sl.user_id = u.id 
            ORDER BY sl.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 获取短链接总数（分页用）
    public function getTotalLinksCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM short_links");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    private function generateUniqueCode() {
        $code = generateRandomString(SHORT_URL_LENGTH);
        
        // 确保代码唯一
        $stmt = $this->pdo->prepare("SELECT id FROM short_links WHERE short_code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetch()) {
            return $this->generateUniqueCode(); // 递归直到找到唯一代码
        }
        
        return $code;
    }
}
?>