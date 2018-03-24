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
    "Thane" => "Thane123",
    "Miraj" => "Miraj123",
    "Karad" => "Karad123",
    "Kolhapur" => "Kolhapur123",
    "Pune" => "Pune123",
    "Viewer" => ""
);
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