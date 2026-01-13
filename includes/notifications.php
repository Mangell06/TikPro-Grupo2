<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function notify($type, $message) {
    $_SESSION['notifications'][] = [
        'type' => $type, // info | warning | error
        'message' => $message
    ];
}

function notifyInfo($msg) {
    notify('info', $msg);
}

function notifyWarning($msg) {
    notify('warning', $msg);
}

function notifyError($msg) {
    notify('error', $msg);
}

/* Render */
if (!empty($_SESSION['notifications'])):
?>
<div class="notifications">
<?php foreach ($_SESSION['notifications'] as $n): ?>
    <div class="notification <?= htmlspecialchars($n['type']) ?>">
        <span><?= htmlspecialchars($n['message']) ?></span>
        <button onclick="this.parentElement.remove()">×</button>
    </div>
<?php endforeach; ?>
</div>
<?php
unset($_SESSION['notifications']);
endif;
