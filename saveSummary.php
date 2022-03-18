<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 23/3/18
 * Time: 1:09 AM
 */

require_once "functions.php";

$round = $_REQUEST["round"];
$summary = $_REQUEST["summary"];

$result = file_put_contents("bidding/summary" . $round . ".txt", $summary);

if ($result === false)
{
    echo "0";
}
else
{
    for($i=1; $i<=6; $i++)
    {
        unlink("bidding/" . $i . $round . ".txt");
    }
    echo "1";
}
