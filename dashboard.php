<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$user_id = $_SESSION['user_id'];

$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

$positions = $pdo->query("SELECT * FROM positions")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kura Safi | Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <a href="logout.php" class="btn btn-danger btn-sm float-end">Logout</a>
    <h3 class="text-success">Kura Safi Voting Dashboard</h3>

    <div id="msg"></div>

    <?php foreach ($positions as $pos): ?>
        <?php
        $voted = ($pos['name'] === 'President')
            ? $user['voted_president']
            : $user['voted_vice'];

        $candidates = $pdo->prepare("SELECT * FROM candidates WHERE position_id = ?");
        $candidates->execute([$pos['id']]);
        ?>

        <div class="card my-3">
            <div class="card-header bg-success text-white">
                <?= $pos['name'] ?>
            </div>
            <div class="card-body">
                <?php if ($voted): ?>
                    <div class="alert alert-success">You have already voted.</div>
                <?php else: ?>
                    <?php foreach ($candidates as $c): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="candidate_<?= $pos['id'] ?>"
                                   value="<?= $c['id'] ?>">
                            <label class="form-check-label">
                                <?= htmlspecialchars($c['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button class="btn btn-success mt-2" onclick="vote(<?= $pos['id'] ?>)">
                        Submit Vote
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <a href="results.php" class="btn btn-outline-success">View Results</a>
</div>

<script>
function vote(positionId) {
    let selected = document.querySelector(
        'input[name="candidate_' + positionId + '"]:checked'
    );

    if (!selected) {
        alert("Please select a candidate.");
        return;
    }

    fetch("vote_handler.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({candidate_id: selected.value})
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("msg").innerHTML =
            `<div class="alert alert-${data.status}">${data.message}</div>`;
        if (data.status === "success") location.reload();
    });
}
</script>

</body>
</html>