<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 23/3/18
 * Time: 1:15 AM
 */

require_once "functions.php";

$round = $_REQUEST["round"];
$bid = $_REQUEST["bid"];
$teamId = $_REQUEST["teamId"];

$i = file_put_contents("bidding/" . $teamId . $round . ".txt", $bid);
if ($i === false)
{
    echo "0";
}
else
{
    echo "1";
}