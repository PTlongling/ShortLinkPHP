<?php
// install.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短链接系统安装</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">短链接系统安装</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // 检查是否已安装
                        if (file_exists('config.php')) {
                            echo '<div class="alert alert-warning">系统似乎已经安装过了。如需重新安装，请先删除 config.php 文件。</div>';
                            echo '<a href="index.php" class="btn btn-primary">前往首页</a>';
                            exit;
                        }

                        // 处理表单提交
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $db_host = $_POST['db_host'] ?? 'localhost';
                            $db_name = $_POST['db_name'] ?? 'shortlink_db';
                            $db_user = $_POST['db_user'] ?? '';
                            $db_pass = $_POST['db_pass'] ?? '';
                            $admin_user = $_POST['admin_user'] ?? 'admin';
                            $admin_pass = $_POST['admin_pass'] ?? '';
                            $admin_email = $_POST['admin_email'] ?? '';
                            
                            // 验证输入
                            $errors = [];
                            if (empty($db_user)) $errors[] = '数据库用户名不能为空';
                            if (empty($admin_user)) $errors[] = '管理员用户名不能为空';
                            if (empty($admin_pass)) $errors[] = '管理员密码不能为空';
                            if (empty($admin_email)) $errors[] = '管理员邮箱不能为空';
                            
                            if (empty($errors)) {
                                try {
                                    // 测试数据库连接
                                    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    
                                    // 创建数据库
                                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                                    $pdo->exec("USE `$db_name`");
                                    
                                    // 直接执行SQL语句而不是从文件读取
                                    $sql_commands = [
                                        // 用户表
                                        "CREATE TABLE users (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            username VARCHAR(50) NOT NULL UNIQUE,
                                            email VARCHAR(100) NOT NULL UNIQUE,
                                            password VARCHAR(255) NOT NULL,
                                            user_level ENUM('free', 'premium', 'admin') DEFAULT 'free',
                                            registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                                            last_login DATETIME NULL,
                                            status ENUM('active', 'inactive', 'banned') DEFAULT 'active'
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                                        
                                        // 短链接表
                                        "CREATE TABLE short_links (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            short_code VARCHAR(10) NOT NULL UNIQUE,
                                            long_url TEXT NOT NULL,
                                            user_id INT NOT NULL,
                                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                                            expires_at DATETIME NULL,
                                            click_count INT DEFAULT 0,
                                            is_active BOOLEAN DEFAULT TRUE,
                                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                                        
                                        // 用户等级限制表
                                        "CREATE TABLE user_limits (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            user_level ENUM('free', 'premium', 'admin') NOT NULL UNIQUE,
                                            max_links INT NOT NULL,
                                            link_validity_days INT NOT NULL
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                                        
                                        // 插入默认限制
                                        "INSERT INTO user_limits (user_level, max_links, link_validity_days) VALUES
                                        ('free', 10, 30),
                                        ('premium', 100, 365),
                                        ('admin', 1000, 3650);",
                                        
                                        // 验证码表
                                        "CREATE TABLE captchas (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            captcha_text VARCHAR(10) NOT NULL,
                                            session_id VARCHAR(100) NOT NULL,
                                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
                                    ];
                                    
                                    // 执行所有SQL命令
                                    foreach ($sql_commands as $sql) {
                                        $pdo->exec($sql);
                                    }
                                    
                                    // 创建管理员账号
                                    $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
                                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_level) VALUES (?, ?, ?, 'admin')");
                                    $stmt->execute([$admin_user, $admin_email, $hashed_password]);
                                    
                                    // 创建配置文件
                                    $config_content = "<?php\n";
                                    $config_content .= "define('DB_HOST', '$db_host');\n";
                                    $config_content .= "define('DB_NAME', '$db_name');\n";
                                    $config_content .= "define('DB_USER', '$db_user');\n";
                                    $config_content .= "define('DB_PASS', '$db_pass');\n";
                                    $config_content .= "define('SITE_URL', '" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "');\n";
                                    $config_content .= "define('SHORT_URL_LENGTH', 6);\n";
                                    $config_content .= "define('CAPTCHA_SESSION_KEY', 'captcha_code');\n";
                                    $config_content .= "define('USER_LEVEL_FREE', 'free');\n";
                                    $config_content .= "define('USER_LEVEL_PREMIUM', 'premium');\n";
                                    $config_content .= "define('USER_LEVEL_ADMIN', 'admin');\n";
                                    $config_content .= "define('RENEWAL_GRACE_PERIOD', 86400);\n";
                                    $config_content .= "?>";
                                    
                                    file_put_contents('config.php', $config_content);
                                    
                                    echo '<div class="alert alert-success">安装成功！</div>';
                                    echo '<p>管理员账号已创建：</p>';
                                    echo '<ul>';
                                    echo '<li>用户名：' . htmlspecialchars($admin_user) . '</li>';
                                    echo '<li>邮箱：' . htmlspecialchars($admin_email) . '</li>';
                                    echo '</ul>';
                                    echo '<a href="index.php" class="btn btn-primary">前往首页</a>';
                                    exit;
                                    
                                } catch (PDOException $e) {
                                    echo '<div class="alert alert-danger">数据库错误：' . $e->getMessage() . '</div>';
                                }
                            } else {
                                foreach ($errors as $error) {
                                    echo '<div class="alert alert-danger">' . $error . '</div>';
                                }
                            }
                        }
                        ?>
                        <form method="post">
                            <h4>数据库配置</h4>
                            <div class="mb-3">
                                <label class="form-label">数据库主机</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">数据库名</label>
                                <input type="text" class="form-control" name="db_name" value="shortlink_db" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">数据库用户名</label>
                                <input type="text" class="form-control" name="db_user" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">数据库密码</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>
                            
                            <h4>管理员账户</h4>
                            <div class="mb-3">
                                <label class="form-label">管理员用户名</label>
                                <input type="text" class="form-control" name="admin_user" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">管理员邮箱</label>
                                <input type="email" class="form-control" name="admin_email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">管理员密码</label>
                                <input type="password" class="form-control" name="admin_pass" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">安装</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>