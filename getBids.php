<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 22/3/18
 * Time: 2:56 AM
 */

require_once "functions.php";

header("Content-Type: application/json", true);

function getUserBids($round)
{
    $bids = array();
    for($i=1; $i<=5; $i++)
    {
        $bid = file_get_contents("bidding/" . $i . $round . ".txt");
        $bids[$i] = $bid;
    }
    return json_encode($bids);
}

function getRoundSummary($round)
{
    $summary = file_get_contents("bidding/summary" . $round . ".txt");
    return $summary;
}

$round = $_POST["round"];
if ($loggedInUser == "admin")
{
    echo getUserBids($round);
}
else
{
    echo getRoundSummary($round);
}