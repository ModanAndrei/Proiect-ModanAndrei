<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$feedback = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$dog = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM caini WHERE id = ?");
    $stmt->execute([$id]);
    $dog = $stmt->fetch();
    if (!$dog) {
        $error = 'Anunțul nu a fost găsit.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = trim($_POST['nume'] ?? '');
    $rasa = trim($_POST['rasa'] ?? '');
    $varsta = trim($_POST['varsta'] ?? '');
    $descriere = trim($_POST['descriere'] ?? '');
    $imagine = trim($_POST['imagine'] ?? '');
    $status = $_POST['status'] ?? 'disponibil';

    if ($nume === '') {
        $error = 'Completează numele.';
    } else {
        try {
            if (!empty($_POST['id'])) {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE caini SET nume = ?, rasa = ?, varsta = ?, descriere = ?, imagine = ?, status = ? WHERE id = ?");
                $stmt->execute([$nume, $rasa, $varsta, $descriere, $imagine, $status, $id]);
                $feedback = 'Anunțul a fost actualizat.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO caini (nume, rasa, varsta, descriere, imagine, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nume, $rasa, $varsta, $descriere, $imagine, $status]);
                $feedback = 'Anunțul a fost creat.';
            }
            header('Location: admin_dogs.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $id ? 'Editează anunț' : 'Adaugă anunț'; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
    .form-wrap { max-width:700px; margin:30px auto; padding:20px; background:#fff; border-radius:10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06);}    
    label { display:block; margin:10px 0 6px; font-weight:600; }
    input[type=text], textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; }
    textarea { min-height:140px; }
    .btn { background:#4CAF50; color:white; padding:10px 12px; border-radius:6px; border:none; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="form-wrap">
        <h1><?php echo $id ? 'Editează anunț' : 'Adaugă anunț'; ?></h1>

        <?php if ($error): ?>
            <div style="padding:10px;background:#ffdede;border:1px solid #f0b0b0;color:#8a0000; margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php if ($id): ?><input type="hidden" name="id" value="<?php echo $id; ?>"><?php endif; ?>
            <label>Nume</label>
            <input type="text" name="nume" value="<?php echo htmlspecialchars($dog['nume'] ?? ''); ?>">

            <label>Rasă</label>
            <input type="text" name="rasa" value="<?php echo htmlspecialchars($dog['rasa'] ?? ''); ?>">

            <label>Vârsta</label>
            <input type="text" name="varsta" value="<?php echo htmlspecialchars($dog['varsta'] ?? ''); ?>">

            <label>Descriere</label>
            <textarea name="descriere"><?php echo htmlspecialchars($dog['descriere'] ?? ''); ?></textarea>

            <label>URL imagine (sau calea locală)</label>
            <input type="text" name="imagine" value="<?php echo htmlspecialchars($dog['imagine'] ?? ''); ?>">

            <label>Status</label>
            <select name="status">
                <option value="disponibil" <?php echo ($dog['status'] ?? '') === 'disponibil' ? 'selected' : ''; ?>>disponibil</option>
                <option value="adoptat" <?php echo ($dog['status'] ?? '') === 'adoptat' ? 'selected' : ''; ?>>adoptat</option>
            </select>

            <div style="margin-top:14px;">
                <button type="submit" class="btn"><?php echo $id ? 'Salvează' : 'Creează'; ?></button>
                <a href="admin_dogs.php" style="margin-left:8px;">Anulează</a>
            </div>
        </form>
    </div>
</body>
</html>