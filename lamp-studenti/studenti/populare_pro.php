<?php
require 'db.php';

$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("TRUNCATE TABLE caini");
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "<h2>Începem generarea a 50 de câini unici...</h2>";

$api_url = "https://dog.ceo/api/breeds/image/random/50";
$response = file_get_contents($api_url);
$data = json_decode($response, true);
$lista_imagini = $data['message']; 

$nume_lista = [
    'Bruno', 'Rex', 'Max', 'Bella', 'Luna', 'Daisy', 'Charlie', 'Coco', 'Buddy', 'Milo',
    'Archie', 'Ollie', 'Toby', 'Jack', 'Teddy', 'Leo', 'Jax', 'Loki', 'Winston', 'Murphy',
    'Grivei', 'Azorel', 'Lăbuș', 'Pufi', 'Ursu', 'Negruț', 'Spot', 'Biju', 'Sasha', 'Linda',
    'Rita', 'Dolly', 'Zoe', 'Lady', 'Mura', 'Roco', 'Spike', 'Thor', 'Zeus', 'Ares',
    'Hera', 'Athena', 'Oscar', 'Sam', 'Koda', 'Finn', 'Diesel', 'Apollo', 'Simba', 'Nala',
    'Molly', 'Penny', 'Ruby', 'Rosie', 'Sadie', 'Bailey', 'Lola', 'Cleo', 'Pippa', 'Ziggy'
];

shuffle($nume_lista);

$adj_personalitate = ['Foarte energic', 'Extrem de loial', 'Putin timid', 'Prietenos cu toata lumea', 'Un adevarat paznic', 'Jucaus si vesel', 'Calm si intelept', 'Curios din fire', 'Bland si rabdator', 'Independent si curajos'];
$adj_activitate = ['adora sa alerge in parc.', 'iubeste plimbarile lungi.', 'preferata sa doarma pe canapea.', 'invata trucuri foarte repede.', 'este perfect pentru apartament.', 'are nevoie de o curte mare.', 'se intelege bine cu alte animale.', 'adora copiii mici.'];
$adj_extra = ['Are vaccinurile la zi.', 'A fost gasit pe strada.', 'Vine dintr-o familie iubitoare.', 'Cauta un stapan rabdator.', 'Este microcipat.', 'Mananca orice cu placere.'];

$sql = "INSERT INTO caini (nume, rasa, varsta, descriere, imagine, status) VALUES (?, ?, ?, ?, ?, 'disponibil')";
$stmt = $pdo->prepare($sql);

$contor = 0;

foreach ($lista_imagini as $index => $img_url) {
    if ($contor >= 50) break;
    
    $parti_link = explode('/', $img_url);
    $rasa_raw = $parti_link[4];
    
    $rasa = str_replace('-', ' ', ucwords($rasa_raw, '-'));
    
    $personalitate = $adj_personalitate[array_rand($adj_personalitate)];
    $activitate = $adj_activitate[array_rand($adj_activitate)];
    $extra = $adj_extra[array_rand($adj_extra)];
    
    $descriere = $personalitate . '. ' . $activitate . ' ' . $extra;
    
    $varsta = rand(1, 10) . ' ani';
    
    $index_nume = $contor % count($nume_lista);
    $nume = $nume_lista[$index_nume];
    

    try {
        $stmt->execute([$nume, $rasa, $varsta, $descriere, $img_url]);
        echo "✓ Adaugat: <strong>$nume</strong> ($rasa, $varsta)<br>";
    } catch (Exception $e) {
        echo "✗ Eroare pentru $nume: " . $e->getMessage() . "<br>";
    }
    
    $contor++;
}

echo "<br><h2>Populare finalizata! Total: $contor caini adaugati.</h2>";
echo '<p><a href="index.php">Inapoi la pagina principala</a></p>'; 