<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['caine_id']) || !is_numeric($_GET['caine_id'])) {
    header('Location: adopta.php?error=invalid_id');
    exit;
}

$caine_id = (int)$_GET['caine_id'];
$stmt = $pdo->prepare("SELECT * FROM caini WHERE id = ?");
$stmt->execute([$caine_id]);
$caine = $stmt->fetch();
if (!$caine || $caine['status'] !== 'disponibil') {
    header('Location: adopta.php?error=not_available');
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formular Adopție - <?php echo htmlspecialchars($caine['nume']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 700px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; }
        .form-container label { display:block; margin-bottom:10px; }
        .form-container input[type="text"], .form-container textarea { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
        .form-container button { background:#4CAF50; color:white; padding:10px 16px; border:none; border-radius:4px; cursor:pointer; }
        .alert { padding:10px; margin-bottom:10px; border-radius:4px; }
        .alert.error { background:#ffdede; border:1px solid #f0b0b0; color:#8a0000; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="form-container">
        <h2>Completează cererea de adopție pentru <strong><?php echo htmlspecialchars($caine['nume']); ?></strong></h2>
        <?php if ($error): ?>
            <?php if ($error === 'missing_fields'): ?>
                <div class="alert error">Te rog completează toate câmpurile obligatorii.</div>
            <?php elseif ($error === 'already_requested'): ?>
                <div class="alert error">Ai deja o cerere în așteptare pentru acest câine.</div>
            <?php else: ?>
                <div class="alert error">A apărut o eroare. Încearcă din nou.</div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="request_adoption.php">
            <input type="hidden" name="caine_id" value="<?php echo $caine_id; ?>">

            <label>Nume complet
                <input type="text" name="applicant_name" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>
            </label>

            <label>Telefon
                <input type="text" name="phone" placeholder="07xx xxx xxx" required>
            </label>

            <label>Adresă (oraș, stradă)
                <input type="text" name="address" required>
            </label>

            <label>De ce dorești să adopți acest câine?
                <textarea name="mesaj" rows="6" required></textarea>
            </label>

            <button type="submit">Trimite cererea</button>
        </form>

        <p style="margin-top:12px;"><a href="adopta.php">Înapoi la lista de câini</a></p>
    </div>
</body>
</html>