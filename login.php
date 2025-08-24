<?php
// login.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'short');
define('DB_USER', '123');
define('DB_PASS', '114514');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('SHORT_URL_LENGTH', 6);
define('CAPTCHA_SESSION_KEY', 'captcha_code');
define('USER_LEVEL_FREE', 'free');
define('USER_LEVEL_PREMIUM', 'premium');
define('USER_LEVEL_ADMIN', 'admin');

// 手动包含 Database 类
require_once 'classes/Database.php';

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建数据库实例
    $db = new Database($pdo);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 常用函数
function isLoggedIn() {
    return isset($_SESSION['user_id']);
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

// 如果已登录，重定向到首页
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    
    // 验证验证码
    if (!$db->verifyCaptcha(session_id(), $captcha)) {
        $error = "验证码错误";
    } else {
        $user = $db->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                // 登录成功
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_level'] = $user['user_level'];
                
                // 更新最后登录时间
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                redirect('dashboard.php');
            } else {
                $error = "账户已被禁用，请联系管理员";
            }
        } else {
            $error = "用户名或密码错误";
        }
    }
}

// 生成验证码
$captcha_code = generateRandomString(4);
$db->storeCaptcha(session_id(), $captcha_code);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">用户登录</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">用户名</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">密码</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="captcha" class="form-label">验证码</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="captcha" name="captcha" placeholder="请输入验证码" required>
                                    <span class="input-group-text" style="font-family: monospace; font-weight: bold; letter-spacing: 3px;">
                                        <?php echo $captcha_code; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">登录</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="register.php">还没有账号？立即注册</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>