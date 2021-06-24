<?php

/**
 * @param array $left_map A map from the name displayed to the URL on the left side of the nav bar
 * @param array $right_map A similar map for the right side of the nav bar
 * @param string $active The name of the active option
 */
function genNavbar(array $left_map, array $right_map, string $active)
{
    echo '<nav>';
    echo '<ul>';

    foreach ($left_map as $name => $url)
    {
        echo "<li><a";
        echo $active == $name ? " id='active' " : " ";
        echo "href='$url'>$name</a></li>";
    }

    foreach (array_reverse($right_map) as $name => $url)
    {
        echo "<li class='right'><a";
        echo $active == $name ? " id='active' " : " ";
        echo "href='$url'>$name</a></li>";
    }

    echo '</ul>';
    echo '<div class="clearfix"></div>';
    echo '</nav>';
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
