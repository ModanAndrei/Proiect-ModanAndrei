<?php
$host = 'mysql';
$db   = 'studenti';
$user = 'user';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Inceput migrare...<br>";

    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (!in_array('profile_image', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) AFTER role");
        echo "✓ Coloana 'profile_image' adaugata<br>";
    } else {
        echo "- Coloana 'profile_image' exista deja<br>";
    }

    if (!in_array('about', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN about TEXT AFTER profile_image");
        echo "✓ Coloana 'about' adaugata<br>";
    } else {
        echo "- Coloana 'about' exista deja<br>";
    }

    echo "<br><strong>Migrare completata!</strong>";

} catch (PDOException $e) {
    die("Eroare: " . $e->getMessage());
}
?>
