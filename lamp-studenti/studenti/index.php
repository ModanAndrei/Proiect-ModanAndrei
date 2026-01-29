<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labuta Fericita - Adopta un Prieten</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        .hero h1 {
            font-size: 2.5rem;
            margin: 0 0 10px 0;
        }
        .hero p {
            font-size: 1.2rem;
            margin: 0;
        }
        .news-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .news-section h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .news-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .news-card-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
        }
        .news-card-content {
            padding: 20px;
        }
        .news-card-date {
            color: #4CAF50;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .news-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin: 0 0 8px 0;
        }
        .news-card-excerpt {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }
        .featured-dogs {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .featured-dogs h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .carousel-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .carousel-viewport {
            flex: 1;
            overflow: hidden;
            width: 100%;
        }
        .dogs-carousel {
            display: flex;
            gap: 20px;
            transition: transform 0.5s ease;
            width: 100%;
        }
        .dog-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            flex: 0 0 280px;
        }
        .dog-card:hover {
            transform: scale(1.05);
        }
        .dog-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .dog-card-info {
            padding: 15px;
            text-align: center;
        }
        .dog-card-info h3 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .dog-card-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .dog-card-info button {
            margin-top: 10px;
            width: 100%;
        }
        .carousel-arrow {
            background: rgba(76, 175, 80, 0.9);
            color: white;
            border: none;
            padding: 15px 18px;
            font-size: 1.5rem;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s ease;
            flex-shrink: 0;
            height: fit-content;
        }
        .carousel-arrow:hover {
            background: rgba(76, 175, 80, 1);
        }
        .stats {
            background: linear-gradient(135deg, #f9f9f9 0%, #f0f0f0 100%);
            padding: 40px 20px;
            margin: 40px 0;
        }
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }
        .stat-item h3 {
            font-size: 2.5rem;
            color: #4CAF50;
            margin: 0;
        }
        .stat-item p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .cta-section {
            background: #4CAF50;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .cta-section h2 {
            margin-top: 0;
            font-size: 2rem;
        }
        .cta-section a {
            display: inline-block;
            background: white;
            color: #4CAF50;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            transition: transform 0.3s ease;
        }
        .cta-section a:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- CAINI FEATURED - TOP -->
    <div class="featured-dogs" style="margin-top: 20px; margin-bottom: 40px;">
        <h2>🐕 Caini Disponibili pentru Adoptie</h2>
        <div class="carousel-container">
            <button class="carousel-arrow carousel-prev">❮</button>
            
            <div class="carousel-viewport">
                <div class="dogs-carousel">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM caini WHERE status = 'disponibil' ORDER BY RAND() LIMIT 10");
                    $caini = $stmt->fetchAll();

                    if (count($caini) > 0) {
                        foreach ($caini as $caine) {
                            echo '<div class="dog-card">';
                            echo '<img src="' . htmlspecialchars($caine['imagine']) . '" alt="' . htmlspecialchars($caine['nume']) . '">';
                            echo '<div class="dog-card-info">';
                            echo '<h3>' . htmlspecialchars($caine['nume']) . '</h3>';
                            echo '<p>' . htmlspecialchars($caine['rasa']) . ' • ' . htmlspecialchars($caine['varsta']) . '</p>';
                            echo '<button onclick="adoptCaine(' . $caine['id'] . ')">Adopta</button>';

                            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                                echo '<div style="margin-top:8px; display:flex; gap:8px; justify-content:center;">';
                                echo '<a href="edit_dog.php?id=' . $caine['id'] . '" style="background:#4CAF50;color:white;padding:6px 8px;border-radius:6px;text-decoration:none;">Editează</a>';
                                echo '<form method="POST" action="admin_dogs.php" onsubmit="return confirm(\'Ștergi acest anunț?\');" style="display:inline;margin:0;">';
                                echo '<input type="hidden" name="action" value="delete">';
                                echo '<input type="hidden" name="dog_id" value="' . $caine['id'] . '">';
                                echo '<button type="submit" style="background:#ff4d4d;color:white;border:none;padding:6px 8px;border-radius:6px;cursor:pointer;">Șterge</button>';
                                echo '</form>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="grid-column: 1/-1; text-align: center; color: #999;">Nu sunt caini disponibili pentru adoptie.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <button class="carousel-arrow carousel-next">❯</button>
        </div>
    </div>

    <!-- HERO SECTION -->
        <h1>🐾 Labuta Fericita</h1>
        <p>Gaseste-ti prietenul perfect si salveaza o viata</p>
    </div>

    <div class="news-section">
        <h2>📰 Noutati Recente</h2>
        <div class="news-grid">
            <div class="news-card">
                <div class="news-card-image" style="background: linear-gradient(135deg, #4CAF50, #45a049);"></div>
                <div class="news-card-content">
                    <div class="news-card-date">29 Ianuarie 2026</div>
                    <h3 class="news-card-title">Noua Campanie de Donatie</h3>
                    <p class="news-card-excerpt">Lansam o noua campanie pentru a aduna fonduri pentru ingrijirea cainilor. Orice donatie conteaza!</p>
                </div>
            </div>
            <div class="news-card">
                <div class="news-card-image" style="background: linear-gradient(135deg, #FF9800, #F57C00);"></div>
                <div class="news-card-content">
                    <div class="news-card-date">27 Ianuarie 2026</div>
                    <h3 class="news-card-title">Azorel Gasit o Familie!</h3>
                    <p class="news-card-excerpt">Azorel, unul din cainii nostri, a fost adoptat de o familie minunata. Suntem asa de fericiti!</p>
                </div>
            </div>
            <div class="news-card">
                <div class="news-card-image" style="background: linear-gradient(135deg, #2196F3, #1976D2);"></div>
                <div class="news-card-content">
                    <div class="news-card-date">25 Ianuarie 2026</div>
                    <h3 class="news-card-title">Voluntar pentru o Zi</h3>
                    <p class="news-card-excerpt">Vrei sa ne ajuti? Ofertam programe de voluntariat pentru cei care vor sa contribuie activ.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="stats">
        <div class="stats-container">
            <div class="stat-item">
                <h3>150+</h3>
                <p>Caini Salvati</p>
            </div>
            <div class="stat-item">
                <h3>3500+</h3>
                <p>Adopteri Reușite</p>
            </div>
            <div class="stat-item">
                <h3>200+</h3>
                <p>Voluntari Activi</p>
            </div>
        </div>
    </div>

    <div class="featured-dogs">
        <h2>🐕 Caini Disponibili pentru Adoptie</h2>
        <div class="carousel-container">
            <button class="carousel-arrow carousel-prev">❮</button>
            
            <div class="carousel-viewport">
                <div class="dogs-carousel">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM caini WHERE status = 'disponibil' ORDER BY RAND() LIMIT 10");
                    $caini = $stmt->fetchAll();

                    if (count($caini) > 0) {
                        foreach ($caini as $caine) {
                            echo '<div class="dog-card">';
                            echo '<img src="' . htmlspecialchars($caine['imagine']) . '" alt="' . htmlspecialchars($caine['nume']) . '">';
                            echo '<div class="dog-card-info">';
                            echo '<h3>' . htmlspecialchars($caine['nume']) . '</h3>';
                            echo '<p>' . htmlspecialchars($caine['rasa']) . ' • ' . htmlspecialchars($caine['varsta']) . '</p>';
                            echo '<p style="font-size: 0.85rem; color: #999; margin-top: 5px;">' . htmlspecialchars($caine['descriere']) . '</p>';
                            echo '<button onclick="adoptCaine(' . $caine['id'] . ')">Adopta</button>';

                            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                                echo '<div style="margin-top:8px; display:flex; gap:8px; justify-content:center;">';
                                echo '<a href="edit_dog.php?id=' . $caine['id'] . '" style="background:#4CAF50;color:white;padding:6px 8px;border-radius:6px;text-decoration:none;">Editează</a>';
                                echo '<form method="POST" action="admin_dogs.php" onsubmit="return confirm(\'Ștergi acest anunț?\');" style="display:inline;margin:0;">';
                                echo '<input type="hidden" name="action" value="delete">';
                                echo '<input type="hidden" name="dog_id" value="' . $caine['id'] . '">';
                                echo '<button type="submit" style="background:#ff4d4d;color:white;border:none;padding:6px 8px;border-radius:6px;cursor:pointer;">Șterge</button>';
                                echo '</form>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="grid-column: 1/-1; text-align: center; color: #999;">Nu sunt caini disponibili pentru adoptie.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <button class="carousel-arrow carousel-next">❯</button>
        </div>
    </div>

    <div class="cta-section">
        <h2>Vrei sa Adoptezi?</h2>
        <p>Vedeti toti cainii noștri disponibili și gasiti-va noul prieten cel mai bun!</p>
        <a href="adopta.php">Exploreaza Caini</a>
        <span style="margin: 0 15px;">|</span>
        <a href="doneaza.php" style="background: transparent; color: white; border: 2px solid white;">Doneaza</a>
    </div>

    <script>
        // Simple infinite carousel - per-carousel initialization to avoid duplicated IDs causing incorrect counts
        document.querySelectorAll('.carousel-container').forEach(container => {
            const carousel = container.querySelector('.dogs-carousel');
            if (!carousel) return;
            const dogCards = carousel.querySelectorAll('.dog-card');
            let currentIndex = 0;
            const totalCards = dogCards.length;
            let autoPlayInterval;
            const cardWidth = 280;
            const gap = 20;

            function updateCarousel() {
                const offset = -(currentIndex * (cardWidth + gap));
                carousel.style.transform = `translateX(${offset}px)`;
            }

            function nextSlide() {
                if (totalCards === 0) return;
                currentIndex = (currentIndex + 1) % totalCards;
                carousel.style.transition = 'transform 0.5s ease';
                updateCarousel();
                resetAutoPlay();
            }

            function prevSlide() {
                if (totalCards === 0) return;
                currentIndex = (currentIndex - 1 + totalCards) % totalCards;
                carousel.style.transition = 'transform 0.5s ease';
                updateCarousel();
                resetAutoPlay();
            }

            function autoPlay() {
                autoPlayInterval = setInterval(() => {
                    nextSlide();
                }, 4000);
            }

            function resetAutoPlay() {
                clearInterval(autoPlayInterval);
                autoPlay();
            }

            const nextBtn = container.querySelector('.carousel-next');
            const prevBtn = container.querySelector('.carousel-prev');
            if (nextBtn) nextBtn.addEventListener('click', nextSlide);
            if (prevBtn) prevBtn.addEventListener('click', prevSlide);

            autoPlay();
        });

        // Standard adoptCaine function
        function adoptCaine(caineId) {
            <?php if (isset($_SESSION['user_id'])): ?>
                window.location.href = 'adopt_form.php?caine_id=' + caineId;
            <?php else: ?>
                alert('Trebuie sa te conectezi pentru a putea adopta!');
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</body>
</html>