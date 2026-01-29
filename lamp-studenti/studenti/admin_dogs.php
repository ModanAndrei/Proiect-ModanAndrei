<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$feedback = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            if (!isset($_POST['dog_id']) || !is_numeric($_POST['dog_id'])) throw new Exception('ID invalid.');
            $dog_id = (int)$_POST['dog_id'];

            $stmt = $pdo->prepare("DELETE FROM caini WHERE id = ?");
            $stmt->execute([$dog_id]);
            $feedback = 'Anunțul a fost șters.';

        } elseif ($action === 'set_status') {
            if (!isset($_POST['dog_id']) || !is_numeric($_POST['dog_id'])) throw new Exception('ID invalid.');
            $dog_id = (int)$_POST['dog_id'];
            $status = $_POST['status'] ?? '';
            $allowed = ['disponibil','adoptat'];
            if (!in_array($status, $allowed)) throw new Exception('Status invalid.');

            $stmt = $pdo->prepare("UPDATE caini SET status = ? WHERE id = ?");
            $stmt->execute([$status, $dog_id]);
            $feedback = 'Status actualizat.';
        } else {
            throw new Exception('Acțiune necunoscută.');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM caini ORDER BY id DESC");
$caini = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Gestionare Anunțuri</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .admin-container { max-width: 1100px; margin: 30px auto; padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: middle; }
    img.thumb { width: 80px; height: 60px; object-fit: cover; border-radius:6px; }
    .actions form { display: inline-block; margin-right: 6px; }
    .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .btn { padding:8px 10px; border-radius:6px; text-decoration:none; color:white; background:#4CAF50; }
    .danger { background:#ff4d4d; }
    select.status { padding:6px; border-radius:6px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="admin-container">
        <div class="top-actions">
            <h1>📝 Gestionare Anunțuri</h1>
            <a href="edit_dog.php" class="btn">Adaugă anunț nou</a>
        </div>

        <?php if ($feedback): ?>
            <div style="padding:10px;background:#eaffea;border:1px solid #b7f0b7;color:#084d10; margin-bottom:12px;"><?php echo htmlspecialchars($feedback); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="padding:10px;background:#ffdede;border:1px solid #f0b0b0;color:#8a0000; margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagine</th>
                    <th>Nume</th>
                    <th>Rasă</th>
                    <th>Vârsta</th>
                    <th>Stare</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($caini as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><img src="<?php echo htmlspecialchars($c['imagine']); ?>" class="thumb" alt="<?php echo htmlspecialchars($c['nume']); ?>"></td>
                        <td><?php echo htmlspecialchars($c['nume']); ?></td>
                        <td><?php echo htmlspecialchars($c['rasa']); ?></td>
                        <td><?php echo htmlspecialchars($c['varsta']); ?></td>
                        <td><?php echo htmlspecialchars($c['status']); ?></td>
                        <td>
                            <a href="edit_dog.php?id=<?php echo $c['id']; ?>" class="btn">Editează</a>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('Ștergi acest anunț?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="dog_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" class="btn danger">Șterge</button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="set_status">
                                <input type="hidden" name="dog_id" value="<?php echo $c['id']; ?>">
                                <select name="status" class="status" onchange="this.form.submit()">
                                    <option value="disponibil" <?php echo $c['status'] === 'disponibil' ? 'selected' : ''; ?>>disponibil</option>
                                    <option value="adoptat" <?php echo $c['status'] === 'adoptat' ? 'selected' : ''; ?>>adoptat</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>