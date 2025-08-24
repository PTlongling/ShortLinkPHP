<?php
// terms.php
session_start();

// 定义常量
define('DB_HOST', 'localhost');
define('DB_NAME', 'shortlink_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
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
    // 忽略数据库错误，只显示页面
}

// 常用函数
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserLevel() {
    return $_SESSION['user_level'] ?? 'free';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>服务条款 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(120deg, #007bff, #6610f2);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .terms-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .terms-nav {
            position: sticky;
            top: 20px;
        }
        .terms-content {
            line-height: 1.8;
        }
        .highlight {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            z-index: 1000;
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
                    <li class="nav-item"><a class="nav-link active" href="terms.php">服务条款</a></li>
                    <li class="nav-item"><a class="nav-link" href="privacy.php">隐私政策</a></li>
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
            <h1 class="display-4">服务条款</h1>
            <p class="lead">请仔细阅读以下条款和条件</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- 侧边导航 -->
            <div class="col-md-3 d-none d-md-block">
                <div class="terms-nav">
                    <div class="list-group">
                        <a class="list-group-item list-group-item-action" href="#acceptance">1. 接受条款</a>
                        <a class="list-group-item list-group-item-action" href="#description">2. 服务描述</a>
                        <a class="list-group-item list-group-item-action" href="#registration">3. 注册与账户</a>
                        <a class="list-group-item list-group-item-action" href="#user-conduct">4. 用户行为规范</a>
                        <a class="list-group-item list-group-item-action" href="#content">5. 内容责任</a>
                        <a class="list-group-item list-group-item-action" href="#intellectual-property">6. 知识产权</a>
                        <a class="list-group-item list-group-item-action" href="#privacy">7. 隐私政策</a>
                        <a class="list-group-item list-group-item-action" href="#disclaimer">8. 免责声明</a>
                        <a class="list-group-item list-group-item-action" href="#limitation">9. 责任限制</a>
                        <a class="list-group-item list-group-item-action" href="#termination">10. 终止服务</a>
                        <a class="list-group-item list-group-item-action" href="#modification">11. 条款修改</a>
                        <a class="list-group-item list-group-item-action" href="#governing-law">12. 适用法律</a>
                        <a class="list-group-item list-group-item-action" href="#contact">13. 联系我们</a>
                    </div>
                </div>
            </div>
            
            <!-- 主要内容 -->
            <div class="col-md-9">
                <div class="terms-content">
                    <div class="alert alert-info">
                        <strong>最后更新日期：</strong>2023年11月1日<br>
                        <strong>生效日期：</strong>2023年11月1日
                    </div>

                    <h2 id="acceptance" class="section-title">1. 接受条款</h2>
                    <p>通过访问或使用短链接服务（以下简称"本服务"），您同意受本服务条款的约束。如果您不同意这些条款，请勿使用本服务。</p>
                    <p>我们保留随时修改这些条款的权利。修改后的条款将在网站上公布后立即生效。您继续使用本服务即表示您接受修改后的条款。</p>

                    <h2 id="description" class="section-title">2. 服务描述</h2>
                    <p>本服务提供URL缩短功能，允许用户将长网址转换为短链接。本服务包括：</p>
                    <ul>
                        <li>免费生成短链接</li>
                        <li>链接点击统计</li>
                        <li>链接管理功能</li>
                        <li>自定义短链接（仅限高级用户）</li>
                    </ul>
                    <p>我们保留随时修改、暂停或终止本服务（或其任何部分）的权利，恕不另行通知。</p>

                    <h2 id="registration" class="section-title">3. 注册与账户</h2>
                    <p>要使用本服务的某些功能，您需要注册一个账户。您同意：</p>
                    <ul>
                        <li>提供真实、准确、最新和完整的注册信息</li>
                        <li>维护并及时更新注册信息，以保持其真实、准确、最新和完整</li>
                        <li>对您的账户和密码的保密负责</li>
                        <li>对在您账户下发生的所有活动负责</li>
                        <li>立即通知我们任何未经授权使用您账户的情况</li>
                    </ul>
                    <p>我们保留因以下原因拒绝服务、终止账户、删除或编辑内容的权利：</p>
                    <ul>
                        <li>违反本服务条款</li>
                        <li>提供虚假或不完整的注册信息</li>
                        <li>从事非法或有害活动</li>
                        <li>长时间不活动</li>
                    </ul>

                    <h2 id="user-conduct" class="section-title">4. 用户行为规范</h2>
                    <p>您同意不使用本服务从事以下活动：</p>
                    <div class="highlight">
                        <h5>禁止内容：</h5>
                        <ul>
                            <li>非法或促进非法活动的内容</li>
                            <li>侵犯他人知识产权的内容</li>
                            <li>仇恨言论、歧视性或骚扰性内容</li>
                            <li>色情或成人内容</li>
                            <li>恶意软件、病毒或其他破坏性代码</li>
                            <li>网络钓鱼或欺诈活动</li>
                            <li>垃圾邮件或未经请求的大量消息</li>
                            <li>侵犯他人隐私的内容</li>
                            <li>暴力或威胁性内容</li>
                            <li>违反任何适用法律或法规的内容</li>
                        </ul>
                    </div>
                    <p>我们有权自行决定删除任何违反这些条款的内容，并暂停或终止违规用户的账户。</p>

                    <h2 id="content" class="section-title">5. 内容责任</h2>
                    <p>您对通过本服务创建和共享的短链接及其指向的内容承担全部责任。我们：</p>
                    <ul>
                        <li>不预先审查用户生成的内容</li>
                        <li>不对任何用户生成的内容负责</li>
                        <li>保留删除任何我们认为违反这些条款的内容的权利</li>
                    </ul>
                    <p>您同意赔偿并使本服务免受因您使用本服务而产生的任何索赔、损害、责任、损失和费用。</p>

                    <h2 id="intellectual-property" class="section-title">6. 知识产权</h2>
                    <p>本服务及其原始内容、特性和功能是本服务的独家财产，受版权、商标、专利和其他知识产权法保护。</p>
                    <p>您授予我们全球性、免版税、可再许可的非独占许可，以使用、复制、修改、适配、发布、翻译和分发您通过本服务提交的任何内容。</p>
                    <p>您声明并保证您拥有必要的权利授予上述许可，并且您的内容不侵犯任何第三方的权利。</p>

                    <h2 id="privacy" class="section-title">7. 隐私政策</h2>
                    <p>您的隐私对我们至关重要。我们的<a href="privacy.php">隐私政策</a>解释了我们会如何收集、使用和保护您的个人信息。</p>
                    <p>使用本服务即表示您同意按照我们的隐私政策收集和使用信息。</p>

                    <h2 id="disclaimer" class="section-title">8. 免责声明</h2>
                    <p>本服务按"原样"和"可用"的基础提供，不提供任何明示或暗示的保证，包括但不限于对适销性、特定用途适用性和不侵权的暗示保证。</p>
                    <p>我们不保证：</p>
                    <ul>
                        <li>本服务将满足您的要求</li>
                        <li>本服务将不间断、及时、安全或无错误</li>
                        <li>通过本服务获得的任何信息准确可靠</li>
                        <li>本服务中的任何缺陷将被纠正</li>
                    </ul>

                    <h2 id="limitation" class="section-title">9. 责任限制</h2>
                    <p>在任何情况下，我们均不对任何间接、附带、特殊、后果性或惩罚性损害赔偿负责，包括但不限于利润损失、数据损失或其他无形损失，无论其原因如何。</p>
                    <p>我们对您使用本服务的总责任不超过您在过去12个月内为使用本服务支付的金额（如有）。</p>

                    <h2 id="termination" class="section-title">10. 终止服务</h2>
                    <p>我们可自行决定终止或暂停您的账户和访问本服务的权限，恕不另行通知或承担责任，如果我们认为您违反了这些服务条款。</p>
                    <p>终止后，您使用本服务的权利将立即停止。如果您希望终止您的账户，您可以简单地停止使用本服务。</p>

                    <h2 id="modification" class="section-title">11. 条款修改</h2>
                    <p>我们保留随时修改这些条款的权利。我们将通过在本网站上发布修订后的条款来通知您任何更改。</p>
                    <p>在发布修订后的条款后继续使用本服务，即表示您接受这些更改。如果您不同意修订后的条款，您必须停止使用本服务。</p>

                    <h2 id="governing-law" class="section-title">12. 适用法律</h2>
                    <p>这些条款应受中华人民共和国法律管辖并据其解释，不考虑法律冲突原则。</p>
                    <p>任何因这些条款引起或与之相关的争议应提交有管辖权的中华人民共和国法院解决。</p>

                    <h2 id="contact" class="section-title">13. 联系我们</h2>
                    <p>如果您对这些服务条款有任何疑问，请通过以下方式联系我们：</p>
                    <ul>
                        <li>电子邮件：legal@yourdomain.com</li>
                        <li>联系表单：<a href="contact.php">点击这里</a></li>
                    </ul>

                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <p class="card-text">通过使用本服务，您承认已阅读、理解并同意受这些服务条款的约束。</p>
                            <a href="index.php" class="btn btn-primary">返回首页</a>
                            <?php if (!isLoggedIn()): ?>
                                <a href="register.php" class="btn btn-success">注册账户</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 返回顶部按钮 -->
    <a href="#" class="back-to-top btn btn-primary rounded-circle">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>短链接服务</h5>
                    <p>简单、快速、可靠的URL缩短服务</p>
                </div>
                <div class="col-md-3">
                    <h5>链接</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">首页</a></li>
                        <li><a href="about.php" class="text-white">关于</a></li>
                        <li><a href="contact.php" class="text-white">联系我们</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>法律</h5>
                    <ul class="list-unstyled">
                        <li><a href="terms.php" class="text-white">服务条款</a></li>
                        <li><a href="privacy.php" class="text-white">隐私政策</a></li>
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
    <script>
        // 返回顶部按钮
        const backToTopButton = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // 平滑滚动到锚点
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>