<?php

session_start();
if (!isset($_SESSION['user'])) {
    throw new RuntimeException("Utilisateur non connecté ou session invalide.");
}

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'appdb';
$user = getenv('DB_USER') ?: 'appuser';
$pass = getenv('DB_PASSWORD') ?: 'apppass';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    die("Erreur DB : " . htmlspecialchars($mysqli->connect_error, ENT_QUOTES, 'UTF-8'));
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $url === '' || $description === '') {
        $message = "Tous les champs sont obligatoires.";
        $messageType = 'error';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $message = "L'URL fournie n'est pas valide.";
        $messageType = 'error';
    } else {
        $stmt = $mysqli->prepare(
            "INSERT INTO resources (title, url, description) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $title, $url, $description);

        if ($stmt->execute()) {
            $message = "Ressource enregistrée avec succès.";
            $messageType = 'success';
        } else {
            $message = "Erreur lors de l'enregistrement : " . $stmt->error;
            $messageType = 'error';
        }

        $stmt->close();
    }
}

$search = trim($_GET['s'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $result = $mysqli->query(
        "SELECT id, title, url, description, created_at
         FROM resources
         WHERE title LIKE '" . $like . "' OR url LIKE '" . $like . "' OR description LIKE '" . $like . "' ORDER BY created_at DESC"
    );
} else {
    $result = $mysqli->query(
        "SELECT id, title, url, description, created_at
         FROM resources
         ORDER BY created_at DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="corporate">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ressources</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-200">
  <div class="max-w-6xl mx-auto p-6">
    <div class="navbar bg-base-100 rounded-box shadow mb-6">
      <div class="flex-1">
        <a href="index.php" class="btn btn-ghost text-xl">POC PHP / MySQL</a>
      </div>
    <div class="gap-2">
        <div class="flex-none">
            <a href="/" class="btn btn-primary">Accueil</a>
        </div>
        <div class="flex-none">
            <a href="/profile.php" class="btn btn-primary">Profil</a>
        </div>
      </div>
    </div>

    <div class="mb-6">
      <h1 class="text-4xl font-bold mb-2">Ressources web</h1>
      <p class="text-base-content/70">Ajout, recherche et affichage de ressources.</p>
    </div>

    <?php if ($message !== ''): ?>
      <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?> mb-6">
        <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-1">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Ajouter une ressource</h2>

            <form method="post" class="space-y-4">
              <div>
                <label class="label" for="title">
                  <span class="label-text">Titre</span>
                </label>
                <input
                  class="input input-bordered w-full"
                  type="text"
                  id="title"
                  name="title"
                  maxlength="255"
                  required
                >
              </div>

              <div>
                <label class="label" for="url">
                  <span class="label-text">Lien</span>
                </label>
                <input
                  class="input input-bordered w-full"
                  type="url"
                  id="url"
                  name="url"
                  maxlength="2048"
                  placeholder="https://example.com"
                  required
                >
              </div>

              <div>
                <label class="label" for="description">
                  <span class="label-text">Description</span>
                </label>
                <textarea
                  class="textarea textarea-bordered w-full"
                  id="description"
                  name="description"
                  rows="8"
                  required
                ></textarea>
              </div>

              <button class="btn btn-primary w-full" type="submit">
                Enregistrer
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="lg:col-span-2">
        <div class="mb-4 w-full">
          <div class="mb-1">
            <form method="get" class="flex gap-3 flex-col sm:flex-row">
              <input
                class="input input-bordered w-full"
                type="text"
                name="s"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Rechercher dans le titre, le lien ou la description"
              >
              <button class="btn btn-secondary" type="submit">Rechercher</button>
              <a class="btn btn-ghost" href="resources.php">Réinitialiser</a>
            </form>
          </div>
          <p class="text-sm text-base-content/60">Votre recherche : <?php echo $search ?></p>
        </div>

        <div class="space-y-4">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="card bg-base-100 shadow">
                <div class="card-body">
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <h3 class="card-title">
                        <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>
                      </h3>
                      <p class="text-sm text-base-content/60">
                        Ajoutée le <?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?>
                      </p>
                    </div>
                    <div>
                      <a
                        class="btn btn-outline btn-sm"
                        href="<?= htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Ouvrir
                      </a>
                    </div>
                  </div>

                  <p class="break-all text-primary">
                    <?= htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <p>
                    <?= nl2br(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8')) ?>
                  </p>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="alert">
              <span>Aucune ressource trouvée.</span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>