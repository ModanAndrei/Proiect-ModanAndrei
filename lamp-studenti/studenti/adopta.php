<?php
session_start();
require 'db.php';

if (isset($_GET['adopt'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $caine_id = intval($_GET['adopt']);
    header('Location: adopt_form.php?caine_id=' . $caine_id);
    exit;
} 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopta - Labuta Fericita</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-wrap { max-width: 1100px; margin: 20px auto 30px; padding: 0 20px; width:100%; }
        .filter-form { display: flex; gap: 12px; align-items: center; background: #fff; padding: 12px; border-radius: 8px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); width:100%; max-width:900px; margin:0 auto; }
        .filter-input, .filter-select { padding: 10px 12px; border-radius: 6px; border: 1px solid #e1e1e1; font-size: 0.95rem; }
        .filter-input.small { width: 160px; }
        .filter-btn { background: #ff9800; color: white; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .filter-reset { margin-left: 8px; color: #777; text-decoration: underline; font-size: 0.95rem; }
        @media (max-width: 768px) {
            .filter-form { flex-direction: column; align-items: stretch; }
            .filter-reset { margin-left: 0; text-align: center; }
            .filter-input.small { width: 100%; }
            .filter-select, .filter-input { width: 100%; }
        }

.cards-grid { max-width: 1100px; margin: 20px auto 40px; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; width:100%; box-sizing:border-box; }
        .card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 18px rgba(0,0,0,0.08); display: flex; flex-direction: column; min-height: 340px; }
        .card img { width: 100%; height: 180px; object-fit: cover; display: block; }
        .card-body { padding: 16px; display:flex; flex-direction:column; gap:8px; }
        .card h2 { margin: 4px 0 0; font-size: 1.35rem; text-align:center; }
        .card .detalii { text-align:center; color:#777; font-size:0.95rem; margin-top:4px; }
        .descriere { color:#666; font-size:0.95rem; margin:6px 0 12px; text-align:center; }
        .adopt-btn { background:#ff9800; color:white; border:none; padding:10px 14px; border-radius:6px; cursor:pointer; font-weight:700; align-self:center; }

        header { padding-bottom: 24px; }

.pagination { list-style:none; display:flex; gap:8px; justify-content:center; align-items:center; padding:0; margin:20px 0; }
        .pagination li { display:inline-block; }
        .pagination a, .pagination span { display:inline-block; padding:8px 12px; border-radius:6px; text-decoration:none; color:#333; border:1px solid #eee; background:#fff; }
        .pagination a:hover { background:#f7f7f7; }
        .pagination .active span { background:#4CAF50; color:white; border-color:#4CAF50; font-weight:700; }
        .pagination .dots { padding:8px 6px; color:#999; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <header>
        <h1>🐶 Adopta un Prieten</h1>
        <p>Alege cainele perfect pentru tine si familia ta!</p>
    </header>

    <div class="container">
        <?php
        if (isset($_GET['success'])) {
            echo '<p style="color:green; padding:10px; background:#eaffea; border:1px solid #b7f0b7;">Cererea a fost trimisă cu succes. Veți fi contactat pentru pașii următori.</p>';
        }
        if (isset($_GET['error'])) {
            $err = $_GET['error'];
            $map = [
                'invalid_id' => 'ID invalid',
                'not_available' => 'Câinele nu este disponibil',
                'already_requested' => 'Ai deja o cerere în așteptare'
            ];
            $msg = isset($map[$err]) ? $map[$err] : 'Eroare necunoscută';
            echo '<p style="color:#8a0000; padding:10px; background:#ffdede; border:1px solid #f0b0b0;">' . htmlspecialchars($msg) . '</p>';
        }

        $per_page = 8;
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * $per_page;

        $filter_rasa = trim($_GET['rasa'] ?? '');
        $filter_varsta = trim($_GET['varsta'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $breedsStmt = $pdo->query("SELECT DISTINCT rasa FROM caini WHERE status = 'disponibil' AND rasa IS NOT NULL AND rasa != '' ORDER BY rasa");
        $breeds = $breedsStmt->fetchAll(PDO::FETCH_COLUMN);

        $where = ["status = 'disponibil'"];
        $params = [];
        if ($filter_rasa !== '') { $where[] = 'rasa = ?'; $params[] = $filter_rasa; }
        if ($filter_varsta !== '') { $where[] = 'varsta LIKE ?'; $params[] = "%$filter_varsta%"; }
        if ($q !== '') { $where[] = 'nume LIKE ?'; $params[] = "%$q%"; }

        $where_sql = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM caini WHERE $where_sql");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $total_pages = max(1, ceil($total / $per_page));

        $sql = "SELECT * FROM caini WHERE $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $p) {
            $stmt->bindValue($paramIndex, $p, PDO::PARAM_STR);
            $paramIndex++;
        }
        $stmt->bindValue($paramIndex, (int)$per_page, PDO::PARAM_INT);
        $paramIndex++;
        $stmt->bindValue($paramIndex, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $caini = $stmt->fetchAll();

        echo '<div class="filter-wrap">';
        echo '<form method="GET" class="filter-form">';
        echo '<input type="text" name="q" placeholder="Caută nume..." value="' . htmlspecialchars($q) . '" class="filter-input">';
        echo '<select name="rasa" class="filter-select"><option value="">Toate rasele</option>';
        foreach ($breeds as $b) {
            echo '<option value="' . htmlspecialchars($b) . '"' . ($filter_rasa === $b ? ' selected' : '') . '>' . htmlspecialchars($b) . '</option>';
        }
        echo '</select>';
        echo '<input type="text" name="varsta" placeholder="Vârsta (ex: 2 ani)" value="' . htmlspecialchars($filter_varsta) . '" class="filter-input small">';
        echo '<button type="submit" class="filter-btn">Filtrează</button>';
        echo '<a href="adopta.php" class="filter-reset">Reset</a>';
        echo '</form>';
        echo '</div>';

        if (count($caini) > 0) {
            echo '<div class="cards-grid">';
            foreach ($caini as $caine) {
                echo '<div class="card">';
                echo '<img src="' . htmlspecialchars($caine['imagine']) . '" alt="' . htmlspecialchars($caine['nume']) . '">';
                echo '<div class="card-body">';
                echo '<h2>' . htmlspecialchars($caine['nume']) . '</h2>';
                echo '<div class="detalii">' . htmlspecialchars($caine['rasa']) . ' • ' . htmlspecialchars($caine['varsta']) . '</div>';
                echo '<div class="descriere">' . htmlspecialchars($caine['descriere']) . '</div>';
                echo '<div style="margin-top:auto"><button class="adopt-btn" onclick="adoptCaine(' . $caine['id'] . ')">Adopta</button></div>';

                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo '<div style="display:flex; gap:8px; justify-content:center; margin-top:8px;">';
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
            echo '</div>';

            $baseParams = []; 
            if ($filter_rasa !== '') $baseParams['rasa'] = $filter_rasa;
            if ($filter_varsta !== '') $baseParams['varsta'] = $filter_varsta;
            if ($q !== '') $baseParams['q'] = $q;

            echo '<nav aria-label="Paginare" style="margin-top:18px;"><ul class="pagination">';

            if ($page > 1) {
                $baseParams['page'] = $page - 1;
                echo '<li><a href="?' . http_build_query($baseParams) . '">◀ Prev</a></li>';
            }

            $visible = 7;
            $start = max(1, $page - intval(floor($visible/2)));
            $end = min($total_pages, $start + $visible - 1);
            if ($end - $start + 1 < $visible) {
                $start = max(1, $end - $visible + 1);
            }

            if ($start > 1) {
                $baseParams['page'] = 1;
                echo '<li><a href="?' . http_build_query($baseParams) . '">1</a></li>';
                if ($start > 2) echo '<li class="dots">…</li>';
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    echo '<li class="active"><span>' . $i . '</span></li>';
                } else {
                    $baseParams['page'] = $i;
                    echo '<li><a href="?' . http_build_query($baseParams) . '">' . $i . '</a></li>';
                }
            }

            if ($end < $total_pages) {
                if ($end < $total_pages - 1) echo '<li class="dots">…</li>';
                $baseParams['page'] = $total_pages;
                echo '<li><a href="?' . http_build_query($baseParams) . '">' . $total_pages . '</a></li>';
            }

            if ($page < $total_pages) {
                $baseParams['page'] = $page + 1;
                echo '<li><a href="?' . http_build_query($baseParams) . '">Next ▶</a></li>';
            }

            echo '</ul></nav>';

        } else {
            echo '<p style="text-align: center; grid-column: 1/-1; padding: 40px;">Nu sunt caini disponibili pentru adoptie in acest moment.</p>';
        }
        ?>
    </div>

    <script>
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
