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
        console.log(userName);
        console.log(currentTeamId);
    </script>
</head>
<body>
<div id ="lastActionTextDiv"></div>
<br>
<div id = "summaryDiv"></div>
<br>
<div id = "nextPlayerTextDiv"></div>
<br>
<div id = "currentLeaderDiv"></div>
<br>
<div id = "currentBidValueDiv"></div>
<br>
<button id = "raiseButton"">Raise to</button>
<div id = "nextBidValueDiv"></div>
<script src="js/bidding.js"></script>
</body>
</html>