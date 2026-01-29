<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php?section=requests');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM cereri WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: profile.php?section=requests&error=not_found');
        exit;
    }
    if ($req['status'] !== 'in_asteptare') {
        header('Location: profile.php?section=requests&error=cannot_edit');
        exit;
    }
} else {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $applicant_name = trim($_POST['applicant_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');

    if (!$id || $applicant_name === '' || $phone === '' || $mesaj === '') {
        header('Location: edit_request.php?id=' . $id . '&error=missing');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM cereri WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $req = $stmt->fetch();
    if (!$req) {
        header('Location: profile.php?section=requests&error=not_found');
        exit;
    }
    if ($req['status'] !== 'in_asteptare') {
        header('Location: profile.php?section=requests&error=cannot_edit');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE cereri SET applicant_name = ?, phone = ?, address = ?, mesaj = ? WHERE id = ?");
    if ($stmt->execute([$applicant_name, $phone, $address, $mesaj, $id])) {
        header('Location: profile.php?section=requests&success=edited');
        exit;
    } else {
        header('Location: edit_request.php?id=' . $id . '&error=save_failed');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editează Cerere - Labuta Fericita</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div style="max-width:700px;margin:40px auto;padding:20px;background:white;border-radius:8px;">
        <h2>Editează cererea #<?php echo htmlspecialchars($req['id']); ?></h2>
        <?php if (isset($_GET['error'])): ?>
            <div style="padding:10px;background:#ffdede;border:1px solid #f0b0b0;color:#8a0000;">Eroare: <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($req['id']); ?>">
            <div class="form-group">
                <label>Nume complet</label>
                <input type="text" name="applicant_name" value="<?php echo htmlspecialchars($req['applicant_name'] ?? $_SESSION['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Telefon</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($req['phone'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Adresa</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($req['address'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Mesaj</label>
                <textarea name="mesaj" rows="6" required><?php echo htmlspecialchars($req['mesaj'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn-submit">Salvează modificările</button>
            <a href="profile.php?section=requests" style="margin-left:10px;">Înapoi</a>
        </form>
    </div>
</body>
</html>