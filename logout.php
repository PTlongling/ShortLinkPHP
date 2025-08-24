<?php
// logout.php
session_start();

// 清除所有会话变量
$_SESSION = array();

// 删除会话cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 销毁会话（只有在会话已启动时才调用）
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// 重定向到首页
header("Location: index.php");
exit;
?>