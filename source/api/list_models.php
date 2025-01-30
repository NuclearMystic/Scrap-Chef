<?php
require_once "config.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/models");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . OPENAI_API_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
