<?php
session_start();
require 'db.php';

// Permisiuni - doar admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$feedback = '';
$error = '';

// Procesare actiuni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['request_id']) || !is_numeric($_POST['request_id'])) {
        $error = 'ID cerere invalid.';
    } else {
        $request_id = (int)$_POST['request_id'];
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'approve') {
                // Start tranzactie
                $pdo->beginTransaction();

                // Verific cererea si starea cainelui
                $stmt = $pdo->prepare("SELECT cereri.*, caini.status AS caine_status, caini.id AS caine_id FROM cereri JOIN caini ON cereri.caine_id = caini.id WHERE cereri.id = ? FOR UPDATE");
                $stmt->execute([$request_id]);
                $req = $stmt->fetch();

                if (!$req) {
                    throw new Exception('Cererea nu a fost gasita.');
                }
                if ($req['status'] !== 'in_asteptare') {
                    throw new Exception('Cererea nu mai este in asteptare.');
                }
                if ($req['caine_status'] !== 'disponibil') {
                    throw new Exception('Cainele nu este disponibil.');
                }

                // Aprobare cerere
                $stmt = $pdo->prepare("UPDATE cereri SET status = 'aprobat' WHERE id = ?");
                $stmt->execute([$request_id]);

                // Marcheaza caine ca adoptat
                $stmt = $pdo->prepare("UPDATE caini SET status = 'adoptat' WHERE id = ?");
                $stmt->execute([$req['caine_id']]);

                // Respinge alte cereri in asteptare pentru acelasi caine
                $stmt = $pdo->prepare("UPDATE cereri SET status = 'respins' WHERE caine_id = ? AND status = 'in_asteptare' AND id != ?");
                $stmt->execute([$req['caine_id'], $request_id]);

                $pdo->commit();
                $feedback = 'Cererea a fost aprobată și câinele marcat ca adoptat.';

            } elseif ($action === 'reject') {
                // Respinge cererea
                $stmt = $pdo->prepare("SELECT * FROM cereri WHERE id = ?");
                $stmt->execute([$request_id]);
                $req = $stmt->fetch();

                if (!$req) {
                    throw new Exception('Cererea nu a fost gasita.');
                }
                if ($req['status'] !== 'in_asteptare') {
                    throw new Exception('Cererea nu mai este in asteptare.');
                }

                $stmt = $pdo->prepare("UPDATE cereri SET status = 'respins' WHERE id = ?");
                $stmt->execute([$request_id]);

                $feedback = 'Cererea a fost respinsă.';
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Filtre / interogare cereri
$statusFilter = $_GET['status'] ?? 'in_asteptare';
$allowed = ['in_asteptare','aprobat','respins','all'];
if (!in_array($statusFilter, $allowed)) $statusFilter = 'in_asteptare';

// Detectam daca coloana `created_at` exista in tabela `cereri` si alegem ORDERE BY potrivit
$colStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cereri' AND COLUMN_NAME = 'created_at'");
$colStmt->execute();
$hasCreated = $colStmt->fetchColumn() > 0;
$orderBy = $hasCreated ? 'cereri.created_at DESC' : 'cereri.id DESC';

if ($statusFilter === 'all') {
    $stmt = $pdo->prepare("SELECT cereri.*, users.username, users.email, caini.nume AS caine_nume, caini.rasa AS caine_rasa FROM cereri JOIN users ON cereri.user_id = users.id JOIN caini ON cereri.caine_id = caini.id ORDER BY " . $orderBy);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT cereri.*, users.username, users.email, caini.nume AS caine_nume, caini.rasa AS caine_rasa FROM cereri JOIN users ON cereri.user_id = users.id JOIN caini ON cereri.caine_id = caini.id WHERE cereri.status = ? ORDER BY " . $orderBy);
    $stmt->execute([$statusFilter]);
}
$cereri = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Cereri Adoptie</title>
    <link rel="stylesheet" href="style.css">
    <style>
    .admin-container { max-width: 1000px; margin: 30px auto; padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #ddd; text-align: left; }
    .actions form { display: inline-block; margin-right: 8px; }
    .filter-links a { margin-right: 8px; }
    .feedback { padding: 10px; background: #eaffea; border: 1px solid #b7f0b7; color: #084d10; }
    .error { padding: 10px; background: #ffdede; border: 1px solid #f0b0b0; color: #8a0000; }
    .status-pill { padding: 6px 10px; border-radius: 12px; font-weight: 600; }
    .status-in_asteptare { background:#fff7d6; }
    .status-aprobat { background:#eaffea; }
    .status-respins { background:#ffdede; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="admin-container">
        <h1>⚙️ Admin - Cereri de adopție</h1>

        <div class="filter-links">
            <strong>Filtre:</strong>
            <a href="admin_dashboard.php?status=in_asteptare">În așteptare</a>
            <a href="admin_dashboard.php?status=aprobat">Aprobate</a>
            <a href="admin_dashboard.php?status=respins">Respinse</a>
            <a href="admin_dashboard.php?status=all">Toate</a>
        </div>

        <?php if ($feedback): ?>
            <div class="feedback"><?php echo htmlspecialchars($feedback); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Utilizator</th>
                    <th>Câine</th>
                    <th>Mesaj</th>
                    <th>Stare</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cereri as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars(isset($r['created_at']) ? date('d.m.Y H:i', strtotime($r['created_at'])) : '—'); ?></td>
                        <td><?php echo htmlspecialchars($r['username']) . '<br><small>' . htmlspecialchars($r['email']) . '</small>'; ?></td>
                        <td><?php echo htmlspecialchars($r['caine_nume']) . ' • ' . htmlspecialchars($r['caine_rasa']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($r['mesaj'])); ?></td>
                        <td>
                            <span class="status-pill status-<?php echo $r['status']; ?>"><?php echo htmlspecialchars($r['status']); ?></span>
                        </td>
                        <td class="actions">
                            <?php if ($r['status'] === 'in_asteptare'): ?>
                                <form method="POST" onsubmit="return confirm('Aprobi această cerere?');">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit">Aprobă</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Respinge această cerere?');">
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit">Respinge</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>
</html>
