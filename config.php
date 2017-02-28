<?php
/**
 * Created by PhpStorm.
 * User: aalhadk
 * Date: 24/2/17
 * Time: 12:14 AM
 */

class AuctionState
{
    public $round; //Integer: Current round number

    public $history; //Object: History of which player was sold to which team at what price from round 1 till now. Null if current round is round 1.

    /*public $batsmenRemaining; //Array of batsmanIds
    public $bowlersRemaining; //Array of bowlerIds
    public $keepersRemaining; //Array of keeperIds
    public $allroundersRemaining; //Array of allrounderIds*/

    public $allRemainingPlayers;

    public $players; //Array of Player objects

    public $leagueTeams; //Array of LeagueTeam objects

    public $categories;

    public $unsoldPlayersRemaining;
    public $unsoldPlayers;
    public $soldPlayers;

    public function __construct()
    {
        $this->unsoldPlayersRemaining = array();
        $this->unsoldPlayers = array();
        $this->soldPlayers = array();
        $this->categories = array
        (
            new Category("Keeper", "Indian", true),
            new Category("Keeper", "Overseas", true),
            new Category("All Rounder", "Indian", true),
            new Category("All Rounder", "Overseas", true),
            new Category("Batsman", "Indian", true),
            new Category("Batsman", "Overseas", true),
            new Category("Bowler", "Indian", true),
            new Category("Bowler", "Overseas", true),

            new Category("All Rounder", "Indian", false),
            new Category("All Rounder", "Overseas", false),
            new Category("Batsman", "Indian", false),
            new Category("Batsman", "Overseas", false),
            new Category("Bowler", "Indian", false),
            new Category("Bowler", "Overseas", false),
            new Category("Keeper", "Indian", false),
            new Category("Keeper", "Overseas", false),
        );
    }
}

class Player
{
    public $id;
    public $name;
    public $role;
    public $basePrice;
    public $iplTeam;
    public $nationality;
    public $isStar;
}

class LeagueTeam
{
    public $id;
    public $name;
    public $budgetLeft;
    public $actions; //Array of Action objects
}

class Action
{
    public $leagueTeamId;
    public $playerId;
    public $bid;
}

class Category
{
    public $role;
    public $nationality;
    public $isStar;

    public function __construct($role, $nationality, $isStar)
    {
        $this->role = $role;
        $this->nationality = $nationality;
        $this->isStar = $isStar;
    }
}

/*{
	"round": <round>,

	"history":
	[
		{
			"league_team": <team_id>,
			"player": <player_id>,
			"bid": <bid>
		},...
	]

	"batsmen_remaining": [id,id,id,...],
	"bowlers_remaining": [id,id,id,...],
	"keepers_remaining": [id,id,id,...],
	"allrounders_remaining": [id,id,id,...],

	"players":
	[
		{
			"id": <id>,
			"name": <name>,
			"role": <role>,
			"base_price": <base_price>,
			"ipl_team": <ipl_team_id>,
			"league_team": <league_team_id>
		},...
	],

	"ipl_teams":
	[
		{
			"id": <id>,
			"name": <name>,
		},...
	],

	"league_teams":
	[
		{
			"id": <id>,
			"name": <name>
			"budget_left": <budget_left>,
			"actions":
			[
				{
					"player_id": <player_id>,
					"bid": <bid>
				}
			]
		}
	]
}*/