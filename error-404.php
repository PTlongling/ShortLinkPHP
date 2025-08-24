<?php
// 获取短代码
$code = isset($_GET['c']) ? $_GET['c'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短链接不存在 - 短链接服务</title>
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
            <h1 class="display-1 text-muted">404</h1>
            <h2>短链接不存在</h2>
            <p class="lead">
                您访问的短链接(代码: <code><?php echo htmlspecialchars($code); ?></code> )不存在或已被删除。
            </p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary">返回首页</a>
                <a href="dashboard.php" class="btn btn-outline-secondary">我的控制台</a>
            </div>
        </div>
    </div>
</body>
</html>