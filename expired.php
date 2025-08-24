<?php
// 获取短代码
$code = isset($_GET['c']) ? $_GET['c'] : '';

// 尝试获取链接信息
$link = null;
if (!empty($code)) {
    $stmt = $pdo->prepare("SELECT * FROM short_links WHERE short_code = ?");
    $stmt->execute([$code]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 计算剩余删除时间（过期后7天删除）
$delete_in = null;
if ($link && $link['expires_at']) {
    $delete_time = strtotime($link['expires_at']) + (7 * 24 * 60 * 60); // 7天后删除
    $delete_in = max(0, $delete_time - time());
    $delete_days = ceil($delete_in / (24 * 60 * 60));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短链接已过期 - 短链接服务</title>
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
            <h1 class="display-1 text-warning"><i class="bi bi-clock-history"></i></h1>
            <h2>短链接已过期</h2>
            <p class="lead">
                您访问的短链接 <code><?php echo htmlspecialchars($code); ?></code> 已过期。
            </p>
            
            <?php if ($delete_in && $delete_in > 0): ?>
                <div class="alert alert-info mt-3">
                    此链接将在 <?php echo $delete_days; ?> 天后被自动删除。
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn() && $link && $link['user_id'] == $_SESSION['user_id']): ?>
                <?php
                // 检查是否在续期宽限期内
                $expired_time = time() - strtotime($link['expires_at']);
                $can_renew = $expired_time < RENEWAL_GRACE_PERIOD;
                ?>
                
                <?php if ($can_renew): ?>
                    <div class="mt-4">
                        <p>您可以在24小时内续期此链接</p>
                        <a href="dashboard.php" class="btn btn-success">前往控制台续期</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4">
                        <p class="text-muted">续期宽限期已过，无法再续期此链接</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary">返回首页</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-outline-secondary">我的控制台</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary">登录</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>