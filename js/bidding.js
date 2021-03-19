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
            element: "nextPlayerTextDiv",
            alert: true
        },
        lastActionText: {
            element: "lastActionTextDiv",
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
                currentBidValue = currentBid.value || "";
                if (!isNaN(currentBidValue) && currentLeader != "") {
                    nextBidValue = parseFloat(currentBidValue) + 0.5;
                } else {
                    nextBidValue = parseFloat(currentBidValue);
                    currentBidValue = "No bids yet";
                }

                var currentBidValueDiv = document.getElementById("currentBidValueDiv");
                var currentLeaderDiv = document.getElementById("currentLeaderDiv");
                var nextBidValueDiv = document.getElementById("nextBidValueDiv");

                currentLeaderDiv.innerHTML = currentLeader;
                currentBidValueDiv.innerHTML = currentBidValue;
                if (!isNaN(nextBidValue)) {
                    nextBidValueDiv.innerHTML = nextBidValue;
                }
            });
        }

        var raiseButton = document.getElementById("raiseButton");
        raiseButton.onclick = raise;
    }

    function setData(data, listener, i) {
        console.log(i, listener, data);
        if (data == null) {
            data = "";
        }
        if (env == "auctioneer") {
            data = data.replaceAll("<br>", "\n");
        }

        var element = listenerCustomElements[i] || document.getElementById(listener.element);
        element.innerHTML = data;

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

})(document);