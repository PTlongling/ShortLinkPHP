<?php
require_once 'config.php';

// 检查是否已登录
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $db = new Database($pdo);
    $user_id = $_SESSION['user_id'];
    $link_id = intval($_POST['id']);
    
    if ($db->renewLink($link_id, $user_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '续期失败，可能已超过续期宽限期']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效请求']);
}
?>