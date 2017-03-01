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

$roles = ["Batsman", "Bowler", "All Rounder", "Keeper"];
$nationalites = ["Indian", "Overseas"];
$isStarStatuses = [true, false];

$totalCount = 0;
$roleCount = array("Batsman"=>0, "Bowler"=>0, "All Rounder"=>0, "Keeper"=>0);
$nationalityCount = array("Indian"=>0, "Overseas"=>0);
$starStatusCount = array(true=>0, false=>0);

foreach ($isStarStatuses as $isStarStatus)
{
    foreach ($roles as $role)
    {
        foreach ($nationalites as $nationality)
        {
            $playerNames = array();
            $count = 0;
            foreach ($players as $player)
            {
                if($player->isStar == $isStarStatus
                    && $player->role == $role
                    && $player->nationality == $nationality)
                {
                    $playerNames[] = $player->name;
                    $count++;

                    $starStatusCount[$isStarStatus]++;
                    $roleCount[$role]++;
                    $nationalityCount[$nationality]++;
                    $totalCount++;
                }
            }
            echo "<b>";
            echo ($isStarStatus) ? "Star " : "Ordinary ";
            echo $role . " ";
            echo $nationality . " (" . $count . ")<br>";
            echo "</b>";
            foreach ($playerNames as $playerName)
            {
                echo $playerName . "<br>";
            }
        }
    }
}

echo "<b>";
foreach ($starStatusCount as $starStatus=>$count)
{
    echo ($starStatus) ? "Star players: " : "Ordinary players: ";
    echo $count . "<br>";
}

foreach ($roleCount as $role=>$count)
{
    switch ($role)
    {
        case "Batsman":
            echo "Batsmen: ";
            break;

        default:
            echo $role . "s: ";
    }
    echo $count . "<br>";
}

foreach ($nationalityCount as $nationality=>$count)
{
    echo $nationality . ": ";
    echo $count . "<br>";
}

echo "Total players: " . $totalCount;

echo "</b>";

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

        $players[] = $player;

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

        $leagueTeams[] = $leagueTeam;

        $id++;
    }

    return $leagueTeams;
}