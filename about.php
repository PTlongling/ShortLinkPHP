<?php
// about.php
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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关于我们 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(120deg, #007bff, #6610f2);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #007bff;
        }
        .team-member {
            transition: transform 0.3s;
        }
        .team-member:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 1rem;
            color: #6c757d;
        }
        .timeline {
            position: relative;
            padding-left: 3rem;
            margin: 2rem 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #007bff;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.1rem;
            top: 0.5rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #007bff;
            border: 3px solid white;
        }
        .faq-item {
            margin-bottom: 1.5rem;
            border-left: 3px solid #007bff;
            padding-left: 1rem;
        }
        .testimonial {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .testimonial::before {
            content: '"';
            font-size: 4rem;
            color: #007bff;
            opacity: 0.2;
            position: absolute;
            top: -0.5rem;
            left: 0.5rem;
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
                    <li class="nav-item"><a class="nav-link active" href="about.php">关于</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">联系我们</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">控制台</a></li>
                        <?php if (getUserLevel() === USER_LEVEL_ADMIN): ?>
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

    <!-- 英雄区域 -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4">关于短链接服务</h1>
            <p class="lead">简单、快速、可靠的URL缩短服务，让您的链接更简洁、更易分享</p>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="container">
        <!-- 网站介绍 -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">我们的使命</h2>
                <p class="lead">
                    短链接服务致力于为用户提供简单、快速、可靠的URL缩短服务。我们相信，简洁的链接能够让信息传播更加高效，让分享变得更加便捷。
                </p>
            </div>
        </div>

        <!-- 统计数据 -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>我们的成就</h2>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number">5000+</div>
                <div class="stat-label">已生成短链接</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number">100+</div>
                <div class="stat-label">注册用户</div>
            </div>
            <div class="col-md-3 col-6 text-center mb-4">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">服务可用性(高级会员)</div>
            </div>
        </div>

        <!-- 功能特点 -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>为什么选择我们？</h2>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <h4>快速生成</h4>
                <p>只需粘贴长链接，一键生成短链接，无需等待</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h4>安全可靠</h4>
                <p>采用高级加密技术，保障您的链接安全和隐私</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h4>数据统计</h4>
                <p>实时跟踪链接点击情况，了解受众行为</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h4>多级用户</h4>
                <p>免费、高级和商业账户，满足不同需求</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h4>链接续期</h4>
                <p>过期链接可在24小时内续期，避免数据丢失</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="bi bi-palette"></i>
                </div>
                <h4>自定义后缀</h4>
                <p>高级用户可自定义短链接后缀，提升品牌形象</p>
            </div>
        </div>

        <!-- 发展历程 -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>发展历程</h2>
            </div>
            <div class="col-lg-8 mx-auto">
                <div class="timeline">
                    <div class="timeline-item">
                        <h5>2023年1月</h5>
                        <p>项目启动，开始开发短链接服务系统</p>
                    </div>
                    <div class="timeline-item">
                        <h5>2023年3月</h5>
                        <p>完成核心功能开发，开始内部测试</p>
                    </div>
                    <div class="timeline-item">
                        <h5>2023年5月</h5>
                        <p>正式上线，向公众开放注册</p>
                    </div>
                    <div class="timeline-item">
                        <h5>2023年7月</h5>
                        <p>用户突破100人，短链接生成量达10,00+</p>
                    </div>
                    <div class="timeline-item">
                        <h5>2023年9月</h5>
                        <p>推出高级会员计划，增加自定义后缀功能</p>
                    </div>
                    <div class="timeline-item">
                        <h5>2023年11月</h5>
                        <p>用户突破5,00人</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 团队介绍 -->
        <!-- 用户评价 -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>用户评价</h2>
            </div>
            <div class="col-md-6 mb-4">
                <div class="testimonial">
                    <p>"这是我用过的最好的短链接服务，界面简洁，功能强大，最重要的是免费！"</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0"></h6>
                            <small class="text-muted">免费用户</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="testimonial">
                    <p>"高级会员的自定义功能太棒了，让我们的品牌链接更加专业，物超所值！"</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0"></h6>
                            <small class="text-muted">高级用户</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="testimonial">
                    <p>"数据统计功能非常实用，帮助我们更好地了解用户行为和链接效果。"</p>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0"></h6>
                            <small class="text-muted">市场营销专员</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 常见问题 -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>常见问题</h2>
            </div>
            <div class="col-lg-8 mx-auto">
                <div class="faq-item">
                    <h5>短链接会过期吗？</h5>
                    <p>是的，免费用户的链接有效期为30天，高级用户为365天，管理员为10年。过期后链接将无法访问，但可以在24小时内续期。此举是为了防止恶意注册和长期无用的链接以节约费用</p>
                </div>
                <div class="faq-item">
                    <h5>如何升级到高级账户？</h5>
                    <p>目前我们提供免费账户，高级功能暂时不对所有用户开放。未来会推出付费高级计划，请关注我们的公告。</p>
                </div>
                <div class="faq-item">
                    <h5>支持自定义域名吗？</h5>
                    <p>目前不支持自定义域名，但高级用户可以使用自定义后缀功能，创建更具品牌特色的短链接。</p>
                </div>
                <div class="faq-item">
                    <h5>如何获取API访问权限？</h5>
                    <p>API功能目前处于测试阶段，仅对部分用户开放。如果您需要API访问权限，请联系我们的技术支持团队。</p>
                </div>
                <div class="faq-item">
                    <h5>数据隐私如何保护？</h5>
                    <p>我们非常重视用户隐私，所有链接数据都经过加密处理，不会向第三方透露任何用户信息。详细内容请参阅我们的隐私政策。</p>
                </div>
            </div>
        </div>

        <!-- 行动号召 -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <div class="card bg-light">
                    <div class="card-body py-5">
                        <h3 class="card-title mb-4">立即开始使用短链接服务</h3>
                        <?php if (isLoggedIn()): ?>
                            <a href="index.php" class="btn btn-primary btn-lg me-3">创建短链接</a>
                            <a href="dashboard.php" class="btn btn-outline-primary btn-lg">管理我的链接</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary btn-lg me-3">免费注册</a>
                            <a href="login.php" class="btn btn-outline-primary btn-lg">登录账户</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>短链接服务</h5>
                    <p>简单、快速、可靠的URL缩短服务</p>
                    <div class="d-flex">
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>产品</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">首页</a></li>
                        <li><a href="pricing.php" class="text-white-50">价格</a></li>
                        <li><a href="api.php" class="text-white-50">API</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>支持</h5>
                    <ul class="list-unstyled">
                        <li><a href="contact.php" class="text-white-50">联系我们</a></li>
                        <li><a href="https://status.wanweitool.cn/status/dashboard/" class="text-white-50">服务状态</a></li>
                        <li><a href="faq.php" class="text-white-50">常见问题</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>法律</h5>
                    <ul class="list-unstyled">
                        <li><a href="privacy.php" class="text-white-50">隐私政策</a></li>
                        <li><a href="getinfo.php" class="text-white-50">服务条款</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2023 短链接服务. 保留所有权利.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Made with <i class="bi bi-heart-fill text-danger"></i> for a better web</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>