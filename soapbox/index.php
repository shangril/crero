<?php

$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

$microtime = microtime(true);
$randomString = bin2hex(random_bytes(4)); 
$filename = $microtime . '-' . $randomString . '.dat';

file_put_contents($filename, $clientIp);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>You should not see that</title>
</head>
<body>
    <h1>Restricted access</h1>
    <p>This document should not be seen my anyone. If you are the owner of this site, check your .dat collection in this directory of your web server immediately and remove any of them containing your own IP. Otherwise you will not be able to access your own website. </p>
</body>
</html>
