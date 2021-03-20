(function (document, undefined) {
    var env = "bidder";

    var database;
    var firebaseScript = document.createElement("script");
    firebaseScript.src = "https://www.gstatic.com/firebasejs/4.12.1/firebase.js";
    firebaseScript.onload = function () {
        var config = {
            apiKey: 'AIzaSyD6jiFzXv1Bhq9gt_6QrNYzN7-cSTdVwZw',
            authDomain: 'fantasy-league-b5923.firebaseapp.com',
            databaseURL: 'https://fantasy-league-b5923.firebaseio.com',
            projectId: 'fantasy-league-b5923',
            storageBucket: 'gs://fantasy-league-b5923.appspot.com/'
        };
        firebase.initializeApp(config);
        database = firebase.database();

        init();
    };
    document.body.appendChild(firebaseScript);

    var listeners = {
        summary: {
            element: "summaryDiv",
            alert: false
        },
        nextPlayerText: {
            element: "updatesDiv",
            alert: true
        },
        lastActionText: {
            element: "updatesDiv",
            alert: true
        }
    };

    var currentLeader, currentBidValue, nextBidValue;

    var listenerCustomElements = {};

    function init() {
        for (var i in listeners) {
            var listener = listeners[i];
            (function (listener, i) {
                database.ref("auction/" + i).on("value", function (data) {
                    setData(data.val(), listener, i);
                })
            })(listener, i);
        }

        if (env == "bidder") {
            database.ref("auction/auctioneer/currentBid").on("value", function (data) {
                var currentBid = data.val() || {};

                console.log(currentBid);
                currentLeader = currentBid.team || "";
                if (currentLeader == userName) {
                    raiseButton.disabled = true;
                    noBidButton.disabled = true;
                } else {
                    raiseButton.disabled = false;
                    noBidButton.disabled = false;
                }
                currentBidValue = currentBid.value || "";
                if (!isNaN(currentBidValue) && currentLeader != "") {
                    nextBidValue = parseFloat(currentBidValue) + 0.5;
                } else {
                    if (!isNaN(currentBidValue)) {
                        nextBidValue = parseFloat(currentBidValue);
                        console.log(nextBidValue);
                    } else {
                        nextBidValue = 0;
                    }
                    currentBidValue = "No bids yet";
                    currentLeader = "No bids yet";
                }

                var currentBidValueDiv = document.getElementById("currentBidValueDiv");
                var currentLeaderDiv = document.getElementById("currentLeaderDiv");
                var nextBidValueDiv = document.getElementById("nextBidValueDiv");

                currentLeaderDiv.innerHTML = "<h4>" + currentLeader + "</h4>";
                currentBidValueDiv.innerHTML = "<h4>" + currentBidValue + "</h4>";
                if (!isNaN(nextBidValue)) {
                    nextBidValueDiv.innerHTML = "<h4>" + nextBidValue + "</h4>";
                }
            });

            database.ref("auction/bids/" + userName).on("value", function (data) {
                var bid = data.val();
                if (bid == "No Bid") {
                    raiseButton.disabled = true;
                    noBidButton.disabled = true;
                }
            });
        }

        var raiseButton = document.getElementById("raiseButton");
        raiseButton.onclick = raise;

        var noBidButton = document.getElementById("noBidButton");
        noBidButton.onclick = noBid;
    }

    function setData(data, listener, i) {
        console.log(i, listener, data);
        if (data == null) {
            data = "";
        }
        if (env == "auctioneer") {
            data = data.replaceAll("<br>", "\n");
        }

        if (data != "") {
            var element = listenerCustomElements[i] || document.getElementById(listener.element);
            element.innerHTML = "<h4>" + data + "</h4>";
        }

        if (listener.alert && data != "") {
            if (env == "auctioneer") {
                send();
            } else {
                alert(data);
            }
        }
    }

    function send() {

    }

    function raise() {
        console.log("Sending " + nextBidValue);
        database.ref("auction/bids/" + userName).set(nextBidValue);
    }

    function noBid() {
        database.ref("auction/bids/" + userName).set("No Bid");
    }

})(document);