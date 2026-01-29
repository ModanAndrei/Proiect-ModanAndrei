<?php
require 'db.php';
session_start();
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $user_data = $stmt->fetch();

    if ($user_data && password_verify($pass, $user_data['password'])) {
        if($user_data['is_banned'] == 1) {
             $mesaj = "Contul tău a fost BANAT de un admin.";
        } else {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];
            header("Location: index.php");
            exit();
        }
    } else {
        $mesaj = "Nume sau parolă incorecte!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="form-container">
        <h2>Autentificare</h2>
        <?php if($mesaj) echo "<div class='alert'>$mesaj</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit">Intră în cont</button>
        </form>
    </div>
</body>
</html>