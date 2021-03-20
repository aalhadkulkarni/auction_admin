(function (window, document, undefined) {

    var messages = [];
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

        checkDiv();
    };
    document.body.appendChild(firebaseScript);

    function checkDiv() {
        var divs = document.getElementsByClassName("_1JAUF");
        if (divs.length != 2) {
            setTimeout(checkDiv, 50);
            return;
        }
        divs[1].onclick = init;
    }

    function init() {
        database.ref("auction/auctioneer/currentBid").on("value", newBid);
        database.ref("auction/summary").on("value", summaryUpdated);
        database.ref("auction/nextPlayerText").on("value", nextPlayerSelected);
        database.ref("auction/lastActionText").on("value", biddingEnded);

        var bidTeams = ["Thane", "Miraj", "Karad", "Kolhapur", "Pune"];
        for (var i = 0; i < bidTeams.length; i++) {
            var bidTeam = bidTeams[i];
            (function(bidTeam) {
                database.ref("auction/bids/" + bidTeam).on("value", function(data) {
                    var bid = data.val();
                    if (bid == "No Bid") {
                        sendToWhatsapp(bidTeam + " - " + bid);
                    }
                });
            })(bidTeam);
        }
    }

    function newBid(data) {
        var currentBid = data.val() || {};
        var leader = currentBid.team || "",
            bid = currentBid.value;
        var message = "";
        if (leader != "") {
            message = leader + " - " + getBidText(bid);
        }
        if (message == "") {
            return;
        }
        sendToWhatsapp(message);
    }

    function noBid() {

    }

    function biddingEnded(data) {
        var text = data.val();
        if (text == null || text == "") {
            return;
        }
        sendToWhatsapp("🔨");
        sendToWhatsapp(text + " 🔨");
    }

    function nextPlayerSelected(data) {
        var text = data.val();
        if (text == null || text == "") {
            return;
        }
        sendToWhatsapp("*Starting bidding for next player*");
        sendToWhatsapp(text);
    }

    function summaryUpdated(data) {
        var summary = data.val();
        if (summary == null || summary == "") {
            return;
        }
        console.log(summary, typeof summary);
        summary = summary.replaceAll("<br>", "\n");
        sendToWhatsapp(summary);
    }

    function getBidText(bid) {
        return bid + "L";
    }

    function sendToWhatsapp(message) {
        try {
            document.execCommand("insertText", false, message);
            document.getElementsByClassName('_1E0Oz')[0].click();
        } catch (e) {
            console.log(e);
            console.log(message);
            //alert("Please click on message box");
        }
    }
})(window, document);