<?php
require 'vendor/autoload.php';
session_start();

if (!isset($_GET['id'])) {
    die("No se proporcionó el ID de la imagen.");
}

$imageId = $_GET['id'];

$client = new Google_Client();
$client->setAuthConfig('credentials.json');
$client->setScopes(Google_Service_Drive::DRIVE);
$client->setAccessType('offline');

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    die("No hay token de acceso. Por favor, autentícate.");
}

$service = new Google_Service_Drive($client);

try {
    $fileInfo = $service->files->get($imageId, ["fields" => "mimeType"]);
    $mimeType = $fileInfo->getMimeType();

    $response = $service->files->get($imageId, ["alt" => "media"]);
    $content = $response->getBody()->getContents();

    header("Content-Type: " . $mimeType);
    echo $content;
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error al cargar la imagen: " . $e->getMessage();
}
?>
