<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 7:45 PM
 */

ini_set("display_errors", 1);
require_once "functions.php";

$round = safeReturn($_REQUEST, "round");
$fileName = getAuctionStateFile($round, true);

echo $fileName;
$auctionStateJson = "";
if($round==1 || !isStringSet($round))
{
    $auctionStateJson = getInitialState();
    file_put_contents("auction_states/round1.txt", $auctionStateJson);
}
else if($fileName!=null)
{
    $auctionStateJson = file_get_contents($fileName);
}
if(!isStringSet($round))
{
    $round = 1;
}
if(isset($_REQUEST["reset"]))
{
    for($i=$round+1;;$i++)
    {
        $fileName = getAuctionStateFile($i, true);
        if($fileName==null)
        {
            break;
        }
        unlink($fileName);
    }
}
echo $auctionStateJson;