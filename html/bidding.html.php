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
        .bid-panel {
            height: 200px;
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

<div class="container-fluid">
    <div class="panel panel-info">
        <div class="panel panel-body">
            <div class="row">

                <div class="col-sm-3" align="center" id = "welcomeDiv">
                </div>

                <div class="col-sm-6">
                    <div class="panel panel-info">
                        <div class="panel panel-heading" align="center">
                            Current player
                        </div>
                        <div class="panel panel-body" align="center">
                            <div id ="updatesDiv">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3" align="center">
                    <h4><a href="index.php?logout=1">Log out</a></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="panel panel-info">
        <div class="panel panel-body">
            <div class="row">

                <div class="col-sm-3">
                    <div class="panel panel-info bid-panel">
                        <div class="panel panel-heading" align="center">
                            Current leading team
                        </div>
                        <div class="panel panel-body" align="center">
                            <div id = "currentLeaderDiv"></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="panel panel-info bid-panel">
                        <div class="panel panel-heading" align="center">
                            Current leading bid
                        </div>
                        <div class="panel panel-body" align="center">
                            <div id = "currentBidValueDiv"></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="panel panel-info bid-panel">
                        <div class="panel panel-heading" align="center">
                            Your next bid
                        </div>
                        <div class="panel panel-body" align="center">
                            <div id = "nextBidValueDiv"></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3">
                    <div class="panel panel-info bid-panel">
                        <div class="panel panel-heading" align="center">
                            Your action
                        </div>
                        <div class="panel panel-body" align="center">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6"><button class="btn btn-info form-control" id = "raiseButton"">Send bid</button></div>
                                    <div class="col-sm-6"><button class="btn btn-info form-control" id = "noBidButton"">No bid</button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>


<div class="container-fluid">
    <div class="panel panel-info">
        <div class="panel panel-body">
            <div class="row">

                <div class="col-sm-12">
                    <div class="panel panel-info">
                        <div class="panel panel-heading" align="center">
                            Auction Summary
                        </div>
                        <div class="panel panel-body" align="center">
                            <div id = "summaryDiv"></div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#welcomeDiv").html("<h4>Welcome " + userName + "</h4>");
</script>

<script src="js/bidding.js"></script>
</body>
</html>