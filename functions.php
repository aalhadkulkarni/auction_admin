<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 8:09 PM
 */

require_once "config.php";

function getAuctionStateFile($round, $checkIfExists = false)
{
    $fileName = "auction_states/round" . $round . ".txt";
    if(!$checkIfExists || ($checkIfExists && file_exists($fileName)))
    {
        return $fileName;
    }
    return null;
}

function getInitialState()
{
    $players = getPlayers();
    $leagueTeams = getLeagueTeams();

    $auctionState = new AuctionState();

    $auctionState->round = 1;
    $auctionState->leagueTeams = $leagueTeams;
    $auctionState->players = $players;

    foreach ($players as $player)
    {
        $auctionState->allRemainingPlayers[$player->id] = $player;
        $auctionState->allRemainingPlayersCount++;
    }

    $json = json_encode($auctionState);
    return $json;
}

function getPlayers()
{
    $input = file_get_contents("data/players.txt");
    $lines = explode("\n", $input);

    $players= array();
    $id = 1;
    foreach ($lines as $line)
    {
        $playerData = explode(",", $line);
        $player = new Player();

        $player->id = $id;
        $player->name = $playerData[0];
        $player->basePrice = $playerData[1];
        $player->role = $playerData[2];
        $player->iplTeam = $playerData[3];
        $player->nationality = $playerData[4];
        $player->isStar = ($playerData[5] == "Yes") ? true : false;

        $players[$id] = $player;

        $id++;
    }

    return $players;
}

function getLeagueTeams()
{
    $input = file_get_contents("data/leagueTeams.txt");
    $lines = explode("\n", $input);

    $leagueTeams= array();
    $id = 1;
    foreach ($lines as $line)
    {
        $leagueTeamData = explode(",", $line);
        $leagueTeam = new LeagueTeam();

        $leagueTeam->id = $id;
        $leagueTeam->name = $leagueTeamData[0];
        $leagueTeam->budgetLeft = $leagueTeamData[1];
        $leagueTeam->actions = array();

        $leagueTeams[$id] = $leagueTeam;

        $id++;
    }

    return $leagueTeams;
}

function safeReturn($array, $index, $default=null)
{
    return isset($array[$index]) ? $array[$index] : $default;
}

function isStringSet($string)
{
    return (!is_null($string) && $string !== '');
}

function getTotalRounds()
{
    $round = file_get_contents("auction_states/totalRounds.txt");
    return isStringSet($round) ? $round : 0;
}

function setTotalRounds($round)
{
    file_put_contents("auction_states/totalRounds.txt", $round);
}

function resetAuctionToRound($round)
{
    $totalRounds = getTotalRounds();
    for($i=$round+1;$i<=$totalRounds;$i++)
    {
        $fileName = getAuctionStateFile($i, true);
        if(isStringSet($fileName))
        {
            unlink($fileName);
        }
    }
    setTotalRounds($round);
}