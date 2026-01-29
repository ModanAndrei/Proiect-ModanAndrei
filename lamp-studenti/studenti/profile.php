<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$section = $_GET['section'] ?? $_POST['section'] ?? 'about';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die('Utilizator nu gasit.');
}

$userRequests = []; 
if ($section === 'requests') {
    $stmt = $pdo->prepare("SELECT cereri.*, caini.nume AS caine_nume, caini.rasa AS caine_rasa FROM cereri LEFT JOIN caini ON cereri.caine_id = caini.id WHERE cereri.user_id = ? ORDER BY cereri.id DESC");
    $stmt->execute([$user_id]);
    $userRequests = $stmt->fetchAll();
}

$message = '';
$error = '';

if (isset($_GET['success']) && $_GET['success'] === 'edited') {
    $message = 'Cererea a fost actualizată cu succes.';
}
if (isset($_GET['error']) && $_GET['error'] === 'cannot_edit') {
    $error = 'Această cerere nu poate fi editată.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($section === 'about') {
        $about = $_POST['about'] ?? '';
        $stmt = $pdo->prepare("UPDATE users SET about = ? WHERE id = ?");
        if ($stmt->execute([$about, $user_id])) {
            $message = 'Despre tine a fost actualizat!';
            $user['about'] = $about;
        }
    } elseif ($section === 'security') {
        if (isset($_POST['action']) && $_POST['action'] === 'email') {
            $new_email = $_POST['new_email'] ?? '';
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalid!';
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$new_email, $user_id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Email-ul este deja folosit!';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    if ($stmt->execute([$new_email, $user_id])) {
                        $message = 'Email actualizat cu succes!';
                        $_SESSION['email'] = $new_email;
                        $user['email'] = $new_email;
                    }
                }
            }
        }
        if (isset($_POST['action']) && $_POST['action'] === 'password') {
            $old_password = $_POST['old_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!password_verify($old_password, $user['password'])) {
                $error = 'Parola veche este incorect!';
            } elseif (strlen($new_password) < 6) {
                $error = 'Parola noua trebuie sa aiba minim 6 caractere!';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Parolele nu se potrivesc!';
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed, $user_id])) {
                    $message = 'Parola a fost schimbata cu succes!';
                }
            }
        }
    } elseif ($section === 'profile-picture') {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowed)) {
                $error = 'Doar imagini sunt acceptate! (JPG, PNG, GIF, WebP)';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Fisierul este prea mare! (Max 5MB)';
            } else {
                $filename = 'profile_' . $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $filepath = 'assets/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    if ($user['profile_image'] && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    if ($stmt->execute([$filepath, $user_id])) {
                        $message = 'Poza de profil actualizata cu succes!';
                        $_SESSION['profile_image'] = $filepath;
                        $user['profile_image'] = $filepath;
                    }
                } else {
                    $error = 'Eroare la incarcare! Verifica permisiunile folderului assets.';
                }
            }
        }
    } elseif ($section === 'requests') {
        if (isset($_POST['action']) && $_POST['action'] === 'cancel' && isset($_POST['request_id']) && is_numeric($_POST['request_id'])) {
            $req_id = (int)$_POST['request_id'];
            $stmt = $pdo->prepare("SELECT * FROM cereri WHERE id = ? AND user_id = ?");
            $stmt->execute([$req_id, $user_id]);
            $req = $stmt->fetch();
            if (!$req) {
                $error = 'Cererea nu a fost găsită.';
            } elseif ($req['status'] !== 'in_asteptare') {
                $error = 'Doar cererile în așteptare pot fi anulate.';
            } else {
                $stmt = $pdo->prepare("UPDATE cereri SET status = 'respins' WHERE id = ?");
                if ($stmt->execute([$req_id])) {
                    $message = 'Cererea a fost anulată.';
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilul Meu - Labuta Fericita</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
        }
        .sidebar {
            width: 200px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .sidebar-item {
            display: block;
            padding: 15px 20px;
            border-left: 4px solid transparent;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .sidebar-item:hover {
            background-color: #f5f5f5;
            border-left-color: #4CAF50;
        }
        .sidebar-item.active {
            background-color: #4CAF50;
            color: white;
            border-left-color: #4CAF50;
        }
        .profile-content {
            flex: 1;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #4CAF50;
            object-fit: cover;
        }
        .profile-info h1 {
            margin: 0;
            color: #333;
        }
        .profile-info p {
            margin: 5px 0;
            color: #777;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.3);
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .message {
            padding: 15px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .file-upload input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-upload-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .file-upload-label:hover {
            background-color: #45a049;
        }
        .file-name {
            display: inline-block;
            margin-left: 10px;
            color: #666;
        }
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                display: flex;
                flex-direction: row;
            }
            .sidebar-item {
                flex: 1;
                border-left: none;
                border-bottom: 4px solid transparent;
                text-align: center;
            }
            .sidebar-item:hover,
            .sidebar-item.active {
                border-left: none;
                border-bottom-color: #4CAF50;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="profile-container">
        <div class="sidebar">
            <a href="?section=about" class="sidebar-item <?= $section === 'about' ? 'active' : '' ?>">Despre Mine</a>
            <a href="?section=security" class="sidebar-item <?= $section === 'security' ? 'active' : '' ?>">Securitate</a>
            <a href="?section=profile-picture" class="sidebar-item <?= $section === 'profile-picture' ? 'active' : '' ?>">Poza de Profil</a>
            <a href="?section=requests" class="sidebar-item <?= $section === 'requests' ? 'active' : '' ?>">Cereri Trimise</a>
        </div>

        <div class="profile-content">
            <div class="profile-header">
                <img src="<?= htmlspecialchars(!empty($user['profile_image']) ? $user['profile_image'] : 'assets/default-avatar.png') ?>" alt="Profil" class="profile-avatar">
                <div class="profile-info">
                    <h1><?= htmlspecialchars($user['username']) ?></h1>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-section <?= $section === 'about' ? 'active' : '' ?>">
                <h2>Despre Mine</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="about">Spune-ne ceva despre tine:</label>
                        <textarea name="about" id="about" placeholder="Scrie despre tine, hobby-urile tale, ce te intereseaza..."><?= htmlspecialchars(!empty($user['about']) ? $user['about'] : '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Salveaza Modificari</button>
                </form>
            </div>

            <div class="form-section <?= $section === 'security' ? 'active' : '' ?>">
                <h2>Securitate</h2>
                
                <h3>Schimba Email-ul</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="email">
                    <div class="form-group">
                        <label for="current_email">Email Actual:</label>
                        <input type="email" id="current_email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="new_email">Email Nou:</label>
                        <input type="email" id="new_email" name="new_email" required>
                    </div>
                    <button type="submit" class="btn-submit">Actualizeaza Email</button>
                </form>

                <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">

                <h3>Schimba Parola</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="old_password">Parola Veche:</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Parola Noua:</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirma Parola Noua:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-submit">Schimba Parola</button>
                </form>
            </div>

            <div class="form-section <?= $section === 'profile-picture' ? 'active' : '' ?>">
                <h2>Poza de Profil</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Incarca o poza noua (JPG, PNG, GIF, WebP - Max 5MB):</label>
                        <div class="file-upload">
                            <label for="profile_image" class="file-upload-label">Alege Poza</label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="document.querySelector('.file-name').textContent = this.files[0]?.name || ''">
                            <span class="file-name">Niciun fisier selectat</span>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Incarca Poza</button>
                </form>
            </div>

            <div class="form-section <?= $section === 'requests' ? 'active' : '' ?>">
                <h2>Cereri Trimise</h2>
                <?php if (empty($userRequests)): ?>
                    <p>Nu ai trimis nicio cerere.</p>
                <?php else: ?>
                    <div style="overflow:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:8px;">ID</th>
                                <th style="text-align:left; padding:8px;">Data</th>
                                <th style="text-align:left; padding:8px;">Câine</th>
                                <th style="text-align:left; padding:8px;">Mesaj</th>
                                <th style="text-align:left; padding:8px;">Stare</th>
                                <th style="text-align:left; padding:8px;">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userRequests as $ur): ?>
                                <tr>
                                    <td style="padding:8px; vertical-align:top;"><?php echo $ur['id']; ?></td>
                                    <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars(isset($ur['created_at']) ? date('d.m.Y H:i', strtotime($ur['created_at'])) : '—'); ?></td>
                                    <td style="padding:8px; vertical-align:top;"><?php echo htmlspecialchars($ur['caine_nume'] ?? '—'); ?></td>
                                    <td style="padding:8px; vertical-align:top; max-width:400px;"><?php echo nl2br(htmlspecialchars($ur['mesaj'] ?? '')); ?></td>
                                    <td style="padding:8px; vertical-align:top;">
                                        <span style="padding:6px 10px; border-radius:12px; font-weight:600; background: <?php echo $ur['status'] === 'in_asteptare' ? '#fff7d6' : ($ur['status'] === 'aprobat' ? '#eaffea' : '#ffdede'); ?>;"><?php echo htmlspecialchars($ur['status']); ?></span>
                                    </td>
                                    <td style="padding:8px; vertical-align:top;">
                                        <?php if ($ur['status'] === 'in_asteptare'): ?>
                                            <a href="edit_request.php?id=<?php echo $ur['id']; ?>" style="margin-right:8px;">Editează</a>
                                            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Anulezi cererea?');">
                                                <input type="hidden" name="section" value="requests">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="request_id" value="<?php echo $ur['id']; ?>">
                                                <button type="submit">Anulează</button>
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
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
