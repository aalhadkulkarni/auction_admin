<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 1:04 AM
 */

include_once "functions.php";

echo file_get_contents("html/home.html");
die();
$admins = array
(
    "aalhadkulkarni" => "aiag#2112A",
    "yogran" => "fantasy@123",
    "guruji" => "sports@123"
);

if(isStringSet(safeReturn($_COOKIE, "auction_admin")))
{
    echo file_get_contents("html/home.html");
}
else if(isStringSet(safeReturn($_REQUEST, "adminId")))
{
    $adminId = safeReturn($_REQUEST, "adminId");
    $password = safeReturn($_REQUEST, "password");
    if(key_exists($adminId, $admins) && $password == safeReturn($admins, $adminId))
    {
        setcookie("auction_admin", $adminId);
        echo file_get_contents("html/home.html");
    }
    else
    {
        echo file_get_contents("html/relogin.html");
    }
}
else
{
    echo file_get_contents("html/login.html");
}