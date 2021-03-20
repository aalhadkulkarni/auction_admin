<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 6/4/17
 * Time: 1:44 AM
 */

ini_set("display_errors", "0");

$loggedInUser = null;

$users = array
(
    "admin" => "admin123",
    "Thane" => "2139",
    "Miraj" => "2859",
    "Karad" => "2105",
    "Kolhapur" => "1401",
    "Pune" => "2101",
    "Viewer" => ""
);

//$users = array
//(
//    "admin" => "admin123",
//    "Thane" => "Thane",
//    "Miraj" => "Miraj",
//    "Karad" => "Karad",
//    "Kolhapur" => "Kolhapur",
//    "Pune" => "Pune",
//    "Viewer" => ""
//);
$userCookie = $_COOKIE["usercookie"];
$logout = $_REQUEST["logout"];
if ($logout == 1)
{
    $_COOKIE["usercookie"] = null;
    $userCookie = null;
    setcookie("usercookie", null, time()-3600);
}
if (array_key_exists($userCookie, $users))
{
    $loggedInUser = $userCookie;
}
else
{
    $userName = $_REQUEST["userName"];
    $password = $_REQUEST["password"];
    if (isset($userName) && isset($password) && $users[$userName] == $password || $userName == "Viewer")
    {
        $loggedInUser = $userName;
        setcookie("usercookie", $userName);
    }
    else
    {
        readfile("html/login.html");
        die();
    }
}