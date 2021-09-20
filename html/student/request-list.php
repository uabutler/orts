<?php
require_once __DIR__ . '/../../php/error/error-handling.php' ;
require_once '../../php/database/requests.php';
require_once '../../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent(null);

$student = Student::get(Auth::getUser());
$requests = Request::get(true, $student);

if (is_null($requests))
    include '../error/error400.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Override Requests</title>
    <?php require '../../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-list.css">
    <script>
        const JSON_DATA = '<?= json_encode($requests) ?>';
    </script>
    <script src="/js/student/request-list.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; studentNavbar("Active Requests"); ?>

<section>
    <h1 id="page-title">Override Requests</h1>
    <!-- Table Created By: Thao Phung -->
    <table id="request-table">
        <colgroup>
            <col style="width:10%;">
            <col style="width:10%;">
            <col style="width:40%;">
            <col style="width:40%;">
        <colgroup>
        <tr class="truman-dark-bg">
            <th>Dept</th>
            <th>Num</th>
            <th>Title</th>
            <th>Status</th>
        </tr>
    </table>
</section>
</body>
</html>
