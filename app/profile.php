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
    die("Erreur DB : " . $mysqli->connect_error);
}

$message = '';
$messageType = 'info';

function setMessage(string $text, string $type = 'info'): void
{
    global $message, $messageType;
    $message = $text;
    $messageType = $type;
}

function uploadAvatar(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Erreur pendant l'envoi du fichier.");
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException("Fichier uploadé invalide.");
    }

    $maxSize = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        throw new RuntimeException("Le fichier est trop volumineux.");
    }

    $avatarDir = __DIR__ . '/avatar';
    if (!is_dir($avatarDir) && !mkdir($avatarDir, 0755, true)) {
        throw new RuntimeException("Impossible de créer le dossier avatar.");
    }

    $filenameParts = pathinfo($file['name']);
    $extension = strtolower($filenameParts['extension'] ?? '');

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $avatarDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException("Impossible d'enregistrer l'image.");
    }

    return 'avatar/' . $filename;
}

if(isset($_FILES['avatar'])) {
    $avatarPath = uploadAvatar($_FILES['avatar'] ?? []);
    $stmt = $mysqli->prepare(
        "UPDATE users SET avatar_path = ? WHERE username = ?"
    );
    $stmt->bind_param("ss", $avatarPath, $_SESSION['user']);
    $stmt->execute();
    $_SESSION['avatar'] = $avatarPath;
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
            <a href="/resources.php" class="btn btn-primary">Ressources</a>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Upload un avatar</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="label" for="avatar">
                <span class="label-text">Avatar</span>
                </label>
                <input
                class="file-input file-input-bordered"
                type="file"
                id="avatar"
                name="avatar"
                >
                <label class="label">
                </label>
            </div>
            <button class="btn btn-primary" type="submit" name="register">
                Upload
            </button>
            </form>

            <?php if (!empty($_SESSION['avatar'])): ?>
              <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2">Avatar actuel</h3>
                <div class="avatar">
                  <div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden">
                    <img
                      src="<?= htmlspecialchars($_SESSION['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="Avatar"
                    >
                  </div>
                </div>
              </div>
            <?php endif; ?>

        </div>
    </div>
  </div>
</body>
</html>