<?php
// dashboard.php
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
$links = $db->getUserLinks($user_id);
$link_count = $db->getUserLinkCount($user_id);
$max_links = $db->getUserLimit($user_id);

// 计算每个链接的续期剩余时间
foreach ($links as &$link) {
    if ($link['is_expired']) {
        // 计算过期时间与当前时间的差值（秒）
        $expired_time = strtotime($link['expires_at']);
        $current_time = time();
        $time_since_expiry = $current_time - $expired_time;
        
        // 计算剩余续期时间
        $link['renewal_remaining'] = max(0, RENEWAL_GRACE_PERIOD - $time_since_expiry);
    } else {
        $link['renewal_remaining'] = 0;
    }
}
unset($link); // 断开引用
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>控制台 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .progress {
            height: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .status-badge {
            font-size: 0.85rem;
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">控制台</a></li>
                    <?php if (getUserLevel() === USER_LEVEL_ADMIN): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">管理员后台</a></li>
                        <li class="nav-item"><a class="nav-link" href="banned.php">用户封禁</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">个人中心</a></li>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">链接管理</a>
                    <a href="profile.php" class="list-group-item list-group-item-action">个人资料</a>
                    <a href="index.php" class="list-group-item list-group-item-action">创建新链接</a>
                </div>
                
                <!-- 统计信息 -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">统计信息</h5>
                        <p>已创建链接: <?php echo $link_count; ?> / <?php echo $max_links; ?></p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo ($link_count / $max_links) * 100; ?>%">
                            </div>
                        </div>
                        <p class="small text-muted">
                            <?php 
                            $user_levels = [
                                USER_LEVEL_FREE => '免费用户',
                                USER_LEVEL_PREMIUM => '高级用户',
                                USER_LEVEL_ADMIN => '管理员'
                            ];
                            echo "您的等级: " . $user_levels[getUserLevel()];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">我的短链接</h5>
                        <a href="index.php" class="btn btn-primary btn-sm">创建新链接</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($links)): ?>
                            <div class="alert alert-info">
                                您还没有创建任何短链接，<a href="index.php">立即创建</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>短链接</th>
                                            <th>原始URL</th>
                                            <th>点击次数</th>
                                            <th>创建时间</th>
                                            <th>过期时间</th>
                                            <th>状态</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($links as $link): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo SITE_URL . '/?c=' . $link['short_code']; ?>" target="_blank">
                                                        <?php echo SITE_URL . '/?c=' . $link['short_code']; ?>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-secondary ms-1" 
                                                            onclick="copyToClipboard('<?php echo SITE_URL . '/?c=' . $link['short_code']; ?>')">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </td>
                                                <td class="text-truncate" style="max-width: 200px;">
                                                    <?php echo htmlspecialchars($link['long_url']); ?>
                                                </td>
                                                <td><?php echo $link['click_count']; ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($link['created_at'])); ?></td>
                                                <td>
                                                    <?php echo $link['expires_at'] ? date('Y-m-d', strtotime($link['expires_at'])) : '永不过期'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($link['is_expired']): ?>
                                                        <span class="badge bg-warning status-badge">已过期</span>
                                                        <?php if ($link['renewal_remaining'] > 0): ?>
                                                            <br><small class="text-muted">可续期: <?php echo gmdate("H:i:s", $link['renewal_remaining']); ?></small>
                                                        <?php else: ?>
                                                            <br><small class="text-muted">已超过续期期限</small>
                                                        <?php endif; ?>
                                                    <?php elseif (!$link['is_active']): ?>
                                                        <span class="badge bg-secondary status-badge">已禁用</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success status-badge">有效</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($link['is_expired'] && $link['renewal_remaining'] > 0): ?>
                                                        <button class="btn btn-sm btn-success mb-1" onclick="renewLink(<?php echo $link['id']; ?>)">续期</button>
                                                        <br>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteLink(<?php echo $link['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('已复制到剪贴板');
            });
        }
        
        function deleteLink(id) {
            if (confirm('确定要删除这个短链接吗？此操作不可恢复。')) {
                fetch('delete_link.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('删除失败: ' + data.message);
                    }
                });
            }
        }
        
        function renewLink(id) {
            if (confirm('确定要续期这个短链接吗？')) {
                fetch('renew_link.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('续期成功！');
                        location.reload();
                    } else {
                        alert('续期失败: ' + data.message);
                    }
                });
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>