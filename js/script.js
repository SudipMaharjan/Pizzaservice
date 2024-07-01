function addPizza(pizzaName, price){
    "use strict";
    //get select tag from id
    let addedPizza = document.getElementById("orders"); 
    //create an new option         
    let newElement = document.createElement("option");
    newElement.text = pizzaName;  
    newElement.setAttribute("data-price",price);                        
    addedPizza.appendChild(newElement);  
    updatePrice();
}

function removeSelected() {
    "use strict";
    let addedPizza = document.getElementById("orders");
    //returns all the selected options
    let selectedOptions = addedPizza.selectedOptions;           
    //loops throught the selected options and removes it
    for (let i = selectedOptions.length - 1; i >= 0; i--) {
        addedPizza.remove(selectedOptions[i].index);
    }
    updatePrice();
}

function removeAll(){
    "use strict";
    let addedPizza = document.getElementById("orders");
    //removes its first child until no child is left
    while(addedPizza.firstChild != null){
        addedPizza.removeChild(addedPizza.firstChild);
    }
    updatePrice();
}

function updatePrice() {
    "use strict";
    let totalPrice = 0.0;
    let addedPizza = document.getElementById("orders");
    for(let i = 0; i < addedPizza.options.length; i++) {
        totalPrice += parseFloat(addedPizza.options[i].getAttribute("data-price"));
    }
    totalPrice = totalPrice.toFixed(2);
    document.getElementById("totalPrice").textContent = "Preis: € " + totalPrice;
}



function readyToOrder(){
    "use strict";
    let addedPizza = document.getElementById("orders");
    let adresse = document.getElementById("adresse"); 
    if(addedPizza.firstChild == null || adresse.value == ''){
        let bestellbutton = document.getElementById("bestellbutton");
        bestellbutton.disabled = true;
        bestellbutton.style.opacity = "0.5";
    }
    else{
        document.getElementById("bestellbutton").disabled = false;
        bestellbutton.style.opacity = "1";
    }
}

function placeOrder(){
    "use strict";
    let addedPizza = document.getElementById("orders");
    for (let i = 0; i < addedPizza.options.length; i++) {
        addedPizza.options[i].selected = true;
    }
    document.getElementById("bestellen").submit();
}

// Add event listeners to trigger the function
document.getElementById("orders").addEventListener("change", readyToOrder);
document.getElementById("adresse").addEventListener("input", readyToOrder);

// Call the function initially to set the initial state
readyToOrder();

