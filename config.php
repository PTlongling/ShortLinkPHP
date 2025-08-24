<?php
// config.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'short');
define('DB_USER', '123');
define('DB_PASS', '114514');
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
define('SHORT_URL_LENGTH', 6);
define('CAPTCHA_SESSION_KEY', 'captcha_code');
define('USER_LEVEL_FREE', 'free');
define('USER_LEVEL_PREMIUM', 'premium');
define('USER_LEVEL_ADMIN', 'admin');
define('RENEWAL_GRACE_PERIOD', 86400); // 24小时
// Cloudflare Turnstile 配置
define('TURNSTILE_SITEKEY', '0x4AAAAAABueDGdy80KLsZQC'); // 您的站点密钥
define('TURNSTILE_SECRET', '0x4AAAAAABueDMioJJCSzCt6o7J3ii2jTcM'); // 您的密钥
define('TURNSTILE_VERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

// 手动包含 Database 类
require_once 'classes/Database.php';

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建数据库实例
    $db = new Database($pdo);
} catch (PDOException $e) {
    // 如果是数据库不存在错误，重定向到安装页面
    if ($e->getCode() == 1049) {
        if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
            header('Location: install.php');
            exit;
        }
    } else {
        die("数据库连接失败: " . $e->getMessage());
    }
}

// 常用函数
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserLevel() {
    return $_SESSION['user_level'] ?? 'free';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function generateRandomString($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Cloudflare Turnstile 验证函数
function verifyTurnstile($token, $remoteIp = null) {
    $data = [
        'secret' => TURNSTILE_SECRET,
        'response' => $token
    ];
    
    if ($remoteIp) {
        $data['remoteip'] = $remoteIp;
    }
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 5 // 5秒超时
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents(TURNSTILE_VERIFY_URL, false, $context);
    
    if ($result === FALSE) {
        return ['success' => false, 'error' => '验证服务不可用'];
    }
    
    return json_decode($result, true);
}

// 获取客户端IP地址
function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'unknown';
}
?>