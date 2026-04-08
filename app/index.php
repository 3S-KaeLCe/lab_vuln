<?php
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'appdb';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASSWORD') ?: 'apppass';

$dbStatus = "Not tested";
$dbMessage = "";

try {
    $mysqli = @new mysqli($host, $user, $pass, $dbname);
    if ($mysqli->connect_errno) {
        $dbStatus = "KO";
        $dbMessage = "Connexion impossible : " . $mysqli->connect_error;
    } else {
        $dbStatus = "OK";
        $result = $mysqli->query("SELECT COUNT(*) AS total FROM messages");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        $dbMessage = "Connexion réussie. " . (int)$row['total'] . " message(s) présent(s) dans la base.";
        $mysqli->close();
    }
} catch (Throwable $e) {
    $dbStatus = "KO";
    $dbMessage = "Erreur PHP/MySQL : " . $e->getMessage();
}

$lang = "fr";
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    setcookie('lang', $lang, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
} elseif (isset($_COOKIE['lang'])) {
  $lang = $_COOKIE['lang'];
}

include __DIR__ . '/lang/' .$lang;

?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" data-theme="corporate">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POC PHP + MySQL</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-200">
  <div class="hero min-h-screen">
    <div class="hero-content text-center">
      <div class="max-w-2xl">
        <div class="flex justify-end mb-4 gap-2">
          <a href="?lang=fr" class="btn btn-sm <?php echo $lang === 'fr' ? 'btn-primary' : 'btn-outline'; ?>">FR</a>
          <a href="?lang=en" class="btn btn-sm <?php echo $lang === 'en' ? 'btn-primary' : 'btn-outline'; ?>">EN</a>
        </div>

        <div class="badge badge-primary badge-lg mb-4">POC Dockerisé</div>
        <h1 class="text-5xl font-bold"><?php echo $title; ?></h1>
        <p class="py-6 text-lg">
          Deux conteneurs : base de données et application PHP. Les vulnérabilités peuvent toucher tous les aspects (machine hôte, application web, système de fichier, base de données).
        </p>

        <div class="grid gap-4 md:grid-cols-2">
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">Application</h2>
              <p>PHP 8.2 + Apache dans un conteneur dédié.</p>
              <div class="card-actions justify-end">
                <span class="badge badge-success">Ready</span>
              </div>
            </div>
          </div>

          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">Base de données</h2>
              <p>MySQL 8 avec un script d'initialisation.</p>
              <div class="card-actions justify-end">
                <span class="badge <?php echo $dbStatus === 'OK' ? 'badge-success' : 'badge-error'; ?>">
                  DB <?php echo htmlspecialchars($dbStatus, ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="alert mt-6 <?php echo $dbStatus === 'OK' ? 'alert-success' : 'alert-error'; ?>">
          <span><?php echo htmlspecialchars($dbMessage, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="mockup-code mt-8 text-left">
          <pre><code>URL application : http://localhost:8080</code></pre>
          <pre><code>MySQL exposé : localhost:3307</code></pre>
          <pre><code>Utilisateur SQL : appuser / apppass</code></pre>
          <pre><code>Root SQL : root / rootpass</code></pre>
        </div>

        <div>
          <a href="./user.php" class="btn btn-primary mt-4">S'enregistrer ou se connecter</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
