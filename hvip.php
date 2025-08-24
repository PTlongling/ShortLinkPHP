<?php
// hvip.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'short');
define('DB_USER', '123');
define('DB_PASS', '114514');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);

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
    
    // 获取用户信息
    $user = $db->getUserById($link['user_id']);
    
    // 获取用户自定义等待时间（1-10秒），默认为3秒
    $wait_time = isset($user['redirect_delay']) ? max(1, min(10, (int)$user['redirect_delay'])) : 3;
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
    <title>跳转中 - 高级短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0c0c0c 0%, #2a2a2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .gold-gradient {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .countdown-container {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #BF953F;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(191, 149, 63, 0.3);
            padding: 2.5rem;
            max-width: 700px;
            width: 100%;
        }
        .countdown-number {
            font-size: 5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            text-shadow: 0 0 10px rgba(191, 149, 63, 0.5);
        }
        .progress {
            height: 12px;
            border-radius: 6px;
            margin: 2rem 0;
            background: rgba(255, 255, 255, 0.1);
        }
        .progress-bar {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            box-shadow: 0 0 10px rgba(191, 149, 63, 0.5);
        }
        .btn-gold {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            border: none;
            color: #000;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 0 15px rgba(191, 149, 63, 0.5);
            transition: all 0.3s;
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #AA771C 0%, #FBF5B7 25%, #B38728 50%, #FCF6BA 75%, #BF953F 100%);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(191, 149, 63, 0.7);
        }
        .website-card {
            border-left: 4px solid #BF953F;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1.2rem;
            margin: 1.5rem 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .vip-badge {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            color: #000;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .gold-border {
            border: 1px solid #BF953F;
        }
        .gold-text {
            color: #BF953F;
        }
    </style>
</head>
<body>
    <div class="countdown-container gold-border">
        <div class="text-center">
            <span class="vip-badge">高级用户专属</span>
            <h1 class="gold-gradient mb-4">短链接服务</h1>
            <h2 class="mb-4 gold-text">正在跳转到目标网站</h2>
            
            <div class="website-card">
                <h5 class="gold-text">目标网站</h5>
                <p class="text-truncate"><?php echo htmlspecialchars($link['long_url']); ?></p>
            </div>
            
            <div class="website-card">
                <h5 class="gold-text">我的短链接首页</h5>
                <p><?php echo SITE_URL; ?></p>
            </div>
            
            <div class="countdown-number" id="countdown"><?php echo $wait_time; ?></div>
            <p class="gold-text">秒后自动跳转</p>
            
            <div class="progress">
                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 100%"></div>
            </div>
            
            <p class="text-muted mb-4">高级用户可以自定义等待时间（1-10秒）</p>
            
            <a href="<?php echo htmlspecialchars($link['long_url']); ?>" class="btn btn-gold btn-lg" id="skip-button">
                立即跳转
            </a>
        </div>
    </div>

    <script>
        // 倒计时功能
        let countdown = <?php echo $wait_time; ?>;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');
        const skipButton = document.getElementById('skip-button');
        const totalTime = <?php echo $wait_time; ?>;
        
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