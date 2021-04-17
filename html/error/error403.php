<?php http_response_code(403); ?>
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
        <h1>ERROR 403</h1>
        <p>
            The system doesn't think you should be allowed to access this.
            If you think this is a mistake, contact the system administrator.
            If this isn't a mistake, field agents will be sent to apprehend you shortly.
        </p>
    </div>
</div>
</body>
</html>
<?php exit; ?>