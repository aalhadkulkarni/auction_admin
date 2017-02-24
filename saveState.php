<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 8:08 PM
 */

require_once "functions.php";

$round = $_REQUEST["round"];
$auctionStateJson = $_REQUEST["state"];

$fileName = getAuctionStateFile($round);
file_put_contents($fileName, $auctionStateJson);

echo "1";