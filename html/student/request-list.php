<?php
require_once '../php/database/requests.php';
require_once '../php/auth.php';

Auth::createClient();
Auth::forceAuthenticationStudent();
$student_email = Auth::getUser();
$student = Student::get($student_email);
$requests = Request::get($student);

if (is_null($requests))
{
    http_response_code(400);
    header("Location: /error400.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - Override Requests</title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/student/request-list.css">
    <script>
        const JSON_DATA = '<?php echo json_encode($requests); ?>';
    </script>
    <script src="/js/student/request-list.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; studentNavbar("Active Requests"); ?>

<div class="grid-item content">
    <h2>Override Requests</h2>
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
</div>
</body>
</html>
