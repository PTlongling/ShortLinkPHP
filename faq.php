<?php
// faq.php
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

// 常见问题数据
$faq_categories = [
    'general' => [
        'title' => '一般问题',
        'questions' => [
            [
                'question' => '什么是短链接服务？',
                'answer' => '短链接服务是一种将长网址转换为短网址的服务。它可以让您将复杂的URL转换为简短易记的链接，方便在社交媒体、短信或其他场合分享。'
            ],
            [
                'question' => '短链接有什么优势？',
                'answer' => '短链接的主要优势包括：<br>
                            1. 更短的URL，易于记忆和分享<br>
                            2. 可以跟踪链接的点击统计<br>
                            3. 隐藏原始的长URL<br>
                            4. 可以在不改变目的地的情况下更新链接'
            ],
            [
                'question' => '使用短链接服务需要付费吗？',
                'answer' => '我们提供免费和付费两种服务等级：<br>
                            - <strong>免费用户</strong>：可以创建最多100个链接，有效期30天<br>
                            - <strong>高级用户</strong>：可以创建最多1000个链接，有效期365天，支持自定义短代码<br>
                            - <strong>管理员</strong>：拥有所有功能权限'
            ]
        ]
    ],
    'account' => [
        'title' => '账户问题',
        'questions' => [
            [
                'question' => '如何注册账户？',
                'answer' => '您可以通过以下步骤注册账户：<br>
                            1. 点击页面右上角的"注册"按钮<br>
                            2. 填写用户名、邮箱和密码<br>
                            3. 输入验证码<br>
                            4. 点击"注册"按钮完成注册'
            ],
            [
                'question' => '忘记密码怎么办？',
                'answer' => '目前我们提供密码重置功能。如果您忘记密码，请联系管理员或使用注册邮箱找回密码。'
            ],
            [
                'question' => '如何升级到高级账户？',
                'answer' => '高级账户功能目前需要通过管理员开通。请联系我们的客服人员了解升级详情。'
            ],
            [
                'question' => '账户被封禁了怎么办？',
                'answer' => '如果您的账户因违反服务条款被封禁，请联系管理员申诉。请提供您的用户名和注册邮箱以便我们核实。'
            ]
        ]
    ],
    'links' => [
        'title' => '链接管理',
        'questions' => [
            [
                'question' => '如何创建短链接？',
                'answer' => '创建短链接的步骤：<br>
                            1. 登录您的账户<br>
                            2. 在首页输入要缩短的长网址<br>
                            3. （可选）如果您是高级用户，可以输入自定义短代码<br>
                            4. 点击"生成短链接"按钮'
            ],
            [
                'question' => '链接有效期是多久？',
                'answer' => '链接有效期根据用户等级不同：<br>
                            - 免费用户：30天<br>
                            - 高级用户：365天<br>
                            - 管理员：10年<br>
                            链接过期后24小时内可以续期。'
            ],
            [
                'question' => '如何查看链接的点击统计？',
                'answer' => '登录后进入"控制台"，您可以查看所有链接的点击次数、创建时间和过期状态。'
            ],
            [
                'question' => '可以自定义短代码吗？',
                'answer' => '自定义短代码是高级用户专属功能。高级用户可以选择2-10个字母数字字符作为自定义短代码。'
            ],
            [
                'question' => '链接过期后怎么办？',
                'answer' => '链接过期后24小时内，您可以在控制台中续期链接。超过24小时后链接将被永久删除。'
            ]
        ]
    ],
    'privacy' => [
        'title' => '隐私与安全',
        'questions' => [
            [
                'question' => '我的链接数据安全吗？',
                'answer' => '我们非常重视用户数据安全：<br>
                            - 所有数据传输都使用SSL加密<br>
                            - 数据库定期备份<br>
                            - 严格的访问控制措施'
            ],
            [
                'question' => '你们会记录用户的点击信息吗？',
                'answer' => '我们仅记录必要的点击统计信息（如点击次数），用于提供统计服务。不会记录用户的IP地址或其他个人信息。'
            ],
            [
                'question' => '可以删除我的账户和数据吗？',
                'answer' => '是的，您可以联系管理员请求删除账户和所有相关数据。删除后数据将无法恢复。'
            ]
        ]
    ],
    'troubleshooting' => [
        'title' => '故障排除',
        'questions' => [
            [
                'question' => '短链接无法访问怎么办？',
                'answer' => '如果短链接无法访问，可能的原因：<br>
                            1. 链接已过期<br>
                            2. 链接已被管理员禁用<br>
                            3. 原始网址无效或无法访问<br>
                            请检查链接状态或联系管理员。'
            ],
            [
                'question' => '为什么我无法创建新链接？',
                'answer' => '无法创建新链接的可能原因：<br>
                            1. 已达到最大链接数量限制<br>
                            2. 账户已被封禁<br>
                            3. 网络连接问题<br>
                            请检查您的账户状态或联系管理员。'
            ],
            [
                'question' => '验证码无法显示怎么办？',
                'answer' => '如果验证码无法显示，请尝试：<br>
                            1. 刷新页面<br>
                            2. 检查浏览器是否支持JavaScript<br>
                            3. 清除浏览器缓存<br>
                            如果问题持续，请联系管理员。'
            ]
        ]
    ]
];

// 处理搜索功能
$search_query = '';
$search_results = [];
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_results = searchFAQs($faq_categories, $search_query);
}

// 搜索函数
function searchFAQs($categories, $query) {
    $results = [];
    $query = strtolower($query);
    
    foreach ($categories as $category_id => $category) {
        foreach ($category['questions'] as $faq) {
            if (strpos(strtolower($faq['question']), $query) !== false || 
                strpos(strtolower($faq['answer']), $query) !== false) {
                $results[] = [
                    'category' => $category['title'],
                    'question' => $faq['question'],
                    'answer' => $faq['answer']
                ];
            }
        }
    }
    
    return $results;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>常见问题解答 - 短链接服务</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .faq-header {
            background: linear-gradient(120deg, #6c5ce7, #a29bfe);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .faq-category {
            margin-bottom: 3rem;
        }
        .faq-item {
            margin-bottom: 1.5rem;
            border-left: 4px solid #6c5ce7;
            padding-left: 1rem;
        }
        .faq-question {
            font-weight: 600;
            color: #2d3436;
            cursor: pointer;
            margin-bottom: 0.5rem;
        }
        .faq-answer {
            color: #636e72;
            padding-left: 1.5rem;
        }
        .search-highlight {
            background-color: #ffeaa7;
            padding: 0 2px;
            border-radius: 3px;
        }
        .category-badge {
            background: #6c5ce7;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-bottom: 1rem;
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
                    <li class="nav-item"><a class="nav-link active" href="faq.php">常见问题</a></li>
                    <?php if (isLoggedIn() && getUserLevel() === USER_LEVEL_ADMIN): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">管理员后台</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
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

    <!-- 头部区域 -->
    <div class="faq-header">
        <div class="container text-center">
            <h1 class="display-4">常见问题解答</h1>
            <p class="lead">在这里找到您需要的答案</p>
            
            <!-- 搜索框 -->
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
                    <form method="get" action="faq.php">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="搜索问题..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i> 搜索
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- 搜索结果显示 -->
        <?php if (!empty($search_results)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        找到 <strong><?php echo count($search_results); ?></strong> 个与 "<strong><?php echo htmlspecialchars($search_query); ?></strong>" 相关的结果
                        <a href="faq.php" class="float-end">查看所有问题</a>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <?php foreach ($search_results as $result): ?>
                                <div class="faq-item">
                                    <div class="small text-muted mb-1">分类: <?php echo $result['category']; ?></div>
                                    <div class="faq-question">
                                        <i class="bi bi-question-circle"></i>
                                        <?php echo highlightText($result['question'], $search_query); ?>
                                    </div>
                                    <div class="faq-answer">
                                        <i class="bi bi-arrow-return-right"></i>
                                        <?php echo highlightText($result['answer'], $search_query); ?>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 分类显示 -->
        <?php if (empty($search_results) || empty($search_query)): ?>
            <?php foreach ($faq_categories as $category_id => $category): ?>
                <div class="row faq-category">
                    <div class="col-12">
                        <h2 class="mb-4">
                            <span class="category-badge">
                                <i class="bi bi-collection"></i>
                                <?php echo $category['title']; ?>
                            </span>
                        </h2>
                        
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($category['questions'] as $index => $faq): ?>
                                    <div class="faq-item">
                                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#answer-<?php echo $category_id . '-' . $index; ?>">
                                            <i class="bi bi-question-circle"></i>
                                            <?php echo $faq['question']; ?>
                                        </div>
                                        <div class="faq-answer collapse" id="answer-<?php echo $category_id . '-' . $index; ?>">
                                            <i class="bi bi-arrow-return-right"></i>
                                            <?php echo $faq['answer']; ?>
                                        </div>
                                    </div>
                                    <?php if ($index < count($category['questions']) - 1): ?>
                                        <hr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- 联系支持 -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h3>没有找到您需要的答案？</h3>
                        <p class="lead">我们的支持团队随时为您提供帮助</p>
                        <div class="mt-3">
                            <a href="contact.php" class="btn btn-primary me-2">
                                <i class="bi bi-envelope"></i> 联系我们
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> 返回首页
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>短链接服务</h5>
                    <p>简单、快速、可靠的URL缩短服务</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="faq.php" class="text-white me-3">常见问题</a>
                    <a href="about.php" class="text-white me-3">关于我们</a>
                    <a href="contact.php" class="text-white">联系我们</a>
                </div>
            </div>
            <div class="text-center mt-3">
                <p>&copy; 2023 短链接服务. 保留所有权利.</p>
            </div>
        </div>
    </footer>

    <!-- 高亮文本函数 -->
    <?php
    function highlightText($text, $query) {
        if (empty($query)) {
            return $text;
        }
        $pattern = '/' . preg_quote($query, '/') . '/i';
        return preg_replace($pattern, '<span class="search-highlight">$0</span>', $text);
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 自动展开搜索结果的答案
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                // 展开所有搜索结果的答案
                const answers = document.querySelectorAll('.faq-answer');
                answers.forEach(answer => {
                    const bsCollapse = new bootstrap.Collapse(answer, {
                        toggle: true
                    });
                });
            }
        });
    </script>
</body>
</html>