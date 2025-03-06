//Es necesario hacer el composer y entrar a la cuenta de google y descargar el archivo.json
//Además para testear es necesario dar permiso a las cuentas de Google
<?php
require 'vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->setScopes(Google_Service_Drive::DRIVE);
$client->setRedirectUri('http://localhost:8003/oauth2callback.php');
$client->setAccessType('offline');

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    
    if (!isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    } else {
        
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $_SESSION['access_token'] = $accessToken;
        header('Location: ' . filter_var('http://localhost:8003', FILTER_SANITIZE_URL));
    }
}

$service = new Google_Service_Drive($client);

$folderId = '';  

$query = "'$folderId' in parents and mimeType contains 'image/'";

$files = $service->files->listFiles([
    'q' => $query,
    'fields' => 'nextPageToken, files(id, name, mimeType)'
]);

$images = [];
foreach ($files->getFiles() as $file) {
    if (strpos($file->getMimeType(), 'image/') === 0) {
        $images[] = [
            'id'   => $file->getId(),
            'name' => $file->getName()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Imágenes desde Google Drive</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
        }
        .gallery div {
            margin: 10px;
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center">Galería de Imágenes desde Google Drive</h1>
    <hr>
    <div class="gallery">
        <?php foreach ($images as $image): ?>
            <div>
                <img src="image.php?id=<?= $image['id'] ?>" alt="<?= htmlspecialchars($image['name']) ?>" class="img-fluid">
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
