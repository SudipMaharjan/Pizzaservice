// request als globale Variable anlegen (haesslich, aber bequem)
var request = new XMLHttpRequest();

function requestData() { // fordert die Daten asynchron an
    "use strict";
    request.open("GET", "KundenStatus.php"); // URL für HTTP-GET
    request.onreadystatechange = processData; //Callback-Handler zuordnen
    request.send(null); // Request abschicken
}

function processData() {
    "use strict";
    if(request.readyState === 4) {// Uebertragung = DONE
        if (request.status === 200) {
        // HTTP-Status = OK
            if(request.responseText != null)
                process(request.responseText);// Daten verarbeiten
            else console.error ("Dokument ist leer");
        } else console.error ("Uebertragung fehlgeschlagen");
    } else ;
    // Uebertragung laeuft noch
}

function process (intext) {
    "use strict";
    var data = JSON.parse(intext);
    displayData(data);
}

function displayData(data) {
    "use strict";
    var container = document.getElementById("kundenContainer");
    container.textContent = ""; // Clear existing data

    if (Object.keys(data).length == 0) {
        let noDataMessage = document.createElement("h3");
        noDataMessage.textContent = "Keine Sessions / Bestellung vorhanden";
        container.appendChild(noDataMessage);
    } else {
        Object.keys(data).forEach(function(orderingAddress) {
            data[orderingAddress].forEach(function(details) {
                var pizzaName = details["articleName"];
                var statusInt = details["status"];
                var status = "";
                switch (statusInt) {
                    case "0":
                        status = "bestellt";
                        break;
                    case "1":
                        status = "Im Ofen";
                        break;
                    case "2":
                        status = "fertig";
                        break;
                    case "3":
                        status = "unterwegs";
                        break;
                    case "4":
                        status = "geliefert";
                        break;
                    default:
                        status = "Error fetching status";
                }
                let infoArticle = document.createElement("article");
                var info = document.createElement("h3");
                info.textContent = pizzaName + ": " + status;
                infoArticle.appendChild(info);
                container.appendChild(infoArticle);
            });
            // Create and append a horizontal rule
            let  horizontalLine = document.createElement("hr");
            container.appendChild(horizontalLine);
        });
    }
}

window.onload = function() {
    window.setInterval(requestData, 2000);
};