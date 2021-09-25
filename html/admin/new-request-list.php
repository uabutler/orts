<?php
require_once __DIR__ . '/../../php/error/error-handling.php' ;
include_once '../../php/database/requests.php';

if (isset($_GET['semester']))
{
$archive = true;

$semester = Semester::getByCode(strval($_GET['semester']));
if (is_null($semester))
$requests = null;
else
$requests = Request::get(false, null, $semester);
}
else
{
$archive = false;
$requests = Request::get(true);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - <?php echo $archive ? 'Archive' : 'Override Requests'; ?></title>
    <?php require '../../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/request-list.css">
    <!-- Data passed from the server using PHP for use JS -->
    <script>
        const JSON_DATA = '<?php echo json_encode($requests); ?>';
    </script>
    <script src="/js/admin/request-list.js"></script>
</head>

<body>
<?php require_once '../../php/header.php'; ?>
<?php require_once '../../php/navbar.php'; facultyNavbar(!$archive ? "Current Semester" : "Archive"); ?>

<section>
    <h1 id="page-title">Override Requests<?php if ($archive) echo ' - Archive for ' . $semester->getDescription(); ?></h1>

    <div style="float: right; margin-bottom: 12px;">
        <button class="ui positive button">Approve</button>
        <button class="ui right labeled icon button">
            <i class="dropdown icon"></i>
            Actions
        </button>
        <button class="ui button" style="margin-left: 60px;">Filter</button>
        <div class="ui action input">
            <input type="text" placeholder="Search students">
            <button class="ui button">Search</button>
        </div>
    </div>
    <table class="ui celled definition table">
        <thead>
        <tr>
            <th class="collapsing">
                <div class="ui fitted checkbox">
                    <input type="checkbox"> <label></label>
                </div>
            </th>
            <th>Status</th>
            <th>Received</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Class</th>
            <th>CRN</th>
            <th>Faculty</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="collapsing">
                <div class="ui fitted checkbox">
                    <input type="checkbox"> <label></label>
                </div>
            </td>
            <td>
                <i class="material-icons" style="color:orange">warning</i> Received
            </td>
        </tr>
        <tr>
            <td class="collapsing">
                <div class="ui fitted checkbox">
                    <input type="checkbox"> <label></label>
                </div>
            </td>
            <td data-label="Name">Jill</td>
            <td data-label="Age">26</td>
            <td data-label="Job">Engineer</td>
        </tr>
        <tr>
            <td class="collapsing">
                <div class="ui fitted checkbox">
                    <input type="checkbox"> <label></label>
                </div>
            </td>
            <td data-label="Name">Elyse</td>
            <td data-label="Age">24</td>
            <td data-label="Job">Designer</td>
        </tr>
        </tbody>
    </table>
</section>
</body>
</html>
