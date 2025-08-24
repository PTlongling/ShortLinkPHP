<?php
// contact.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'shortlink_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('SITE_EMAIL', 'service@wanweitool.cn');
define('ADMIN_EMAIL', 'service@wanweitool.cn');

// 手动包含 Database 类
require_once 'classes/Database.php';

// 创建数据库连接
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建数据库实例
    $db = new Database($pdo);
} catch (PDOException $e) {
    // 如果数据库连接失败，仍然显示页面但不提供数据库相关功能
    $db = null;
}

// 常用函数
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserLevel() {
    return $_SESSION['user_level'] ?? 'free';
}

// 处理邮件发送
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // 验证输入
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "请填写所有必填字段";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "请输入有效的邮箱地址";
    } else {
        // 准备邮件内容
        $email_subject = "网站联系表单: " . $subject;
        $email_body = "
            <html>
            <head>
                <title>网站联系表单</title>
            </head>
            <body>
                <h2>网站联系表单消息</h2>
                <p><strong>发件人:</strong> $name</p>
                <p><strong>邮箱:</strong> $email</p>
                <p><strong>主题:</strong> $subject</p>
                <p><strong>消息内容:</strong></p>
                <div style='border: 1px solid #eee; padding: 10px; margin: 10px 0;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <p><strong>发送时间:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>IP地址:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
            </body>
            </html>
        ";
        
        // 设置邮件头
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SITE_EMAIL . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // 发送邮件
        if (mail(ADMIN_EMAIL, $email_subject, $email_body, $headers)) {
            $success = "您的消息已成功发送，我们将尽快回复您！";
            
            // 清空表单字段
            $_POST = array();
        } else {
            $error = "发送邮件时出现错误，请稍后再试或直接发送邮件至 " . ADMIN_EMAIL;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>联系我们 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .contact-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 2rem;
        }
        .contact-icon {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 1rem;
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
                    <li class="nav-item"><a class="nav-link" href="about.php">关于</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">联系我们</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">控制台</a></li>
                        <?php if (getUserLevel() === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin.php">管理员后台</a></li>
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

    <div class="container mt-4 mb-5">
        <h2 class="mb-4">联系我们</h2>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">发送消息</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">您的姓名 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">您的邮箱 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">主题 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">消息内容 <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">发送消息</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-info">
                    <div class="text-center mb-4">
                        <div class="contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <h4>联系信息</h4>
                        <p class="text-muted">我们很乐意听取您的意见和建议</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5><i class="bi bi-envelope me-2"></i> 邮箱</h5>
                        <p><a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a></p>
                    </div>
                    
                    <div class="mb-3">
                        <h5><i class="bi bi-clock me-2"></i> 响应时间</h5>
                        <p>我们会在24小时内回复您的邮件</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5><i class="bi bi-chat-dots me-2"></i> 常见问题</h5>
                        <p>在联系我们之前，您可以查看我们的<a href="faq.php">常见问题解答</a></p>
                    </div>
                </div>
                
                <?php if (isLoggedIn() && $db): ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">您的账户信息</h5>
                            <p class="card-text">
                                <strong>用户名:</strong> <?php echo $_SESSION['username']; ?><br>
                                <strong>用户等级:</strong> 
                                <?php 
                                $user_levels = [
                                    'free' => '免费用户',
                                    'premium' => '高级用户',
                                    'admin' => '管理员'
                                ];
                                echo $user_levels[getUserLevel()]; 
                                ?>
                            </p>
                            <p class="card-text">
                                如果您遇到账户相关问题，请在消息中提及您的用户名。
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>短链接服务</h5>
                    <p>简单、快速、可靠的URL缩短服务</p>
                </div>
                <div class="col-md-3">
                    <h5>快速链接</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">首页</a></li>
                        <li><a href="about.php" class="text-white">关于我们</a></li>
                        <li><a href="contact.php" class="text-white">联系我们</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>联系我们</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope me-2"></i> <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="text-white"><?php echo ADMIN_EMAIL; ?></a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; 2023 短链接服务. 保留所有权利.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>