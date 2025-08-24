<?php
// free.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'short');
define('DB_USER', '123');
define('DB_PASS', '114514');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('WAIT_TIME', 5); // 免费用户固定等待5秒

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

// 获取短代码
$code = isset($_GET['c']) ? $_GET['c'] : '';

// 获取链接信息
$link = null;
if (!empty($code)) {
    $link = $db->getShortLinkByCode($code);
    
    // 如果链接不存在或已禁用，跳转到404页面
    if (!$link || !$link['is_active']) {
        header("Location: error-404.php?c=" . $code);
        exit;
    }
    
    // 检查链接是否过期
    if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
        header("Location: expired.php?c=" . $code);
        exit;
    }
    
    // 增加点击计数
    $db->incrementClickCount($link['id']);
} else {
    // 没有提供短代码，跳转到首页
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>跳转中 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .countdown-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
        }
        .countdown-number {
            font-size: 4rem;
            font-weight: bold;
            color: #007bff;
            line-height: 1;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
            margin: 1.5rem 0;
        }
        .progress-bar {
            background-color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .website-card {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .free-badge {
            background-color: #6c757d;
            color: #fff;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="countdown-container">
        <div class="text-center">
            <span class="free-badge">免费用户</span>
            <h1 class="text-primary mb-4">短链接服务</h1>
            <h2 class="mb-4">正在跳转到目标网站</h2>
            
            <div class="website-card">
                <h5>目标网站</h5>
                <p class="text-truncate"><?php echo htmlspecialchars($link['long_url']); ?></p>
            </div>
            
            <div class="website-card">
                <h5>我的短链接首页</h5>
                <p><?php echo SITE_URL; ?></p>
            </div>
            
            <div class="countdown-number" id="countdown"><?php echo WAIT_TIME; ?></div>
            <p>秒后自动跳转</p>
            
            <div class="progress">
                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 100%"></div>
            </div>
            
            <p class="text-muted mb-4">免费用户需要等待 <?php echo WAIT_TIME; ?> 秒后才能跳转</p>
            
            <a href="<?php echo htmlspecialchars($link['long_url']); ?>" class="btn btn-primary btn-lg" id="skip-button">
                立即跳转
            </a>
        </div>
    </div>

    <script>
        // 倒计时功能
        let countdown = <?php echo WAIT_TIME; ?>;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');
        const skipButton = document.getElementById('skip-button');
        const totalTime = <?php echo WAIT_TIME; ?>;
        
        function updateCountdown() {
            countdown--;
            countdownElement.textContent = countdown;
            
            // 更新进度条
            const progress = (countdown / totalTime) * 100;
            progressBar.style.width = progress + '%';
            
            if (countdown <= 0) {
                // 倒计时结束，自动跳转
                window.location.href = "<?php echo htmlspecialchars($link['long_url']); ?>";
            } else {
                // 继续倒计时
                setTimeout(updateCountdown, 1000);
            }
        }
        
        // 启动倒计时
        setTimeout(updateCountdown, 1000);
    </script>
</body>
</html>