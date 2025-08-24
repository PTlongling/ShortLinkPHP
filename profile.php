<?php
// profile.php
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

function getUserLevel() {
    return $_SESSION['user_level'] ?? 'free';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// 检查是否已登录
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = $db->getUserById($user_id);

$error = '';
$success = '';

// 处理密码更改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "请填写所有密码字段";
    } elseif ($new_password !== $confirm_password) {
        $error = "新密码和确认密码不匹配";
    } elseif (strlen($new_password) < 6) {
        $error = "新密码长度至少为6位";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "当前密码不正确";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            $success = "密码更新成功";
        } else {
            $error = "密码更新失败，请稍后再试";
        }
    }
}

// 处理等待时间更改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redirect_delay'])) {
    $redirect_delay = intval($_POST['redirect_delay']);
    
    // 验证输入
    if ($redirect_delay < 1 || $redirect_delay > 10) {
        $error = "等待时间必须在1-10秒之间";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET redirect_delay = ? WHERE id = ?");
        
        if ($stmt->execute([$redirect_delay, $user_id])) {
            $success = "等待时间设置成功";
            // 更新用户信息
            $user = $db->getUserById($user_id);
        } else {
            $error = "等待时间设置失败，请稍后再试";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .gold-gradient {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-gold {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            border: none;
            color: #000;
            font-weight: bold;
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #AA771C 0%, #FBF5B7 25%, #B38728 50%, #FCF6BA 75%, #BF953F 100%);
            color: #000;
        }
        .vip-badge {
            background: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            color: #000;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
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
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">控制台</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">个人中心</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">退出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <!-- 侧边栏 -->
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">链接管理</a>
                    <a href="profile.php" class="list-group-item list-group-item-action active">个人资料</a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">个人资料</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>账户信息</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="30%">用户名:</th>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>邮箱:</th>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>用户等级:</th>
                                        <td>
                                            <?php 
                                            $level_names = [
                                                'free' => '免费用户',
                                                'premium' => '高级用户',
                                                'admin' => '管理员'
                                            ];
                                            echo $level_names[$user['user_level']]; 
                                            ?>
                                            <?php if ($user['user_level'] !== 'free'): ?>
                                                <span class="vip-badge ms-2">VIP</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>注册时间:</th>
                                        <td><?php echo date('Y-m-d H:i', strtotime($user['registration_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>最后登录:</th>
                                        <td>
                                            <?php 
                                            echo $user['last_login'] 
                                                ? date('Y-m-d H:i', strtotime($user['last_login'])) 
                                                : '从未登录'; 
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>账户状态:</th>
                                        <td>
                                            <?php 
                                            $status_names = [
                                                'active' => '活跃',
                                                'inactive' => '未激活',
                                                'banned' => '已封禁'
                                            ];
                                            $status_classes = [
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'banned' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_classes[$user['status']]; ?>">
                                                <?php echo $status_names[$user['status']]; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php if (getUserLevel() !== USER_LEVEL_FREE): ?>
                            <h6>跳转等待时间设置</h6>
                            <form method="post" class="mb-4">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="redirect_delay" class="form-label">跳转等待时间（1-10秒）</label>
                                        <input type="number" class="form-control" id="redirect_delay" name="redirect_delay" 
                                               min="1" max="10" value="<?php echo isset($user['redirect_delay']) ? $user['redirect_delay'] : 3; ?>" required>
                                        <div class="form-text">高级用户可以设置访问者需要等待的时间</div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-gold">保存设置</button>
                            </form>
                        <?php endif; ?>
                        
                        <h6>更改密码</h6>
                        <form method="post" class="mb-4">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="current_password" class="form-label">当前密码</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">新密码</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">更改密码</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>