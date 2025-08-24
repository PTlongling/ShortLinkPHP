<?php
// 启动会话
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
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_BANNED', 'banned');
define('RENEWAL_GRACE_PERIOD', 86400); // 24小时

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

// 处理短链接重定向
// 处理短链接重定向
if (isset($_GET['c'])) {
    $code = $_GET['c'];
    $link = $db->getShortLinkByCode($code);
    
    if ($link) {
        // 检查链接所属用户是否被封禁
        $user = $db->getUserById($link['user_id']);
        
        if ($user && $user['status'] === 'banned') {
            // 用户已被封禁，跳转到封禁提示页面
            header("Location: showban.php?c=" . $code);
            exit;
        }
        
        if ($link['is_active']) {
            // 检查链接是否过期
            if ($link['expires_at'] && strtotime($link['expires_at']) < time()) {
                // 显示过期页面
                include 'expired.php';
                exit;
            }
            
            // 根据用户等级跳转到不同的等待页面
            if ($user['user_level'] === USER_LEVEL_FREE) {
                // 免费用户，跳转到免费等待页面
                header("Location: free.php?c=" . $code);
                exit;
            } else {
                // 高级用户和管理员，跳转到高级等待页面
                header("Location: hvip.php?c=" . $code);
                exit;
            }
        } else {
            // 链接已被禁用
            include 'disabled.php';
            exit;
        }
    } else {
        // 显示自定义404页面
        include 'error-404.php';
        exit;
    }
}

// 处理短链接创建
$short_url = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_url'])) {
    if (!isLoggedIn()) {
        $error = "请先登录再创建短链接";
    } else {
        // 检查用户是否被封禁
        $user = $db->getUserById($_SESSION['user_id']);
        if ($user['status'] === USER_STATUS_BANNED) {
            $error = "您的账户已被封禁，无法创建短链接";
        } else {
            $long_url = filter_var($_POST['long_url'], FILTER_SANITIZE_URL);
            
            // 验证URL格式
            if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
                $error = "请输入有效的URL地址";
            } else {
                // 处理自定义代码
                $custom_code = !empty($_POST['custom_code']) ? $_POST['custom_code'] : null;
                
                // 验证自定义代码权限
                if ($custom_code) {
                    if (getUserLevel() !== USER_LEVEL_PREMIUM && getUserLevel() !== USER_LEVEL_ADMIN) {
                        $error = "您没有权限使用自定义短代码功能";
                    } elseif (!preg_match('/^[A-Za-z0-9]{2,10}$/', $custom_code)) {
                        $error = "自定义代码只能包含字母和数字，长度2-10个字符";
                    } elseif (!$db->isCustomCodeAvailable($custom_code)) {
                        $error = "该自定义代码已被使用，请选择其他代码";
                    }
                }
                
                if (!isset($error)) {
                    // 检查用户是否超过限制
                    $user_id = $_SESSION['user_id'];
                    $link_count = $db->getUserLinkCount($user_id);
                    $max_links = $db->getUserLimit($user_id);
                    
                    if ($link_count >= $max_links) {
                        $error = "您已达到最大短链接数量限制 ($max_links)";
                    } else {
                        $code = $db->createShortLink($long_url, $user_id, $custom_code);
                        
                        if ($code) {
                            $short_url = SITE_URL . '/?c=' . $code;
                            $success = "短链接创建成功！";
                        } else {
                            $error = "创建短链接失败，请稍后再试";
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短链接生成器</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(120deg, #007bff, #6610f2);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }
        .short-url-result {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #007bff;
        }
        .user-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">短链接服务</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">关于</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">控制台</a></li>
                        <?php if (getUserLevel() === USER_LEVEL_ADMIN): ?>
                            <li class="nav-item"><a class="nav-link" href="admin.php">链接管理</a></li>
                            <li class="nav-item"><a class="nav-link" href="banned.php">用户封禁</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="profile.php">个人中心</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">退出</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">登录</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">注册</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 英雄区域 -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4">快速生成短链接</h1>
            <p class="lead">简单、快速、可靠的URL缩短服务</p>
            
            <?php if (isLoggedIn()): ?>
                <div class="mt-3">
                    <span class="badge bg-light text-dark">
                        <?php 
                        $user_levels = [
                            USER_LEVEL_FREE => '免费用户',
                            USER_LEVEL_PREMIUM => '高级用户',
                            USER_LEVEL_ADMIN => '管理员'
                        ];
                        echo "欢迎, " . $_SESSION['username'] . " (" . $user_levels[getUserLevel()] . ")";
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="container">
        <!-- 短链接生成表单 -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm position-relative">
                    <?php if (isLoggedIn() && (getUserLevel() === USER_LEVEL_PREMIUM || getUserLevel() === USER_LEVEL_ADMIN)): ?>
                        <span class="user-badge badge bg-success">高级功能已启用</span>
                    <?php endif; ?>
                    
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">生成短链接</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <div class="short-url-result">
                                    <a href="<?php echo $short_url; ?>" target="_blank" id="shortUrl"><?php echo $short_url; ?></a>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard()">复制</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="long_url" class="form-label">长网址</label>
                                <input type="url" class="form-control" id="long_url" name="long_url" placeholder="https://example.com/very-long-url" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="custom_code" class="form-label">自定义短代码（可选）</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo SITE_URL; ?>/?c=</span>
                                    <input type="text" class="form-control" id="custom_code" name="custom_code" 
                                           placeholder="自定义部分" pattern="[A-Za-z0-9]{2,10}"
                                           <?php if (!isLoggedIn() || (getUserLevel() !== USER_LEVEL_PREMIUM && getUserLevel() !== USER_LEVEL_ADMIN)) echo 'disabled'; ?>>
                                </div>
                                <div class="form-text">
                                    <?php if (isLoggedIn() && (getUserLevel() === USER_LEVEL_PREMIUM || getUserLevel() === USER_LEVEL_ADMIN)): ?>
                                        只能包含字母和数字，2-10个字符
                                    <?php else: ?>
                                        <span class="text-danger">自定义短代码功能仅限高级用户使用</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!isLoggedIn()): ?>
                                <div class="alert alert-info">
                                    您尚未登录，<a href="login.php">登录</a>后可以管理您的短链接。
                                </div>
                            <?php else: ?>
                                <?php
                                $user_id = $_SESSION['user_id'];
                                $link_count = $db->getUserLinkCount($user_id);
                                $max_links = $db->getUserLimit($user_id);
                                $percentage = ($link_count / $max_links) * 100;
                                ?>
                                <div class="mb-3">
                                    <label class="form-label">链接使用情况</label>
                                    <div class="progress mb-2">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $link_count; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?php echo $max_links; ?>">
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        已创建 <?php echo $link_count; ?> / <?php echo $max_links; ?> 个链接
                                        (<?php echo round($percentage); ?>%)
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">生成短链接</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 功能特点 -->
        <div class="row mt-5">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <h4>快速缩短</h4>
                <p>只需粘贴长链接，一键生成短链接</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h4>点击统计</h4>
                <p>跟踪每个短链接的点击次数和访问数据</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h4>安全可靠</h4>
                <p>采用安全算法，保障您的链接安全</p>
            </div>
        </div>

        <!-- 用户等级说明 -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">用户等级与功能</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>功能</th>
                                        <th>免费用户</th>
                                        <th>高级用户</th>
                                        <th>管理员</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>最大链接数</td>
                                        <td>100</td>
                                        <td>1000</td>
                                        <td>65535</td>
                                    </tr>
                                    <tr>
                                        <td>链接有效期</td>
                                        <td>30天</td>
                                        <td>365天</td>
                                        <td>10年</td>
                                    </tr>
                                    <tr>
                                        <td>自定义短代码</td>
                                        <td><i class="bi bi-x-circle text-danger"></i></td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td>链接续期功能</td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td>管理后台</td>
                                        <td><i class="bi bi-x-circle text-danger"></i></td>
                                        <td><i class="bi bi-x-circle text-danger"></i></td>
                                        <td><i class="bi bi-check-circle text-success"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p>&copy; 2023 短链接服务. 保留所有权利.</p>
        </div>
    </footer>

    <script>
        function copyToClipboard() {
            const shortUrl = document.getElementById('shortUrl');
            const textArea = document.createElement('textarea');
            textArea.value = shortUrl.href;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('已复制到剪贴板');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>