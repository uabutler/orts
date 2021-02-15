<?php
include_once '../php/database/requests.php';

if (isset($_GET['id']))
    $request = Request::getById(intval($_GET['id']));
else
    $request = null;

if (is_null($request))
    include '../error/error400.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Request Details</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-details.css">
    <script>
        REQUEST_ID = <?= $_GET['id'] ?>;
        REQUEST_STATUS = "<?= $request->getStatus() ?>";
    </script>
    <script src="/js/student/request-details.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Active Requests"); ?>

<div class="grid-item content content-grid-container">
    <h2 class="truman-dark-bg">Override Status</h2>
    <table>
        <tr>
            <th>Status:</th>
            <td id="status_info"><?= $request->getStatusHtml() ?></td>
        </tr>
        <tr>
            <th>Date Modified:</th>
            <td><?= $request->getLastModified() ?></td>
        </tr>
        <tr>
            <th>Date Received:</th>
            <td>1970-01-01T00:00:00</td>
        </tr>
        <tr>
            <th style="padding-right:1em">Designated Faculty:</th>
            <td><?= $request->getFaculty()->getLastName() ?>, <?= $request->getFaculty()->getFirstName() ?></td>
        </tr>
    </table>
    <h2 class="truman-dark-bg">Course Information</h2>
</div>
</body>
</html>
