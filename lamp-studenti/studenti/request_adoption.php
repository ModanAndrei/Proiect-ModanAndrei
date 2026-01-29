<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function ensureCereriSchemaAndCharset($pdo) {
    $pdo->exec("SET NAMES 'utf8mb4'");

    $stmt = $pdo->query("SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cereri' AND COLUMN_NAME = 'mesaj'");
    $stmt = $pdo->query("SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cereri' AND COLUMN_NAME = 'mesaj'");
    $row = $stmt->fetch();
    if ($row && strtolower($row['CHARACTER_SET_NAME']) !== 'utf8mb4') {
        $pdo->exec("ALTER TABLE cereri MODIFY mesaj TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    $needed = [
        'applicant_name' => "VARCHAR(100) CHARACTER SET utf8mb4",
        'phone' => "VARCHAR(50)",
        'address' => "VARCHAR(255) CHARACTER SET utf8mb4",
    ];
    foreach ($needed as $col => $def) {
        $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cereri' AND COLUMN_NAME = ?");
        $s->execute([$col]);
        if ($s->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE cereri ADD COLUMN $col $def");
        }
    }

    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cereri' AND COLUMN_NAME = 'created_at'");
    $s->execute();
    if ($s->fetchColumn() == 0) {
        $pdo->exec("ALTER TABLE cereri ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['caine_id']) || !is_numeric($_GET['caine_id'])) {
        header('Location: adopta.php?error=invalid_id');
        exit;
    }
    header('Location: adopt_form.php?caine_id=' . intval($_GET['caine_id']));
    exit;
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caine_id = isset($_POST['caine_id']) && is_numeric($_POST['caine_id']) ? (int)$_POST['caine_id'] : 0;
    $user_id = $_SESSION['user_id'];
    $applicant_name = trim($_POST['applicant_name'] ?? ($_SESSION['username'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');

    if (!$caine_id) {
        header('Location: adopta.php?error=invalid_id');
        exit;
    }
    if ($applicant_name === '' || $phone === '' || $mesaj === '') {
        header('Location: adopt_form.php?caine_id=' . $caine_id . '&error=missing_fields');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM caini WHERE id = ? AND status = 'disponibil'");
    $stmt->execute([$caine_id]);
    $caine = $stmt->fetch();
    if (!$caine) {
        header('Location: adopta.php?error=not_available');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM cereri WHERE user_id = ? AND caine_id = ? AND status = 'in_asteptare'");
    $stmt->execute([$user_id, $caine_id]);
    if ($stmt->fetch()) {
        header('Location: adopt_form.php?caine_id=' . $caine_id . '&error=already_requested');
        exit;
    }

    try {
        ensureCereriSchemaAndCharset($pdo);

        $stmt = $pdo->prepare("INSERT INTO cereri (user_id, caine_id, mesaj, applicant_name, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, 'in_asteptare')");
        $stmt->execute([$user_id, $caine_id, $mesaj, $applicant_name, $phone, $address]);

        header('Location: adopta.php?success=1');
        exit;
    } catch (PDOException $e) {
        $mysqlCode = $e->errorInfo[1] ?? null;
        if ($mysqlCode == 1366) {
            try {
                ensureCereriSchemaAndCharset($pdo);
                $stmt = $pdo->prepare("INSERT INTO cereri (user_id, caine_id, mesaj, applicant_name, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, 'in_asteptare')");
                $stmt->execute([$user_id, $caine_id, $mesaj, $applicant_name, $phone, $address]);
                header('Location: adopta.php?success=1');
                exit;
            } catch (Exception $e2) {
                header('Location: adopt_form.php?caine_id=' . $caine_id . '&error=insert_failed');
                exit;
            }
        }

        header('Location: adopt_form.php?caine_id=' . $caine_id . '&error=insert_failed');
        exit;
    }
}

