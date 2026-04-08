<?php
session_start();

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'appdb';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASSWORD') ?: 'apppass';
$salt = getenv('SALT') ?: '123456';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    die("Erreur DB : " . $mysqli->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $hash = md5($salt . $password);

        $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hash);

        if ($stmt->execute()) {
            $message = "Utilisateur créé avec succès.";
        } else {
            $message = "Erreur création utilisateur : " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Merci de renseigner un identifiant et un mot de passe.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_hash = md5($salt . $password);

    $result = $mysqli->query("SELECT id, username, password_hash, avatar_path FROM users WHERE username = '" . $username . "' AND password_hash = '" . $password_hash . "'");
    $userRow = $result->fetch_assoc();

    if ($userRow) {
        $_SESSION['user'] = $userRow['username'];
        $_SESSION['avatar'] = $userRow['avatar_path'];
        $message = "Connexion réussie. Bonjour " . htmlspecialchars($userRow['username'], ENT_QUOTES, 'UTF-8');
    } else {
        $message = "Identifiants invalides.";
    }

    $mysqli->close();;
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="corporate">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POC Auth PHP</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-200 p-8">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-4xl font-bold mb-6">POC PHP / MySQL</h1>

    <?php if ($message !== ''): ?>
      <div class="alert alert-info mb-6">
        <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user'])): ?>
      <div class="alert alert-success mb-1">
        <span>Session active : <?= htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="mb-5">Vous pouvez désormais accéder aux <a class="btn btn-primary mt-4" href="resources.php">ressources</a> ou à votre <a class="btn btn-primary mt-4" href="profile.php">profil</a>.</div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-6">
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Créer un utilisateur</h2>
          <form method="post" class="space-y-4">
            <input class="input input-bordered w-full" type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input class="input input-bordered w-full" type="password" name="password" placeholder="Mot de passe" required>
            <button class="btn btn-primary w-full" type="submit" name="register">Créer</button>
          </form>
        </div>
      </div>

      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Connexion</h2>
          <form method="post" class="space-y-4">
            <input class="input input-bordered w-full" type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input class="input input-bordered w-full" type="password" name="password" placeholder="Mot de passe" required>
            <button class="btn btn-secondary w-full" type="submit" name="login">Se connecter</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>