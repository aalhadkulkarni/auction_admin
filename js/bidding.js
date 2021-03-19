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

})(document);