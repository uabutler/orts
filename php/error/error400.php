<?php http_response_code(400); ?>
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
        <h1>ERROR 400</h1>
        <p>Bad request, he's dead, Jim</p>
    </div>
</div>
</body>
</html>
<?php exit; ?>