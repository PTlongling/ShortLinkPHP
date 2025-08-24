<?php
// privacy.php
session_start();

// 定义常量
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('SITE_NAME', '短链接服务');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隐私条款 - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .privacy-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .privacy-nav {
            position: sticky;
            top: 20px;
        }
        .privacy-nav .nav-link {
            color: #7f8c8d;
            padding: 8px 15px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .privacy-nav .nav-link:hover,
        .privacy-nav .nav-link.active {
            color: #3498db;
            border-left-color: #3498db;
            background-color: #f8f9fa;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            border-radius: 5px;
        }
        .last-updated {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">关于</a></li>
                    <li class="nav-item"><a class="nav-link active" href="privacy.php">隐私条款</a></li>
                    <li class="nav-item"><a class="nav-link" href="getinfo.php">服务条款</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">控制台</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">退出</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">登录</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">注册</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <!-- 侧边导航 -->
            <div class="col-md-3 d-none d-md-block">
                <div class="privacy-nav">
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#introduction">引言</a>
                        <a class="nav-link" href="#data-collection">数据收集</a>
                        <a class="nav-link" href="#data-usage">数据使用</a>
                        <a class="nav-link" href="#data-protection">数据保护</a>
                        <a class="nav-link" href="#cookies">Cookie政策</a>
                        <a class="nav-link" href="#user-rights">用户权利</a>
                        <a class="nav-link" href="#data-retention">数据保留</a>
                        <a class="nav-link" href="#third-party">第三方服务</a>
                        <a class="nav-link" href="#children-privacy">儿童隐私</a>
                        <a class="nav-link" href="#policy-changes">政策变更</a>
                        <a class="nav-link" href="#contact">联系我们</a>
                    </nav>
                </div>
            </div>

            <!-- 主要内容 -->
            <div class="col-md-9">
                <div class="privacy-container">
                    <div class="last-updated">
                        <h5><i class="bi bi-info-circle"></i> 最后更新日期</h5>
                        <p class="mb-0">2024年1月15日</p>
                    </div>

                    <h1 class="mb-4">隐私条款</h1>

                    <section id="introduction" class="mb-5">
                        <h2 class="section-title">1. 引言</h2>
                        <p>欢迎使用 <?php echo SITE_NAME; ?>（以下简称"本服务"）。我们高度重视您的隐私保护，并致力于透明地说明我们如何收集、使用和保护您的个人信息。</p>
                        <p>本隐私条款阐述了当您使用我们的短链接服务时，我们如何处理您的个人信息。请仔细阅读本条款，以了解我们的隐私实践。</p>
                    </section>

                    <section id="data-collection" class="mb-5">
                        <h2 class="section-title">2. 我们收集的信息</h2>
                        
                        <h4>2.1 您直接提供的信息</h4>
                        <ul>
                            <li><strong>账户信息：</strong>当您注册账户时，我们收集用户名、电子邮箱地址和密码</li>
                            <li><strong>链接信息：</strong>您创建短链接时提供的原始URL地址</li>
                            <li><strong>使用数据：</strong>您使用服务时产生的操作记录</li>
                        </ul>

                        <h4>2.2 自动收集的信息</h4>
                        <ul>
                            <li><strong>技术信息：</strong>IP地址、浏览器类型、设备信息、操作系统</li>
                            <li><strong>使用数据：</strong>短链接点击次数、访问时间、来源网址</li>
                            <li><strong>Cookie数据：</strong>用于改善用户体验的Cookie信息</li>
                        </ul>

                        <div class="highlight">
                            <i class="bi bi-shield-check"></i>
                            <strong>重要提示：</strong>我们不会收集或存储通过短链接访问的目标网站的内容数据。
                        </div>
                    </section>

                    <section id="data-usage" class="mb-5">
                        <h2 class="section-title">3. 信息使用方式</h2>
                        
                        <p>我们使用收集的信息用于以下目的：</p>
                        <ul>
                            <li>提供、维护和改进我们的服务</li>
                            <li>创建和管理您的用户账户</li>
                            <li>生成和跟踪短链接的使用统计</li>
                            <li>发送重要的服务通知和更新</li>
                            <li>防止欺诈和滥用行为</li>
                            <li>遵守法律义务</li>
                        </ul>

                        <div class="highlight">
                            <i class="bi bi-graph-up"></i>
                            <strong>统计分析：</strong>我们使用聚合数据（不包含个人身份信息）进行服务优化和业务分析。
                        </div>
                    </section>

                    <section id="data-protection" class="mb-5">
                        <h2 class="section-title">4. 数据保护措施</h2>
                        
                        <p>我们采取严格的安全措施保护您的信息：</p>
                        <ul>
                            <li>使用SSL加密传输所有数据</li>
                            <li>密码采用bcrypt强哈希算法存储</li>
                            <li>定期进行安全审计和漏洞扫描</li>
                            <li>限制员工访问权限，仅限必要人员</li>
                            <li>实施数据备份和灾难恢复计划</li>
                        </ul>

                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb"></i>
                            尽管我们采取了合理的安全措施，但没有任何网络传输或电子存储方式是100%安全的。
                        </div>
                    </section>

                    <section id="cookies" class="mb-5">
                        <h2 class="section-title">5. Cookie政策</h2>
                        
                        <p>我们使用Cookie来：</p>
                        <ul>
                            <li>保持用户登录状态</li>
                            <li>记住用户偏好设置</li>
                            <li>分析服务使用情况</li>
                            <li>防止安全威胁</li>
                        </ul>

                        <p>您可以通过浏览器设置控制Cookie的使用，但请注意禁用某些Cookie可能会影响服务功能。</p>
                    </section>

                    <section id="user-rights" class="mb-5">
                        <h2 class="section-title">6. 您的权利</h2>
                        
                        <p>根据相关法律法规，您享有以下权利：</p>
                        <ul>
                            <li><strong>访问权：</strong>查看我们持有的您的个人信息</li>
                            <li><strong>更正权：</strong>更正不准确或不完整的个人信息</li>
                            <li><strong>删除权：</strong>在特定情况下要求删除您的个人信息</li>
                            <li><strong>限制处理权：</strong>限制我们处理您的个人信息</li>
                            <li><strong>数据可携权：</strong>获取您的数据的机器可读副本</li>
                            <li><strong>反对权：</strong>反对某些数据处理活动</li>
                        </ul>

                        <p>要行使这些权利，请通过本文末尾提供的联系方式与我们联系。</p>
                    </section>

                    <section id="data-retention" class="mb-5">
                        <h2 class="section-title">7. 数据保留期限</h2>
                        
                        <p>我们仅在实现本隐私条款所述目的所必需的时间内保留您的信息：</p>
                        <ul>
                            <li><strong>账户数据：</strong>账户活跃期间及注销后6个月</li>
                            <li><strong>短链接数据：</strong>根据用户等级的有效期设置</li>
                            <li><strong>使用日志：</strong>最长保留12个月用于安全审计</li>
                        </ul>
                    </section>

                    <section id="third-party" class="mb-5">
                        <h2 class="section-title">8. 第三方服务</h2>
                        
                        <p>我们可能使用第三方服务提供商来帮助我们运营服务：</p>
                        <ul>
                            <li><strong>云服务提供商：</strong>用于数据存储和处理</li>
                            <li><strong>分析服务：</strong>用于服务使用情况分析</li>
                            <li><strong>支付处理商：</strong>如果您选择升级服务</li>
                        </ul>

                        <p>这些服务提供商仅根据我们的指示处理数据，并受严格的保密义务约束。</p>
                    </section>

                    <section id="children-privacy" class="mb-5">
                        <h2 class="section-title">9. 儿童隐私</h2>
                        
                        <p>我们的服务不面向13周岁以下的儿童。我们不会故意收集13周岁以下儿童的个人信息。</p>
                        <p>如果您是父母或监护人，并认为您的孩子向我们提供了个人信息，请立即与我们联系，我们将采取措施删除此类信息。</p>
                    </section>

                    <section id="policy-changes" class="mb-5">
                        <h2 class="section-title">10. 政策变更</h2>
                        
                        <p>我们可能不时更新本隐私条款。如有重大变更，我们将在生效前通过网站公告或电子邮件通知您。</p>
                        <p>继续使用我们的服务即表示您接受更新后的隐私条款。</p>
                    </section>

                    <section id="contact" class="mb-5">
                        <h2 class="section-title">11. 联系我们</h2>
                        
                        <p>如果您对本隐私条款或我们的隐私实践有任何疑问、意见或请求，请通过以下方式联系我们：</p>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-envelope"></i> 电子邮件</h5>
                                <p class="card-text">service@wanweitool.cn<?php echo $_SERVER['HTTP_HOST']; ?></p>
                                
                                <h5 class="card-title mt-4"><i class="bi bi-clock"></i> 处理时间</h5>
                                <p class="card-text">我们将在收到请求后的30天内回复您的询问。</p>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>监管机构投诉权：</strong>如果您认为我们对您个人信息的处理违反了适用法律，您有权向相关数据保护机构投诉。
                        </div>
                    </section>

                    <div class="alert alert-success mt-5">
                        <h5><i class="bi bi-check-circle"></i> 感谢您阅读我们的隐私条款</h5>
                        <p class="mb-0">我们承诺保护您的隐私并提供安全可靠的服务。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>简单、快速、可靠的URL缩短服务</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white me-3">隐私条款</a>
                    <a href="getinfo.php" class="text-white me-3">服务条款</a>
                    <a href="contact.php" class="text-white">联系我们</a>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2024 <?php echo SITE_NAME; ?>. 保留所有权利.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 平滑滚动到锚点
        document.querySelectorAll('.privacy-nav .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // 更新活动状态
                    document.querySelectorAll('.privacy-nav .nav-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // 更新URL哈希
                    history.pushState(null, null, this.getAttribute('href'));
                }
            });
        });

        // 监听滚动更新活动状态
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section');
            let currentSection = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 100) {
                    currentSection = section.getAttribute('id');
                }
            });
            
            document.querySelectorAll('.privacy-nav .nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + currentSection) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>