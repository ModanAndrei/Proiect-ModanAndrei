<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <div class="nav-left">
        <a href="index.php" class="logo logo-text-only" aria-label="Labuta fericita - Acasa">
            <span class="logo-text">Labuta <span class="logo-highlight">fericită</span></span>
        </a>
        <ul class="menu menu-left">
            <li><a href="index.php">Acasa</a></li>
            <li><a href="adopta.php">Adopta</a></li>
            <li><a href="doneaza.php">Doneaza</a></li>
        </ul>
    </div>
    
    <ul class="menu menu-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="user-profile">
                <a href="profile.php" class="profile-link">
                    <img src="<?= htmlspecialchars($_SESSION['profile_image'] ?? 'assets/default-avatar.png') ?>" alt="<?= htmlspecialchars($_SESSION['username']) ?>" class="avatar">
                    <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <li><a href="admin_dashboard.php" style="color: #ffcccc;">⚙️ Admin</a></li>
                <li><a href="admin_dogs.php" style="color: #ffcccc;">📝 Gestionare Anunțuri</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Iesire</a></li>
        <?php else: ?>
            <li><a href="login.php">Autentificare</a></li>
            <li><a href="register.php">Inregistrare</a></li>
        <?php endif; ?>
    </ul>
</nav>