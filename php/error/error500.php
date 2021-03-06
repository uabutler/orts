<?php
require_once __DIR__ . '/../logger.php';
http_response_code(500);
$request_id = Logger::getRequestId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Main Page</title>
    <?php require '../../php/common-head.php' ?>
    <style>
        h1 {
            font-size: 100px;
            font-family: nexabold, sans-serif;
        }
    </style>
</head>

<body class="grid-container">
<?php require_once '../../php/header.php'; ?>

<div class="grid-item content">
    <div style="text-align: center;">
        <h1>ERROR 500</h1>
        <p>Request ID: <?= $request_id ?></p>
        <p>That's bad. You might want to let us know this is happening...</p>
    </div>
</div>
</body>
</html>
<?php exit; ?>