<?php
require 'config.php';

$results = $pdo->query("
    SELECT c.name, p.name AS position, COUNT(v.id) AS total
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
    JOIN positions p ON c.position_id = p.id
    GROUP BY c.id
")->fetchAll();

$totals = [];
foreach ($results as $r) {
    $totals[$r['position']] = ($totals[$r['position']] ?? 0) + $r['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kura Safi | Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h3 class="text-success">Kura Safi Election Results</h3>

    <?php foreach ($results as $r):
        $percent = $totals[$r['position']] > 0
            ? ($r['total'] / $totals[$r['position']]) * 100
            : 0;
    ?>
        <div class="card my-2">
            <div class="card-body">
                <strong><?= $r['name'] ?></strong>
                <small>(<?= $r['position'] ?>)</small>
                <div class="progress mt-2">
                    <div class="progress-bar bg-success"
                         style="width: <?= round($percent) ?>%">
                        <?= $r['total'] ?> votes
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <a href="dashboard.php" class="btn btn-outline-success mt-3">Back</a>
</div>

</body>
</html>