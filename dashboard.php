<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch();

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

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg']['type']; ?>">
            <?= $_SESSION['msg']['text']; ?>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <?php foreach ($positions as $pos): ?>

        <?php
        $voted = ($pos['name'] === 'President')
            ? $user['voted_president']
            : $user['voted_vice'];

        $candidatesStmt = $pdo->prepare("SELECT * FROM candidates WHERE position_id = ?");
        $candidatesStmt->execute([$pos['id']]);
        $candidates = $candidatesStmt->fetchAll();
        ?>

        <div class="card my-3">
            <div class="card-header bg-success text-white">
                <?= htmlspecialchars($pos['name']); ?>
            </div>
            <div class="card-body">

                <?php if ($voted): ?>
                    <div class="alert alert-success">
                        You have already voted.
                    </div>
                <?php else: ?>

                    <form method="POST" action="voter_handler.php">

                        <?php foreach ($candidates as $c): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="candidate_id"
                                       value="<?= $c['id']; ?>"
                                       required>
                                <label class="form-check-label">
                                    <?= htmlspecialchars($c['name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-success mt-3">
                            Submit Vote
                        </button>

                    </form>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>

    <a href="results.php" class="btn btn-outline-success">View Results</a>

</div>

</body>
</html>
