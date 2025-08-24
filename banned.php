<?php
// banned.php
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

// 检查是否已登录且是管理员
if (!isLoggedIn() || getUserLevel() !== USER_LEVEL_ADMIN) {
    redirect('index.php');
}

// 获取所有用户
$users = $db->getAllUsers();

// 处理封禁/解封操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['action'])) {
        $user_id = intval($_POST['user_id']);
        
        // 获取当前用户信息
        $current_user = $db->getUserById($_SESSION['user_id']);
        $target_user = $db->getUserById($user_id);
        
        // 不能封禁自己
        if ($user_id == $_SESSION['user_id']) {
            $error = "不能封禁自己";
        }
        // 不能封禁其他管理员
        elseif ($target_user['user_level'] === USER_LEVEL_ADMIN) {
            $error = "不能封禁其他管理员";
        }
        else {
            if ($_POST['action'] === 'ban') {
                // 封禁用户
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                if ($stmt->execute([USER_STATUS_BANNED, $user_id])) {
                    $success = "用户已封禁";
                    
                    // 禁用该用户的所有短链接
                    $stmt = $pdo->prepare("UPDATE short_links SET is_active = FALSE WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                } else {
                    $error = "封禁用户失败";
                }
            } elseif ($_POST['action'] === 'unban') {
                // 解封用户
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                if ($stmt->execute([USER_STATUS_ACTIVE, $user_id])) {
                    $success = "用户已解封";
                } else {
                    $error = "解封用户失败";
                }
            }
            
            // 刷新页面
            redirect('banned.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户封禁管理 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
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
                    <li class="nav-item"><a class="nav-link" href="admin.php">链接管理</a></li>
                    <li class="nav-item"><a class="nav-link active" href="banned.php">用户封禁</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">退出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>用户封禁管理</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">用户列表</h5>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">没有用户</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户名</th>
                                    <th>邮箱</th>
                                    <th>用户等级</th>
                                    <th>注册时间</th>
                                    <th>最后登录</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php 
                                            $level_names = [
                                                USER_LEVEL_FREE => '免费用户',
                                                USER_LEVEL_PREMIUM => '高级用户',
                                                USER_LEVEL_ADMIN => '管理员'
                                            ];
                                            echo $level_names[$user['user_level']]; 
                                            ?>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($user['registration_date'])); ?></td>
                                        <td>
                                            <?php 
                                            echo $user['last_login'] 
                                                ? date('Y-m-d H:i', strtotime($user['last_login'])) 
                                                : '从未登录'; 
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_names = [
                                                USER_STATUS_ACTIVE => '活跃',
                                                USER_STATUS_INACTIVE => '未激活',
                                                USER_STATUS_BANNED => '已封禁'
                                            ];
                                            $status_classes = [
                                                USER_STATUS_ACTIVE => 'success',
                                                USER_STATUS_INACTIVE => 'secondary',
                                                USER_STATUS_BANNED => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_classes[$user['status']]; ?>">
                                                <?php echo $status_names[$user['status']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id'] && $user['user_level'] !== USER_LEVEL_ADMIN): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <?php if ($user['status'] !== USER_STATUS_BANNED): ?>
                                                        <button type="submit" name="action" value="ban" class="btn btn-sm btn-danger" onclick="return confirm('确定要封禁用户 <?php echo htmlspecialchars($user['username']); ?> 吗？')">封禁</button>
                                                    <?php else: ?>
                                                        <button type="submit" name="action" value="unban" class="btn btn-sm btn-success" onclick="return confirm('确定要解封用户 <?php echo htmlspecialchars($user['username']); ?> 吗？')">解封</button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">不可操作</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>