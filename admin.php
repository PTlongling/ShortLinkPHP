<?php
require_once 'config.php';

// 检查是否已登录且是管理员
if (!isLoggedIn() || getUserLevel() !== USER_LEVEL_ADMIN) {
    redirect('index.php');
}

$db = new Database($pdo);

// 分页设置
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$total_links = $db->getTotalLinksCount();
$total_pages = ceil($total_links / $per_page);

// 获取链接列表
$links = $db->getAllLinks($page, $per_page);

// 处理管理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['link_id'])) {
        $link_id = intval($_POST['link_id']);
        
        switch ($_POST['action']) {
            case 'disable':
                $db->toggleLinkStatus($link_id, false);
                $success = "链接已禁用";
                break;
            case 'enable':
                $db->toggleLinkStatus($link_id, true);
                $success = "链接已启用";
                break;
            case 'delete':
                $db->adminDeleteLink($link_id);
                $success = "链接已删除";
                break;
        }
        
        // 刷新页面
        redirect('admin.php?page=' . $page);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员后台 - 短链接服务</title>
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
                    <li class="nav-item"><a class="nav-link active" href="admin.php">管理员后台</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">退出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>管理员后台</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">所有短链接</h5>
            </div>
            <div class="card-body">
                <?php if (empty($links)): ?>
                    <div class="alert alert-info">没有短链接</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>短代码</th>
                                    <th>原始URL</th>
                                    <th>用户</th>
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
                                        <td><?php echo $link['id']; ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL . '/?c=' . $link['short_code']; ?>" target="_blank">
                                                <?php echo $link['short_code']; ?>
                                            </a>
                                        </td>
                                        <td class="text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($link['long_url']); ?>
                                        </td>
                                        <td><?php echo $link['username']; ?></td>
                                        <td><?php echo $link['click_count']; ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($link['created_at'])); ?></td>
                                        <td>
                                            <?php echo $link['expires_at'] ? date('Y-m-d H:i', strtotime($link['expires_at'])) : '永不过期'; ?>
                                        </td>
                                        <td>
                                            <?php if ($link['is_active']): ?>
                                                <span class="badge bg-success">有效</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">禁用</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                                <?php if ($link['is_active']): ?>
                                                    <button type="submit" name="action" value="disable" class="btn btn-sm btn-warning">禁用</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="enable" class="btn btn-sm btn-success">启用</button>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除这个链接吗？')">删除</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 分页 -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="admin.php?page=<?php echo $page - 1; ?>">上一页</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="admin.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="admin.php?page=<?php echo $page + 1; ?>">下一页</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>