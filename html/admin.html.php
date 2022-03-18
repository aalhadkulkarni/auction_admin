<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auction</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <script src="https://www.gstatic.com/firebasejs/4.12.1/firebase.js"></script>
    <script>
        // Initialize Firebase
        var config = {
            apiKey: 'AIzaSyD6jiFzXv1Bhq9gt_6QrNYzN7-cSTdVwZw',
            authDomain: 'fantasy-league-b5923.firebaseapp.com',
            databaseURL: 'https://fantasy-league-b5923.firebaseio.com',
            projectId: 'fantasy-league-b5923',
            storageBucket: 'gs://fantasy-league-b5923.appspot.com/'
        };
        firebase.initializeApp(config);
        var database = firebase.database();
    </script>
    <style>
        .table-responsive {
            max-height: 300px;
        }
    </style>

    <script>
        var userName = "";
        <?php echoVariables(); ?>
    </script>

    <script>
        var stopBids = true;
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

        function initEmptyArraysBecauseFirebaseIsABitch() {
            auctionState.history = auctionState.history || [];
            auctionState.players = auctionState.players || [];
            auctionState.allRemainingPlayers = auctionState.allRemainingPlayers || [];
            auctionState.unsoldPlayersRemaining = auctionState.unsoldPlayersRemaining || [];
            auctionState.unsoldPlayers = auctionState.unsoldPlayers || [];
            auctionState.leagueTeams = auctionState.leagueTeams || [];
            auctionState.categories = auctionState.categories || [];
            auctionState.soldPlayers = auctionState.soldPlayers || [];
            auctionState.soldPlayersCount = auctionState.soldPlayersCount || 0;
            auctionState.unsoldPlayersCount = auctionState.unsoldPlayersCount || 0;
            auctionState.remainingUnsoldPlayersCount = auctionState.remainingUnsoldPlayersCount || 0;
            auctionState.allRemainingPlayersCount = auctionState.allRemainingPlayersCount || 0;
        }

        function updateAuctionState() {
            currentOut = {};
            initEmptyArraysBecauseFirebaseIsABitch();
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
            resetBids();
        }

        function setStatistics() {
            $("#soldPlayersCount").text(auctionState.soldPlayersCount);
            $("#unsoldPlayersCount").text(auctionState.unsoldPlayersCount + auctionState.remainingUnsoldPlayersCount);
            $("#remainingPlayersCount").text(auctionState.allRemainingPlayersCount);
        }

        function resetAllData() {
            currentPlayer = null;
            database.ref("auction/nextPlayerText").set(null);
            $("#currentPlayerText").html("No player selected");
            $("#bidText").val("");
        }

        function setRound() {
            $("#roundDiv").text(auctionState.round);
        }

        function setHistory() {
            $("#historyTable tbody").empty();
            var history = auctionState.history;;
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

                var option = "<option value = '" + leagueTeam.id + "'>" + leagueTeam.shortName + "</option>";
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
                removeCol.html("<a href='#' onclick='removePlayer(" + leagueTeamId + ", " + action.playerId + "); return false;'>Remove Player</a>");

                var playerRow = $("<tr></tr>");
                playerRow.append(playerCol);
                playerRow.append(bidCol);
                playerRow.append(removeCol);

                $("#leagueTeamPlayersTable tbody").append(playerRow);
            }
        }

        function removePlayer(leagueTeamId, playerId) {
            var leagueTeam = auctionState.leagueTeams[leagueTeamId];
            var actions = leagueTeam.actions;
            var player = auctionState.players[playerId];

            for (var i in actions) {
                var action = actions[i];
                if (action.playerId == playerId) {
                    if (confirm("Remove " + player.name + " from " + leagueTeam.name + "?")) {
                        delete(actions[i]);
                        leagueTeam.budgetLeft += parseFloat(player.basePrice);
                        auctionState.ruledOutPlayers = auctionState.ruledOutPlayers || [];
                        auctionState.ruledOutPlayers.push(player);
                        for (var i in auctionState.soldPlayers) {
                            if (auctionState.soldPlayers[i].id == playerId) {
                                delete(auctionState.soldPlayers[i]);
                            }
                        }
                        auctionState.soldPlayersCount--;
                        break;
                    }
                }
            }

            saveState();
            updateAuctionState();
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
            var role = $("#roleSelect").val();
            var slab = $("#slabSelect").val();

            var categoryPlayers = auctionState.allRemainingPlayers;
            /*if(category == 9)
             {
             categoryPlayers = auctionState.unsoldPlayersRemaining;
             }*/

            $("#currentCategoryPlayersTable tbody").empty();
            for (var j in categoryPlayers) {
                var player = categoryPlayers[j];
                if ((player.role == role || role == "All") && (player.slab == slab || slab == "All")) {
                    var nameCol = $("<td></td>");
                    nameCol.html(player.name);

                    var roleCol = $("<td></td>");
                    roleCol.html(player.role);

                    var priceCol = $("<td></td>");
                    priceCol.html(player.basePrice + "L");

                    var teamCol = $("<td></td>");
                    teamCol.html(player.team);

                    var playerRow = $("<tr id='player" + player.id + "' class='playerRow' onclick='setCurrentPlayer(" + player.id + ")'></tr>");
                    playerRow.append(nameCol);
                    playerRow.append(roleCol);
                    playerRow.append(priceCol);
                    playerRow.append(teamCol);

                    $("#currentCategoryPlayersTable tbody").append(playerRow);
                }
            }
        }

        function setCurrentPlayer(playerId) {
            currentPlayer = auctionState.players[playerId];
            $(".playerRow").removeClass("info");
            $("#player" + currentPlayer.id).addClass("info");
        }

        function stopBidding() {
            resetBids();
            stopBids = true;
        }

        function getRandom(from, to) {
            return Math.floor(Math.random() * (to - from + 1) + from);
        }

        function setRandomPlayer() {
            var rows = $(".playerRow");
            var selectedRow = getRandom(1, rows.length) - 1;
            rows[selectedRow].click();
        }

        function startBiddingForPlayer() {
            biddingStopped = false;
            database.ref("auction/auctioneer/currentBid").set({});
            database.ref("auction/bids").set({});
            setRandomPlayer();
            stopBids = false;
            var text = "Next " + currentPlayer.role + " is " + currentPlayer.name + " (" + currentPlayer.team + ") with base price " + currentPlayer.basePrice + "L";
            database.ref("auction/auctioneer/currentBid").set({
                team: "",
                value: currentPlayer.basePrice
            });
            database.ref("auction/nextPlayerText").set(text);
            database.ref("auction/lastActionText").set("");
            updateTimer(10000);
            $("#currentPlayerText").html(text);

            resetBids();
            setBiddingSummary("Bids");
            saveSummary(listenToBids, function () {
                alert("Could not start bidding, please try again");
                resetBids();
            });
        }

        function listenToBids() {
            return;
            if (stopBids) {
                return;
            }
            $.ajax
            ({
                type: "POST",
                url: "getBids.php",
                data: {
                    round: auctionState.round,
                    playerId: currentPlayer.id,
                },
                success: function (bids) {
                    if (stopBids) {
                        return;
                    }
                    for (var teamId in bids) {
                        if (!currentBids[teamId]) {
                            currentBids[teamId] = bids[teamId];
                        }
                    }
                    for (var i in bids) {
                        if (bids[i] == "No bid") {
                            summary.outTeams[i] = 1;
                        }
                    }
                    var bidsReceived = 0;
                    for (var i in auctionState.leagueTeams) {
                        if (currentBids[i] && currentBids[i] != "No bid") {
                            //window.printNow && console.log(i + " : " + currentBids[i]);
                            bidsReceived++;
                        }
                    }
                    for (var i in summary.outTeams) {
                        bidsReceived++;
                    }
                    updateCurrentBids();
                    if (bidsReceived == 6/* && !summary.allShouldBid*/) {
                        alert("All bids are in");
                    } else if (!stopBids) {
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

        function updateCurrentBids() {
            var table = $("#bidsTable tbody");
            table.empty();
            for (var teamId in auctionState.leagueTeams) {
                var teamName = auctionState.leagueTeams[teamId].name;
                var bid = "Not received yet";
                if (currentBids[teamId]) {
                    bid = currentBids[teamId];
                }
                for (var i in summary.outTeams) {
                    if (i == teamId) {
                        bid = "No bid";
                    }
                }
                var tr = $("<tr></tr>");

                var teamCol = $("<td></td>");
                teamCol.html(teamName);

                var bidCol = $("<td></td>");
                bidCol.html(bid);

                tr.append(teamCol);
                tr.append(bidCol);
                table.append(tr);
            }
        }

        function setSummary() {
            var summary = "";
            var history = auctionState.history;
            if (history != null && history.length > 0) {
                var lastAction = history[history.length - 1];
                var lastActionText = getLastActionText(lastAction);
                database.ref("auction/lastActionText").set(lastActionText);
                summary += lastActionText;
            }
            else {
                summary += "*Auction starts:*";
            }
            for (var i in auctionState.leagueTeams) {
                var team = auctionState.leagueTeams[i];
                summary += "<br>";
                summary += team.name + " (" + team.budgetLeft + "L):";
                team.actions = team.actions || [];
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
            database.ref("auction/summary").set(summary);
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

        function decideWinner() {
            setBiddingSummary("Raise");
            var winningBid = summary && summary.bid;
            var winningTeams = summary && summary.teams;
            if (winningTeams.length > 1) {
                alert("Multiple teams have same bid. Cannot decide winner.");
                return;
            }
            if (winningTeams.length == 1) {
                $("#bidText").val(winningBid);
                $("#leagueTeamsSelect").val(winningTeams[0]);
                playerSold();
            } else {
                playerUnsold();
            }
        }

        function setBiddingSummary(status) {
            return;
            summary.allShouldBid = false;
            summary.playerId = currentPlayer.id;
            status && (summary.status = status);
            summary.teams = [];
            for (var i in currentBids) {
                if (currentBids[i] == "No bid" || (status == "Raise" && !currentBids[i])) {
                    summary.outTeams[i] = 1;
                }
                else if (currentBids[i]) {
                    if (currentBids[i] == summary.bid) {
                        summary.teams.push(i);
                    }
                    else if (parseFloat(currentBids[i]) > parseFloat(summary.bid) || !summary.bid) {
                        summary.bid = currentBids[i];
                        summary.teams = [i];
                    }
                }
            }
            if (currentPlayer.slab == "Marquee") {
                var allMax = true;
                for (var i in currentBids) {
                    var curBid = parseFloat(currentBids[i]);
                    if (!curBid || isNaN(curBid)) {
                        continue;
                    }
                    if (curBid < parseFloat(summary.bid)) {
                        allMax = false;
                        break;
                    }
                }
                if (allMax) {
                    for (var i in summary.teams) {
                        console.log("Resetting " + summary.teams[i]);
                        currentBids[summary.teams[i]] = undefined;
                    }
                    summary.allShouldBid = true;
                }
            } else if (currentPlayer.slab == "Star") {
                if (summary.teams.length >= 2) {
                    summary.allShouldBid = true;
                    for (var i in auctionState.leagueTeams) {
                        if (summary.teams.indexOf(i) == -1) {
                            currentBids[i] = "No bid";
                            summary.outTeams[i] = 1;
                        }
                    }
                }
            }
            if (status == "Sold") {
                summary.teams = [$("#leagueTeamsSelect").val()];
                summary.bid = $("#bidText").val();
            }
        }

        function saveSummary(success, fail) {
            return;
            var round = auctionState.round;
            if (summary.status == "Sold" || summary.status == "Unsold") {
                round--;
            }
            $.ajax
            ({
                type: "POST",
                url: "saveSummary.php",
                data: {
                    round: round,
                    summary: JSON.stringify(summary)
                },
                success: function (data) {
                    if (data == "1") {
                        typeof success == "function" && success();
                    } else {
                        typeof fail == "function" && fail();
                    }
                },
                error: function () {
                    typeof fail == "function" && fail();
                }
            });
        }

        function askForRaise() {
            setBiddingSummary("Raise");
            for (var i in currentBids) {
                if (summary.teams.indexOf(i) != -1 && !summary.allShouldBid) {
                    currentBids[i] = summary.bid;
                } else if (summary.outTeams[i]) {
                    currentBids[i] = "No bid";
                } else {
                    currentBids[i] = undefined;
                }
            }
            saveSummary(listenToBids, askForRaise);
        }

        function resetBids() {
            return;
            currentBids = {};
            summary = {
                playerId: null,
                status: "Not started",
                bid: null,
                teams: [],
                outTeams: {}
            };
            setSummary("Not started");
        }

        function playerSold() {
            currentLeader = null;
            currentBidValue = null;
            if (currentPlayer == null) {
                alert("No player selected");
                return;
            }
            var winningLeagueTeamId = $("#leagueTeamsSelect").val();
            var currentPlayerId = currentPlayer.id;
            var winningBid = $("#bidText").val();
            if (winningBid == null || winningBid == "") {
                alert("Winning bid not entered");
                return;
            }
            if (parseFloat(winningBid) < parseFloat(currentPlayer.basePrice)) {
                alert("Winning bid less than base price entered");
                return;
            }
            biddingStopped = true;
            var x = confirm("Sell " + currentPlayer.name + " to " + auctionState.leagueTeams[winningLeagueTeamId].name + " at " + winningBid + "L?");
            console.log(x);
            if (!x) {
                biddingStopped = false;
                console.log("Returning");
                return;
            }
            var action =
            {
                leagueTeamId: winningLeagueTeamId,
                playerId: currentPlayerId,
                bid: winningBid
            };

            auctionState.history.push(action);
            auctionState.leagueTeams[winningLeagueTeamId].actions.push(action);
            auctionState.leagueTeams[winningLeagueTeamId].budgetLeft -= parseFloat(winningBid);
            auctionState.round++;
            if (auctionState.allRemainingPlayers[currentPlayerId]) {
                delete(auctionState.allRemainingPlayers[currentPlayerId]);
                auctionState.allRemainingPlayersCount--;
            }
            else {
                delete(auctionState.unsoldPlayersRemaining[currentPlayerId]);
                auctionState.remainingUnsoldPlayersCount--;
            }
            auctionState.soldPlayers.push(currentPlayer);
            auctionState.soldPlayersCount++;

            database.ref("auction/bids").set({});
            saveState();
            setBiddingSummary("Sold");
            saveSummary();
            stopBidding();
            updateAuctionState();
        }

        function saveState() {
            database.ref("auction/states/" + auctionState.round).set(auctionState);
            database.ref("auction/round").set(auctionState.round);
            downloadState();
        }

        function provisionalSold() {
            biddingStopped = true;
            currentLeader = null;
            currentBidValue = null;
            if (currentPlayer == null) {
                alert("No player selected");
                biddingStopped = false;
                return;
            }
            var winningLeagueTeamId = $("#leagueTeamsSelect").val();
            var winningBid = $("#bidText").val();
            if (winningBid == null || winningBid == "") {
                alert("Winning bid not entered");
                biddingStopped = false;
                return;
            }
            if (parseFloat(winningBid) < parseFloat(currentPlayer.basePrice)) {
                alert("Winning bid less than base price entered");
                biddingStopped = false;
                return;
            }

            var x = confirm("Sell " + currentPlayer.name + " to " + auctionState.leagueTeams[winningLeagueTeamId].name + " at " + winningBid + "L?");
            if (!x) {
                biddingStopped = false;
                return;
            }
            database.ref("auction/lastActionText").set("Sold! Does last year's owner want to use RTM?");
        }

        function playerUnsold() {
            if (currentPlayer == null) {
                alert("No player selected");
                return;
            }
            var currentPlayerId = currentPlayer.id;
            var action =
            {
                leagueTeamId: null,
                playerId: currentPlayerId,
                bid: 0
            };

            if (!confirm("Keep " + currentPlayer.name + " unsold?")) {
                return;
            }

            auctionState.history.push(action);
            auctionState.round++;
            if (auctionState.allRemainingPlayers[currentPlayerId]) {
                delete(auctionState.allRemainingPlayers[currentPlayerId]);
                auctionState.unsoldPlayersRemaining[currentPlayerId] = currentPlayer;
                auctionState.allRemainingPlayersCount--;
                auctionState.remainingUnsoldPlayersCount++;
            }
            else {
                delete(auctionState.unsoldPlayersRemaining[currentPlayerId]);
                auctionState.unsoldPlayers[currentPlayerId] = currentPlayer;
                auctionState.remainingUnsoldPlayersCount--;
                auctionState.unsoldPlayersCount++;
            }

            database.ref("auction/bids").set({});
            saveState();
            setBiddingSummary("Unsold");
            saveSummary();
            stopBidding();
            updateAuctionState();
        }

        function showHistory() {
            $("#historyModal").modal();
        }

        function undo() {
            if (auctionState.round == 1) {
                return;
            }
            if (!confirm("Undo the last round? The change will be permanent.")) {
                return;
            }
            database.ref("auction/auctioneer/currentBid").set("");
            currentLeader = null;
            currentBidValue = null;
            database.ref("auction/bids").set({});
            database.ref("auction/round").set(auctionState.round - 1);
            database.ref("auction/states/" + auctionState.round).set({});
            resume(auctionState.round - 1);
            $.ajax
            ({
                type: "POST",
                url: "getState.php",
                data: {
                    round: auctionState.round - 1
                },
                success: function (data) {
                    auctionState = data;
                    saveState();
                    updateAuctionState();
                }
            });
        }

        function restart() {
            if (!confirm("Restart the auction? All the data of the auction done till now will be lost permanently.")) {
                return;
            }
            $.ajax
            ({
                type: "POST",
                url: "getState.php",
                data: {
                    hardreset: "1"
                },
                success: function () {
                    init();
                }
            })
        }

        function downloadState() {
            return;
            var str = JSON.stringify(auctionState);
            var uri = 'data:text/plain;charset=utf-8,' + str;

            var downloadLink = document.createElement("a");
            downloadLink.href = uri;
            downloadLink.download = (auctionState.round) + " Auction Round.txt";

            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        function init() {
            currentLeader = null;
            currentBidValue = null;
            database.ref("auction").set("");
            $.ajax
            ({
                type: "POST",
                url: "getState.php",
                success: function (data) {
                    auctionState = data;
                    saveState();
                    updateAuctionState();
                }
            });
        }

        function resume(round) {
            if (!isNaN(round)) {
                getFirebaseState(round);
            } else {
                database.ref("auction/round")
                    .once("value")
                    .then(function(data) {
                        var round = data.val();
                        getFirebaseState(round);
                    });
            }
        }

        function getFirebaseState(round) {
            database.ref("auction/states/" + round)
                .once("value")
                .then(function (data) {
                    auctionState = data.val();
                    updateAuctionState();
                });
        }
        window.onload = function () {
            listen();
            resume();
        };

        var currentLeader, currentBidValue;
        var currentOut = {}, biddingStopped = true;

        function listen() {
            database.ref("auction/actioneer/currentBid")
                .once("value")
                .then(function (data) {
                    var obj = data.val() || {};
                    setLeader(obj.team, obj.value, true);
                    startListeningToBids();
                });
        }

        function isTeamOut(team) {
            return currentOut[team] || biddingStopped;
        }

        function remind(attempt) {
            attempt = attempt || 1;
            var message = "";
            var bidTeams = ["Thane", "Miraj", "Karad", "Kolhapur", "Pune", "USA"];
            for (var i = 0; i < bidTeams.length; i++) {
                var bidTeam = bidTeams[i];
                if (!isTeamOut(bidTeam) && bidTeam != currentLeader) {
                    message += bidTeam + " ";
                }
            }

            var duration = 10000;
            if (message != "") {
                message += "\n";
                if (attempt == 1) {
                    message += "Bids please";
                } else if (attempt == 2) {
                    message += "Bids please (2nd reminder)";
                    duration = 10000;
                } else if (attempt == 3) {
                    message += "Bids please *(last call)*";
                    duration = 10000;
                } else if (attempt > 3) {
                    message += "Timed out";
                    duration = null;
                }
                database.ref("auction/reminder").set(message);
                if (duration != null) {
                    window.remindTimer = setTimeout(function () {
                        remind(attempt + 1);
                    }, duration);
                }
            } else {
                database.ref("auction/reminder").set("");
            }
        }

        function startListeningToBids() {
            var bidTeams = ["Thane", "Miraj", "Karad", "Kolhapur", "Pune", "USA"];
            for (var i = 0; i < bidTeams.length; i++) {
                var bidTeam = bidTeams[i];
                (function(bidTeam) {
                    database.ref("auction/bids/" + bidTeam).on("value", function(data) {
                        if (isTeamOut(bidTeam)) {
                            return;
                        }
                        var bid = data.val();
                        if (bid == "No Bid") {
                            currentOut[bidTeam] = true;
                            var curOutCount = 0;
                            for (var i in currentOut) {
                                if (currentOut[i]) {
                                    curOutCount++;
                                }
                            }
                            if ((curOutCount == 5 && currentLeader != null) || curOutCount == 6) {
                                alert("Everyone has given no bid");
                            }
                            console.log("Setting 30s timer after a no bid from " + bidTeam);
                            //updateTimer(30000);
                        } else {
                            bid = parseFloat(bid);
                        }
                        console.log(bid);
                        console.log(currentLeader);
                        if (isNaN(bid)) {
                            return;
                        }
                        if (currentLeader == null || isNaN(currentBidValue)) {
                            console.log("Here1");
                            setLeader(bidTeam, bid);
                        } else if (bid == currentBidValue + 0.5) {
                            console.log("Here2");
                            setLeader(bidTeam, bid);
                        }
                    });
                })(bidTeam);
            }
        }

        function updateTimer(duration) {
            if (window.remindTimer) {
                clearTimeout(window.remindTimer);
            }
            window.remindTimer = setTimeout(remind, duration);
        }

        function setLeader(bidTeam, bid, fromDb) {
            if (isNaN(bid)) {
                return;
            }
            currentLeader = bidTeam;
            currentBidValue = bid;
            var options = $("#leagueTeamsSelect")[0].options;
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (option.innerText == currentLeader) {
                    option.selected = true;
                    break;
                }
            }
            $("#bidText").val(currentBidValue);

            console.log("Setting 20s timer after a bid from " + bidTeam + " for " + bid);
            updateTimer(10000);
            if (!fromDb) {
                database.ref("auction/auctioneer/currentBid").set({
                    team: bidTeam,
                    value: bid
                });
            }
        }
    </script>
</head>
<body>

<div class="container-fluid">
    <div class="panel panel-info">
        <div class="panel panel-body">
            <div class="row">
                <div class="col-sm-3" align="center">
                    <a href="#" onclick="restart(); return false;">Restart auction</a>
                </div>
                <div class="col-sm-3" align="center">
                    <a href="#" onclick="undo(); return false;">Undo</a>
                </div>
                <div class="col-sm-3" align="center">
                    <a href="#" onclick="showHistory(); return false;">Show history</a>
                </div>
                <div class="col-sm-3" align="center">
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
                <div class="panel panel-heading" id="currentCategoryLabel">
                    Current category
                </div>
                <div class="panel panel-body" id="currentCategoryPlayersTable">
                    <div class="row">
                        <div class="col-sm-6">
                            <select class="form-control" id="slabSelect" onchange="setCurrentCategory()">
                                <option>Marquee</option>
                                <option>Star</option>
                                <option>Draft</option>
                                <option>RTR</option>
                                <option selected>All</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" id="roleSelect" onchange="setCurrentCategory()">
                                <option>Batsman</option>
                                <option>Bowler</option>
                                <option>All Rounder</option>
                                <option>Keeper</option>
                                <option selected>All</option>
                            </select>
                        </div>
                    </div>
                    <div class="row table-responsive">
                        <div class="col-sm-12">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Player name</th>
                                    <th>Role</th>
                                    <th>Base price</th>
                                    <th>Cricket Team</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button class="btn btn-info form-control" onclick="startBiddingForPlayer()">Choose a random player and start bidding
                        </button>
                    </div>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Next player (Copy this text to whatsapp)
                </div>
                <div class="panel panel-body" align="center" id="currentPlayerText">
                    No player selected
                </div>
            </div>

            <div class="panel panel-info" style="display: none;">
                <div class="panel panel-heading">
                    Current bids
                </div>
                <div class="panel panel-body" align="center" id="bidsPanel">
                    <table class="table" id="bidsTable">
                        <thead>
                        <tr>
                            <th>
                                Team
                            </th>
                            <th>
                                Bid
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-4">
                            <button class="btn btn-info form-control" onclick="decideWinner()">Declare result
                            </button>
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-info form-control" onclick="askForRaise()">Ask for raise
                            </button>
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-info form-control" onclick="stopBidding()">Stop bidding
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-info">
                <div class="panel panel-heading">
                    Select winner
                </div>
                <div class="panel panel-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Winning team</th>
                            <th>Price</th>
                            <th colspan="3">Result</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <select class="form-control" id="leagueTeamsSelect">
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="bidText"/>
                            </td>
                            <td>
                                <button class="btn btn-info form-control" onclick="playerSold()">Sold!</button>
                            </td>
                            <td>
                                <button class="btn btn-info form-control" onclick="playerUnsold()">Unsold!</button>
                            </td>
                            <td>
                                <button class="btn btn-info form-control" onclick="provisionalSold()">Provisional sold</button>
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
