<?php
require 'db.php';
$mesaj = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$user, $email]);
    
    if ($stmt->rowCount() > 0) {
        $mesaj = "Utilizatorul sau email-ul există deja!";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$user, $email, $pass_hash])) {
            header("Location: login.php");
            exit();
        } else {
            $mesaj = "Eroare la înregistrare.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="form-container">
        <h2>Înregistrare</h2>
        <?php if($mesaj) echo "<div class='alert'>$mesaj</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nume utilizator" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit">Creează Cont</button>
        </form>
    </div>
</body>
</html>