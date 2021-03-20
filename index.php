<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 4/3/17
 * Time: 7:28 PM
 */

echo "Please use live link - check on whatsapp";
die();

require_once "functions.php";

if ($loggedInUser == "admin")
{
    include_once("html/admin.html.php");
}
else if ($loggedInUser == "Viewer")
{
    include_once "html/viewer.html.php";
}
else
{
    include_once "html/bidding.html.php";
}