<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 7:45 PM
 */

require_once "functions.php";

$round = $_REQUEST["round"];
$fileName = getAuctionStateFile($round, true);

$auctionStateJson = "";
if($fileName!=null)
{
    $auctionStateJson = file_get_contents($fileName);
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
}
else if($round==1)
{
    $auctionStateJson = getInitialState();
    file_put_contents("auction_states/round1.txt", $auctionStateJson);
}
echo $auctionStateJson;