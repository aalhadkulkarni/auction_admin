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
        sendOldMessages();
        database.ref("auction/auctioneer/currentBid").on("value", newBid);
        database.ref("auction/summary").on("value", summaryUpdated);
        database.ref("auction/nextPlayerText").on("value", nextPlayerSelected);
        database.ref("auction/lastActionText").on("value", biddingEnded);
    }

    function sendOldMessages() {
        var success = -1;
        try {
            for (var i = 0; i < messages.length; i++) {
                sendToWhatsapp(messages[i]);
                success = i;
            }
        } catch (e) {
            var newMessages = [];
            for (var i = success + 1; i < messages.length; i++) {
                newMessages.push(messages[i]);
            }
            messages = newMessages;
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
        sendToWhatsapp(message);
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
            messages.push(message);
            //alert("Please click on message box");
        }
    }
})(window, document);