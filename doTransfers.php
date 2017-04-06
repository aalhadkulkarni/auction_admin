<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 6/4/17
 * Time: 3:28 AM
 */

require_once "functions.php";

$tournament = "ipl2017";

$matchNo = getMatchNo($tournament);
$nextMatchNo = $matchNo+1;

$nextMatchFile = TOURNAMENTS_HOME_DIR . "/" . $tournament . "/tournament_states/match" . $nextMatchNo . ".txt";

$tournamentState = safeReturn($_REQUEST, "state");

file_put_contents($nextMatchFile, $tournamentState);

echo "1";