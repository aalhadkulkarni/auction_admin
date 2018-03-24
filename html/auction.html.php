<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auction</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <style>
        .table-responsive {
            max-height: 300px;
        }
    </style>

    <script>
        var userName = "";
        var currentTeamId = "";
        <?php echoVariables(); ?>
    </script>

    <script>
        var submitted = false;
        var stopBids = false;
        var currentPlayer = null;
        var summary = {
            playerId: null,
            status: "not started",
            bid: null,
            teams: [],
            outTeams: {}
        };
        var currentBids = {};
        var auctionState =
        {
            round: 1,
            history: [],

            players: [],

            allRemainingPlayers: [],

            unsoldPlayersRemaining: [],
            unsoldPlayers: [],

            leagueTeams: [],

            categories: [],

            soldPlayers: [],

            soldPlayersCount: 0,
            unsoldPlayersCount: 0,
            remainingUnsoldPlayersCount: 0,
            allRemainingPlayersCount: 0
        };

        function updateAuctionState() {
            resetAllData();
            setRound();
            setHistory();
            setAllRemainingPlayers();
            setLeagueTeams();
            setUnsoldPlayersRemaining();
            setUnsoldPlayers();
            setCurrentCategory();
            setSummary();
            setStatistics();
        }

        function setStatistics() {
            $("#soldPlayersCount").text(auctionState.soldPlayersCount);
            $("#unsoldPlayersCount").text(auctionState.unsoldPlayersCount + auctionState.remainingUnsoldPlayersCount);
            $("#remainingPlayersCount").text(auctionState.allRemainingPlayersCount);
        }

        function resetAllData() {
            currentPlayer = null;
            $("#currentPlayerText").html("No player selected");
            $("#bidText").val("");
        }

        function setRound() {
            $("#roundDiv").text(auctionState.round);
        }

        function setHistory() {
            $("#historyTable tbody").empty();
            var history = auctionState.history;
            for (var i = history.length - 1; i >= 0; i--) {
                var action = history[i];

                var round = i + 1;
                var player = auctionState.players[action.playerId];
                var leagueTeam = auctionState.leagueTeams[action.leagueTeamId];
                var bid = action.bid;

                var roundCol = $("<td></td>");
                roundCol.html(round);

                var playerCol = $("<td></td>");
                playerCol.append(player.name);

                var leagueTeamCol = $("<td></td>");
                (leagueTeam != null && leagueTeam.name) ? leagueTeamCol.html(leagueTeam.name) : leagueTeamCol.html("Unsold");

                var bidCol = $("<td></td>");
                (leagueTeam != null && leagueTeam.name) ? bidCol.html(bid + "L") : bidCol.html("-");

                var actionRow = $("<tr></tr>");
                actionRow.append(roundCol);
                actionRow.append(playerCol);
                actionRow.append(leagueTeamCol);
                actionRow.append(bidCol);

                $("#historyTable tbody").append(actionRow);
            }
        }

        function setAllRemainingPlayers() {
            $("#allRemainingPlayersTable tbody").empty();
            for (var i in auctionState.allRemainingPlayers) {
                var player = auctionState.allRemainingPlayers[i];

                var nameCol = $("<td></td>");
                nameCol.html(player.name);

                var priceCol = $("<td></td>");
                priceCol.html(player.basePrice + "L");

                var playerRow = $("<tr></tr>");
                playerRow.append(nameCol);
                playerRow.append(priceCol);

                $("#allRemainingPlayersTable tbody").append(playerRow);
            }
        }

        function setLeagueTeams() {
            $("#leagueTeamsTable tbody").empty();
            $("#leagueTeamsSelect").html("");
            for (var i in auctionState.leagueTeams) {
                var leagueTeam = auctionState.leagueTeams[i];

                var nameCol = $("<td></td>");
                nameCol.html(leagueTeam.name);

                var budgetCol = $("<td></td>");
                budgetCol.html(leagueTeam.budgetLeft + "L");

                var playersCol = $("<td></td>");
                playersCol.html("<a href='#' onclick='viewLeagueTeamPlayers(" + leagueTeam.id + "); return false;'>See players</a>");

                var teamRow = $("<tr></tr>");
                teamRow.append(nameCol);
                teamRow.append(budgetCol);
                teamRow.append(playersCol);

                $("#leagueTeamsTable tbody").append(teamRow);

                var option = "<option value = '" + leagueTeam.id + "'>" + leagueTeam.name + "</option>";
                $("#leagueTeamsSelect").append(option);
            }
        }

        function viewLeagueTeamPlayers(leagueTeamId) {
            $("#leagueTeamPlayersTable tbody").empty();
            var leagueTeam = auctionState.leagueTeams[leagueTeamId];
            $("#leagueTeamPlayersModal").modal();
            $("#leagueTeamPlayersHeader").html(leagueTeam.name);
            var actions = leagueTeam.actions;
            for (var i in actions) {
                var action = actions[i];
                if (!action) continue;
                var player = auctionState.players[action.playerId];
                var bid = action.bid;

                var playerCol = $("<td></td>");
                playerCol.html(player.name);

                var bidCol = $("<td></td>");
                bidCol.html(bid + "L");

                var removeCol = $("<td></td>");
                removeCol.html("");

                var playerRow = $("<tr></tr>");
                playerRow.append(playerCol);
                playerRow.append(bidCol);
                playerRow.append(removeCol);

                $("#leagueTeamPlayersTable tbody").append(playerRow);
            }
        }

        function setUnsoldPlayersRemaining() {
            $("#unsoldRemainingPlayersTable tbody").empty();
            for (var i in auctionState.unsoldPlayersRemaining) {
                var player = auctionState.unsoldPlayersRemaining[i];

                if (!player) continue;

                var nameCol = $("<td></td>");
                nameCol.html(player.name);

                var priceCol = $("<td></td>");
                priceCol.html(player.basePrice + "L");

                var playerRow = $("<tr></tr>");
                playerRow.append(nameCol);
                playerRow.append(priceCol);

                $("#unsoldRemainingPlayersTable tbody").append(playerRow);
            }
        }

        function setUnsoldPlayers() {
            $("#unsoldPlayersTable tbody").empty();
            for (var i in auctionState.unsoldPlayers) {
                var player = auctionState.unsoldPlayers[i];

                if (!player) continue;
                var nameCol = $("<td></td>");
                nameCol.html(player.name);

                var priceCol = $("<td></td>");
                priceCol.html(player.basePrice + "L");

                var playerRow = $("<tr></tr>");
                playerRow.append(nameCol);
                playerRow.append(priceCol);

                $("#unsoldPlayersTable tbody").append(playerRow);
            }
        }

        function setCurrentCategory() {
        }

        function setCurrentPlayer(playerId) {
            currentPlayer = auctionState.players[playerId];
            $(".playerRow").removeClass("info");
            $("#player" + currentPlayer.id).addClass("info");
        }

        function listenToBids() {
            $.ajax
            ({
                type: "POST",
                url: "getBids.php",
                data: {
                    teamId: currentTeamId,
                    round: auctionState.round,
                },
                success: function (biddingSummary) {
                    if (stopBids) {
                        return;
                    }
                    if (isSummaryUpdated(biddingSummary)) {
                        summary = biddingSummary;
                        setBiddingSummary(biddingSummary);
                    } else {
                        setTimeout(listenToBids, 5000);
                    }
                },
                error: function () {
                    if (!stopBids) {
                        setTimeout(listenToBids, 2000);
                    }
                }
            });
        }

        function isSummaryUpdated(biddingSummary) {
            var changed = false;
            if (biddingSummary.playerId != summary.playerId) {
                changed = true;
            }
            if (biddingSummary.status != summary.status) {
                changed = true;
            }
            if (biddingSummary.bid != summary.bid) {
                changed = true;
            }
            if (biddingSummary.teams.length != summary.teams.length) {
                changed = true;
            }
            for (var i in biddingSummary.teams) {
                if (biddingSummary.teams[i] != summary.teams[i]) {
                    changed = true;
                }
            }
            for (var i in biddingSummary.outTeams) {
                if (biddingSummary.outTeams[i] != summary.outTeams[i]) {
                    changed = true;
                }
            }
            for (var i in summary.outTeams) {
                if (biddingSummary.outTeams[i] != summary.outTeams[i]) {
                    changed = true;
                }
            }
            console.log(changed);
            return changed;
        }

        function setBiddingSummary() {
            submitted = false;
            var status = summary.status;
            console.log(summary);
            setCurrentPlayer(summary.playerId);
            var biddingSummary = "Next player is " + currentPlayer.name + " (" + currentPlayer.team + " - " + currentPlayer.slab + " " + currentPlayer.role + ") with base price " + currentPlayer.basePrice + ".";
            if (status == "Bids") {
                alert("Bidding has started for " + currentPlayer.name + ". Please submit your initial bid");
                biddingSummary += "<br>Please submit your initial bid.";
                setTimeout(listenToBids, 5000);
            } else if (status == "Raise") {
                biddingSummary += "<br>Current highest bid: " + summary.bid;
                biddingSummary += "<br>Current leaders:";
                for (var i in summary.teams) {
                    biddingSummary += (" " + auctionState.leagueTeams[summary.teams[i]].name);
                }
                biddingSummary += "<br>Teams out of the bidding:";
                for (var i in summary.outTeams) {
                    biddingSummary += (" " + auctionState.leagueTeams[i].name);
                }
                var out = 0;
                for (var i in summary.outTeams) {
                    out++;
                }
                if (out == 3) {
                    alert("Last two teams remain. Please check the bids and start bidding on whatsapp");
                    var inTeams = [];
                    for (var i in auctionState.leagueTeams) {
                        if (!summary.outTeams[i]) {
                            inTeams.push(i);
                        }
                    }
                    biddingSummary += "<br>" + auctionState.leagueTeams[inTeams[0]].name + " and " + auctionState.leagueTeams[inTeams[1]].name + " will now bid on whatsapp. ";
                    if (summary.teams.indexOf(inTeams[0]) == -1) {
                        biddingSummary += (auctionState.leagueTeams[inTeams[0]].name + " will start the bidding");
                    } else if (summary.teams.indexOf(inTeams[1]) == -1) {
                        biddingSummary += (auctionState.leagueTeams[inTeams[1]].name + " will start the bidding");
                    } else {
                        biddingSummary += (auctionState.leagueTeams[summary.teams[0]].name + " will start the bidding");
                    }
                    biddingSummary += " starting from " + (parseFloat(summary.bid) + 0.5) + " or more";
                } else {
                    if (!summary.outTeams[currentTeamId]) {
                        alert("Admin has asked for raised bids. Please check current leader and submit your bid.");
                    }
                    if ((summary.teams.indexOf(currentTeamId) != -1 || summary.allShouldBid) && !summary.outTeams[currentTeamId]) {
                        biddingSummary += "<br>Please submit your raised bid";
                    }
                }
                setTimeout(listenToBids, 5000);
            } else if (status == "Sold") {
                alert(currentPlayer.name + " sold! Please check the new auction state.");
                biddingSummary += ("<br>" + currentPlayer.name + " sold to " + auctionState.leagueTeams[summary.teams[0]].name + " at " + summary.bid);
                getState();
            } else if (status == "Unsold") {
                alert(currentPlayer.name + " goes unsold! Please check the new auction state");
                biddingSummary += ("<br>" + currentPlayer.name + " unsold.");
                getState();
            }
            $("#biddingSummaryDiv").html(biddingSummary);
        }

        function setSummary() {
            var summary = "";
            var history = auctionState.history;
            if (history != null && history.length > 0) {
                var lastAction = history[history.length - 1];
                summary += getLastActionText(lastAction);
            }
            else {
                summary += "*Auction starts:*";
            }
            for (var i in auctionState.leagueTeams) {
                var team = auctionState.leagueTeams[i];
                summary += "<br>";
                summary += team.name + " (" + team.budgetLeft + "L):";
                summary += "(Players bought: " + team.actions.length + "):";
                var actions = team.actions;
                if (actions == null || actions.length == 0) {
                    summary += " No players yet."
                }
                else {
                    for (var j in actions) {
                        var action = actions[j];
                        if (!action) continue;
                        var player = auctionState.players[action.playerId];
                        summary += " " + player.name + ",";
                    }
                    summary = summary.substr(0, summary.length - 1);
                }
            }
            $("#summaryDiv").html(summary);
        }

        function getLastActionText(lastAction) {
            var leagueTeamId = lastAction.leagueTeamId;
            var playerId = lastAction.playerId;
            var bid = lastAction.bid;

            var player = auctionState.players[playerId];
            if (leagueTeamId == null) {
                return "*" + player.name + " goes unsold*";
            }

            var leagueTeam = auctionState.leagueTeams[leagueTeamId];

            return "*" + player.name + " is sold to " + leagueTeam.name + " at " + bid + "L*";
        }

        function showHistory() {
            $("#historyModal").modal();
        }

        function getState() {
            $.ajax
            ({
                type: "POST",
                url: "getState.php",
                success: function (data) {
                    auctionState = data;
                    updateAuctionState();
                    for(var i in auctionState.leagueTeams) {
                        if (auctionState.leagueTeams[i].name == userName) {
                            currentTeamId = i;
                            $("#currentTeamDiv").html("Welcome " + auctionState.leagueTeams[currentTeamId].name);
                            break;
                        }
                    }
                    listenToBids();
                }
            });
        }

        function submitBid() {
            if (submitted) {
                alert("Kiti vela submit karnar ata? Bid already submitted. Waiting for others.");
                return;
            }
            if (summary.teams.indexOf(currentTeamId) != -1 && !summary.allShouldBid) {
                alert("Kiti ghaai! You already have the highest bid. Please wait for others to submit their raised bids.");
                return;
            }
            if (summary.outTeams[currentTeamId]) {
                alert("You have already submitted 'No Bid' for this player. You cannot bid for this player now. Shetta ghya ata jaanuchi.");
                return;
            }
            var bid = parseFloat($("#bidText").val());
            var basePrice = parseFloat(auctionState.players[currentPlayer.id].basePrice);
            var highestBid = parseFloat(summary.bid);
            console.log(bid);
            console.log(basePrice);
            console.log(highestBid);
            if (isNaN(bid)) {
                alert("Ata shivya dein! Bid should be numeric");
                return;
            }
            if (bid > parseFloat(auctionState.leagueTeams[currentTeamId].budgetLeft)) {
                alert("Kiti players pahije baba? This bid exceeds your remaining budget. Shetta ghya aata jaanuchi");
                return;
            }
            if (!((Math.floor(bid)==(bid-0.5)) || Math.floor(bid)==bid)) {
                alert("Rules vachat java re! Please enter bid only in the multiples of 0.5");
                return;
            }
            if (bid <= highestBid) {
                alert("Arey kanjoos! You cannot bid less than the current highest bid");
                return;
            }
            if (bid < basePrice) {
                alert("Are kanjoos! You cannot bid less than the base price of thye player");
                return;
            }
            $.ajax
            ({
                type: "POST",
                url: "setBid.php",
                data: {
                    round: auctionState.round,
                    bid: bid,
                    teamId: currentTeamId
                },
                success: function (data) {
                    if (data == "0") {
                        alert("Error in submitting bid. Please try again. (Say JSRM before submitting this time)");
                    } else {
                        alert("Bid submitted successfully");
                        submitted = true;
                    }
                },
                error: function () {
                    alert("Error in submitting bid. Please try again. (Say JSRM before submitting this time)");
                }
            });
        }

        function submitNoBid() {
            if (submitted) {
                alert("Kiti vela submit karnar ata? Bid already submitted. Waiting for others.");
                return;
            }
            if (summary.teams.indexOf(currentTeamId) != -1  && !summary.allShouldBid) {
                alert("Kiti ghaai! You have the highest bid currently. Please wait for others to submit their raised bids.");
                return;
            }
            if (summary.outTeams[currentTeamId]) {
                alert("Are ho kalla na baba! You have already submitted no bid for this player. Bakiche bid kartayt toparyanta gotya khajva tumhi jara");
                return;
            }
            $.ajax
            ({
                type: "POST",
                url: "setBid.php",
                data: {
                    round: auctionState.round,
                    bid: "No bid",
                    teamId: currentTeamId
                },
                success: function (data) {
                    if (data == "0") {
                        alert("Error in submitting 'No bid'. Please try again. (Say JSRM before submitting this time)");
                    } else {
                        alert("'No Bid' submitted successfully");
                        submitted = true;
                    }
                },
                error: function () {
                    alert("Error in submitting 'No bid'. Please try again. (Say JSRM before submitting this time)");
                }
            });
        }

        function init() {
            getState();
        }

        window.onload = init;
    </script>
</head>
<body>

<div class="container-fluid">
    <div class="panel panel-info">
        <div class="panel panel-body">
            <div class="row">
                <div class="col-sm-4" align="center">
                    <a href="#" onclick="showHistory(); return false;">Show history</a>
                </div>
                <div class="col-sm-4" align="center" id="currentTeamDiv">
                </div>
                <div class="col-sm-4" align="center">
                    <a href="index.php?logout=1">Log out</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">

        <div class="col-sm-3">
            <div class="panel panel-info">
                <div class="panel panel-heading" align="center">
                    Current round
                </div>
                <div class="panel panel-body" align="center" id="roundDiv">
                    0
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="panel panel-info" align="center">
                <div class="panel panel-heading">
                    Players sold
                </div>
                <div class="panel panel-body" id="soldPlayersCount">
                    0
                </div>
            </div>
        </div>

        <div class="col-sm-3" align="center">
            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Players unsold
                </div>
                <div class="panel panel-body" id="unsoldPlayersCount">
                    0
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="panel panel-info">
                <div class="panel panel-heading" align="center">
                    Players remaining
                </div>
                <div class="panel panel-body" align="center" id="remainingPlayersCount">
                    0
                </div>
            </div>
        </div>

    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3">
            <div class="panel panel-info btn-clock">
                <div class="panel panel-heading">
                    Teams
                </div>
                <div class="panel panel-body table-responsive">
                    <table class="table" id="leagueTeamsTable">
                        <thead>
                        <tr>
                            <th>Team name</th>
                            <th>Budget left</th>
                            <th>Players bought</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Current player bidding summary
                </div>
                <div class="panel panel-body" align="center" id="biddingSummaryDiv">
                    Not started yet
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Submit bid
                </div>
                <div class="panel panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Bid</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <input type="text" class="form-control" id="bidText">
                                </input>
                            </td>
                            <td>
                                <button class="btn btn-info form-control" onclick="submitBid()">Submit bid</button>
                            </td>
                            <td>
                                <button class="btn btn-info form-control" onclick="submitNoBid()">No bid</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Summary (Copy this to whatsapp at the end of each turn)
                </div>
                <div class="panel panel-body" id="summaryDiv">
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Players left
                </div>
                <div class="panel-panel-body height-responsive table-responsive">
                    <table class="table" id="allRemainingPlayersTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Base price</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Unsold players (To be taken in last round)
                </div>
                <div class="panel-panel-body height-responsive table-responsive">
                    <table class="table" id="unsoldRemainingPlayersTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Base price</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Unsold players
                </div>
                <div class="panel-panel-body height-responsive table-responsive">
                    <table class="table" id="unsoldPlayersTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Base price</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="leagueTeamPlayersModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="leagueTeamPlayersHeader">Modal Header</h4>
            </div>
            <div class="modal-body">
                <table class="table table-responsive" id="leagueTeamPlayersTable">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Bid</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="historyModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="historyHeader">Auction history</h4>
            </div>
            <div class="modal-body">
                <table class="table table-responsive" id="historyTable">
                    <thead>
                    <tr>
                        <th>Round</th>
                        <th>Player</th>
                        <th>Team</th>
                        <th>Bid</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

</body>
</html>