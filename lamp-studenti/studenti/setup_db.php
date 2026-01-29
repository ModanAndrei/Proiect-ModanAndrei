<?php
$host = 'mysql'; 
$db   = 'studenti';
$user = 'user';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conexiune reușită!<br>";

    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        is_banned TINYINT(1) DEFAULT 0,
        profile_image VARCHAR(255),
        about TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_users);
    echo "Tabel 'users' creat.<br>";

    $sql_dogs = "CREATE TABLE IF NOT EXISTS caini (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nume VARCHAR(50) NOT NULL,
        rasa VARCHAR(50),
        varsta VARCHAR(20),
        descriere TEXT,
        imagine VARCHAR(255),
        status ENUM('disponibil', 'adoptat') DEFAULT 'disponibil'
    )";
    $pdo->exec($sql_dogs);
    echo "Tabel 'caini' creat.<br>";

    $sql_requests = "CREATE TABLE IF NOT EXISTS cereri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        caine_id INT,
        mesaj TEXT,
        applicant_name VARCHAR(100),
        phone VARCHAR(50),
        address VARCHAR(255),
        status ENUM('in_asteptare', 'aprobat', 'respins') DEFAULT 'in_asteptare',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (caine_id) REFERENCES caini(id)
    )";
    $pdo->exec($sql_requests);
    echo "Tabel 'cereri' creat.<br>";

    $stmt = $pdo->prepare("SELECT count(*) FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if ($stmt->fetchColumn() == 0) {
        $parola_hash = password_hash("admin123", PASSWORD_DEFAULT);
        $sql_admin = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql_admin)->execute(['admin', 'admin@proiect.ro', $parola_hash, 'admin']);
        echo "Cont ADMIN creat (User: admin / Pass: admin123)<br>";
    }


    $stmt = $pdo->query("SELECT count(*) FROM caini");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO caini (nume, rasa, varsta, descriere, imagine) VALUES 
        ('Azorel', 'Metis', '2 ani', 'Jucaus si rapid.', 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=300&q=80'),
        ('Bella', 'Labrador', '4 ani', 'Blanda cu copiii.', 'https://images.unsplash.com/photo-1591769225440-811ad7d6eca6?auto=format&fit=crop&w=300&q=80')");
        echo "Caini de test adaugati.<br>";
    }

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>