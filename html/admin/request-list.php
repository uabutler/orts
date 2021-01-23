<?php
include_once '../php/database/requests.php';

if (isset($_GET['semester']))
{
    $archive = true;

    $semester = Semester::getByCode(strval($_GET['semester']));
    if (is_null($semester))
        $requests = null;
    else
        $requests = Request::getInactive($semester);
}
else
{
    $archive = false;
    $requests = Request::listActive();
}

if (is_null($requests))
{
    http_response_code(400);
    header("Location: error400.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ORTS - <?php echo $archive ? 'Archive' : 'Override Requests'; ?></title>
    <?php require '../php/common-head.php'; ?>
    <link rel="stylesheet" href="/css/admin/request-list.css">
    <!-- Data passed from the server using PHP for use JS -->
    <script>
        const JSON_DATA = '<?php echo json_encode($requests); ?>';
    </script>
    <script src="/js/admin/request-list.js"></script>
</head>

<body class="grid-container">
<?php require_once '../php/header.php'; ?>
<?php require_once '../php/navbar.php'; facultyNavbar(!$archive ? "Current Semester" : "Archive"); ?>

<div class="grid-item content">
    <h2>Override Requests<?php if ($archive) echo ' - Archive for ' . $semester->getDescription(); ?></h2>
    <!-- Table based on one created by Thao Phung. This html just creates the top. The body is done in JS -->
    <table id="request-table">
        <colgroup>
            <col style="width:5%;">
            <col style="width:33%;">
            <col style="width:19%;">
            <col style="width:9%;">
            <col style="width:9%;">
            <col style="width:7%;">
            <col style="width:5%;">
            <col style="width:4%;">
            <col style="width:4%;">
            <col style="width:5%;">
        <colgroup>
            <tr>
                <td class="border-none">
                    <div class="dropdown">
                        <button class="dropbtn" style="padding-right:0px;padding-left: 0px;">Bulk</button>
                        <div class="dropdown-content">
                            <a href="#">Approve</a>
                            <a href="#">Deny</a>
                            <a href="#">Provisionally Approve</a>
                            <a href="#">Marked As Entered To Banner</a>
                            <a href="#">Archive</a>
                        </div>
                    </div>
                </td>
                <td class="border-none">
                    <div class="dropdown">
                        <button class="dropbtn">Filter</button>
                        <div class="dropdown-content">
                            <p style="margin-left:10px;">Status:</p>
                            <input type="radio" id="all_stat" name="status" value="all_stat" class="default"
                                   style="margin:10px;">
                            <label for="all_stat">All</label><br>
                            <input type="radio" id="received" name="status" value="Received" style="margin:10px;">
                            <label for="received"><i class="material-icons" style="color:orange">warning</i>
                                Received</label><br>
                            <input type="radio" id="approved" name="status" value="Approved" style="margin:10px;">
                            <label for="approved"><i class="material-icons" style="color:green">done</i>
                                Approved</label><br>
                            <input type="radio" id="papproved" name="status" value="Provisionally Approved"
                                   style="margin:10px;">
                            <label for="papproved"><i class="material-icons" style="color:yellowgreen">done</i>
                                Provisionally Approved</label><br>
                            <input type="radio" id="denied" name="status" value="Denied" style="margin:10px;">
                            <label for="denied"><i class="material-icons" style="color:red">cancel</i>
                                Denied</label><br>
                            <input type="radio" id="faculty" name="status" value="Needs Faculty" style="margin:10px;">
                            <label for="faculty"><i class="material-icons" style="color:orange">warning</i> Needs
                                Faculty</label><br>
                            <p style="margin-left:10px;">In Banner:</p>
                            <input type="radio" id="both" name="banner" value="both" style="margin:10px;"
                                   class="default">
                            <label for="both">Both</label><br>
                            <input type="radio" id="inbanner" name="banner" value="inbanner" style="margin:10px;">
                            <label for="inbanner"><i class="material-icons" style="color:green">done</i> Not In
                                Banner</label><br>
                            <input type="radio" id="notinbanner" name="banner" value="notinbanner" style="margin:10px;">
                            <label for="notinbanner"><i class="material-icons" style="color:green">done_all</i> In
                                Banner</label><br>
                        </div>
                    </div>
                </td>
                <td class="border-none">
                    <div class="dropdown">
                        <button class="dropbtn">Sort</button>
                        <div class="dropdown-content">
                            <input type="radio" id="datedescending" name="datesort" value="datedescending"
                                   style="margin:10px;" class="default">
                            <label for="datedescending">Descending</label><br>
                            <input type="radio" id="dateacending" name="datesort" value="dateascending"
                                   style="margin:10px;">
                            <label for="dateacending">Ascending</label><br>
                        </div>
                    </div>
                </td>
                <td class="border-none">
                    <input type="text" id="last" class="big-search search-form" placeholder="Last">
                </td>
                <td class="border-none">
                    <input type="text" id="first" class="big-search search-form" placeholder="First">
                </td>
                <td class="border-none">
                    <input type="text" id="bannerid" class="med-search numeric" placeholder="Banner ID">
                </td>
                <td class="border-none">
                    <div class="dropdown">
                        <button class="dropbtn" style="padding-right:0px;padding-left: 0px;">Filter</button>
                        <div class="dropdown-content">
                            <input type="radio" id="all_dept" name="dept" value="all_dept" style="margin:10px;"
                                   class="default">
                            <label for="all_dept">All</label><br>
                            <input type="radio" id="cs" name="dept" value="CS" style="margin:10px;">
                            <label for="cs">CS</label><br>
                            <input type="radio" id="math" name="dept" value="MATH" style="margin:10px;">
                            <label for="math">MATH</label><br>
                            <input type="radio" id="stat" name="dept" value="STAT" style="margin:10px;">
                            <label for="stat">STAT</label><br>
                            <input type="radio" id="tru" name="dept" value="TRU" style="margin:10px;">
                            <label for="tru">TRU</label><br>
                            <input type="radio" id="jins" name="dept" value="JINS" style="margin:10px;">
                            <label for="jins">JINS</label><br>
                        </div>
                    </div>
                    </a>
                </td>
                <td class="border-none">
                    <input type="text" id="course_num" class="tiny-search numeric" placeholder="#">
                </td>
                <td class="border-none">
                    <input type="text" id="crn" class="tiny-search numeric" placeholder="CRN">
                </td>
                <td class="border-none">
                    <input type="text" id="semester" class="small-search numeric" placeholder="Semester">
                </td>
            </tr>
            <tr class="truman-dark-bg">
                <th>Select</th>
                <th>Status</th>
                <th>Recieved</th>
                <th>Last</th>
                <th>First</th>
                <th>Banner ID</th>
                <th>Dept</th>
                <th>Num</th>
                <th>CRN</th>
                <th>Semester</th>
            </tr>
    </table>
</div>
</body>
</html>
