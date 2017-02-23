<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 1:04 AM
 */

require_once "config.php";

$players = getPlayers();
$leagueTeams = getLeagueTeams();

$auctionState = new AuctionState();

$auctionState->round = 1;
$auctionState->leagueTeams = $leagueTeams;
$auctionState->players = $players;
$auctionState->history = array();
$auctionState->batsmenRemaining = array();
$auctionState->bowlersRemaining = array();
$auctionState->allroundersRemaining = array();
$auctionState->keepersRemaining = array();

foreach ($players as $player)
{
    switch($player->role)
    {
        case "Batsman":
            $auctionState->batsmenRemaining[] = $player;
            break;

        case "Bowler":
            $auctionState->bowlersRemaining[] = $player;
            break;

        case "All Rounder":
            $auctionState->allroundersRemaining[] = $player;
            break;

        case "Keeper":
            $auctionState->keepersRemaining[] = $player;
            break;

        default:
            echo "Error: ";
            var_dump($player);
            echo "<br>";
    }
}

$json = json_encode($auctionState);
echo $json;

function getPlayers()
{
    $input = file_get_contents("players.csv");
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

        $players[] = $player;

        $id++;
    }

    return $players;
}

function getLeagueTeams()
{
    $input = file_get_contents("leagueTeams.csv");
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

        $leagueTeams[] = $leagueTeam;

        $id++;
    }

    return $leagueTeams;
}