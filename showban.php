<?php
// showban.php
session_start();

// 获取短代码
$code = isset($_GET['c']) ? $_GET['c'] : '';

// 尝试获取链接信息
$link = null;
$user = null;
if (!empty($code)) {
    // 包含数据库配置
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'short');
    define('DB_USER', '123');
    define('DB_PASS', '114514');
    
    require_once 'classes/Database.php';
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $db = new Database($pdo);
        
        // 获取链接信息
        $stmt = $pdo->prepare("SELECT * FROM short_links WHERE short_code = ?");
        $stmt->execute([$code]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 如果找到链接，获取用户信息
        if ($link) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$link['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // 忽略数据库错误，只显示通用提示
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户已被封禁 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .error-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="text-center">
            <h1 class="display-1 text-danger"><i class="bi bi-slash-circle"></i></h1>
            <h2>用户已被封禁</h2>
            <p class="lead">
                您访问的短链接 <code><?php echo htmlspecialchars($code); ?></code> 所属用户已被封禁。
            </p>
            
            <?php if ($user): ?>
                <div class="alert alert-warning mt-3">
                    用户 <strong><?php echo htmlspecialchars($user['username']); ?></strong> 因违反服务条款已被封禁。
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary">返回首页</a>
                <a href="contact.php" class="btn btn-outline-secondary">联系管理员</a>
            </div>
        </div>
    </div>
</body>
</html>