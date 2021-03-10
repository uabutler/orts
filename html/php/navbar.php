<?php

/**
 * @param array $left_map A map from the name displayed to the URL on the left side of the nav bar
 * @param array $right_map A similar map for the right side of the nav bar
 * @param string $active The name of the active option
 */
function genNavbar(array $left_map, array $right_map, string $active)
{

    echo '<div class="grid-item navbar center">';
    echo '<ul id="nav-list" class="truman-dark-bg">';

    foreach ($left_map as $name => $url)
    {
        echo "<li class='nav-item'><a";
        echo $active == $name ? " class='active' " : " ";
        echo "href='$url'>$name</a></li>";
    }

    foreach (array_reverse($right_map) as $name => $url)
    {
        echo "<li class='nav-item' style='float:right;'><a";
        echo $active == $name ? " class='active' " : " ";
        echo "href='$url'>$name</a></li>";
    }

    echo '</ul>';
    echo '</div>';
}

function studentNavbar($active)
{
    genNavbar
    (
        array
        (
            "New Request" => "/student/new-request.php",
            "Active Requests" => "/student/request-list.php"
        ),
        array
        (
            "Profile" => "/student/profile.php",
            "Log Out" => "/logout.php",
        ),
        $active
    );
}

function facultyNavbar($active)
{
    genNavbar
    (
        array
        (
            "Current Semester" => "/admin/request-list.php",
            "Archive" => "/admin/archive.php",
            "Administration" => "/admin/administration.php",
        ),
        array
        (
            "Profile" => "/admin/profile.php",
            "Log Out" => "/logout.php",
        ),
        $active
    );
}

function homeNavbar()
{
    genNavbar
    (
        [],
        array("Log In" => "/login.php"),
        ""
    );
}
