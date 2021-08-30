<?php
require '../php/database/requests.php';
Request::get(false, "a", null, null, Faculty::getById(2, "0"));
