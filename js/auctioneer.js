(function (window, document, undefined) {

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

    function init() {
        database.ref("auction/auctioneer/currentBid").on("value", newBid);
        database.ref("auction/summary").on("value", summaryUpdated);
        database.ref("auction/nextPlayerText").on("value", nextPlayerSelected);
        database.ref("auction/lastActionText").on("value", biddingEnded);
    }

    function newBid(data) {
        var currentBid = data.val();
        var leader = currentBid.team || "",
            bid = currentBid.value;
        var message = "";
        if (leader == "") {
            message = "Please start bidding from " + getBidText(bid);
        } else {
            message = leader + " - " + getBidText(bid);
        }
        sendToWhatsapp(message);
    }

    function biddingEnded(data) {
        var text = data.val();
        sendToWhatsapp("🔨");
        sendToWhatsapp(text + " 🔨");
    }

    function nextPlayerSelected(data) {
        var text = data.val();
        sendToWhatsapp("*Starting bidding for next player*");
        sendToWhatsapp(text);
    }

    function summaryUpdated(data) {
        var summary = data.val();
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
            alert("Please click on message box");
        }
    }
})(window, document);