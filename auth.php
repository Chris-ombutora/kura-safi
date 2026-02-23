<?php
require 'config.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $student_id = trim($_POST['student_id'] ?? '');
    $password = $_POST['password'];
    $action = $_POST['action'];

    if ($action === 'register') {
        if (strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, student_id, password)
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$username, $student_id, $hash]);
                $message = "Registration successful. Please login.";
            } catch (PDOException $e) {
                $message = "Username or Student ID already exists.";
            }
        }
    }

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid login credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kura Safi | Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="text-success">Kura Safi Access</h4>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input class="form-control mb-2" name="username" placeholder="Username" required>
                <input class="form-control mb-2" name="student_id" placeholder="Student ID (register only)">
                <input type="password" class="form-control mb-2" name="password" placeholder="Password" required>

                <button name="action" value="login" class="btn btn-success">Login</button>
                <button name="action" value="register" class="btn btn-outline-success">Register</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>