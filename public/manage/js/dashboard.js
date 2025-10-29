var reservations = [];
var listings = [];
var modalReservationId;
var listingIdToDelete;

function changeFoodStop(foodStopId) {
    window.location = 'dashboard.php?foodstop=' + foodStopId;
}

var selector = document.getElementById("foodStopSelector");
if (selector != null) {
        selector.onchange = function () {
        changeFoodStop(this.value);
    }
}
document.getElementById("foodStopSelector")

document.getElementById("reloadButton").onclick = function () {
    window.location.reload();
}

function checkListings() {
    fetch('dashboard.php?action=ping')
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(error => {
            console.error('Error:', error);
        });
}

// Get the foodstop set in the URL to add to getting the right listings or reservations
const url = new URL(window.location.href);
var foodstop = url.searchParams.get('foodstop');
if (foodstop != null) {
    foodstop = "&foodstop=" + foodstop;
}
else {
    foodstop = "";
}

fetch('dashboard.php?action=listings' + foodstop)
    .then(response => response.json())
    .then(data =>
        data.forEach(listing =>
            listings.push(listing)
        )
    );

fetch('dashboard.php?action=reservations' + foodstop)
    .then(response => response.json())
    .then(data =>
        data.forEach(reservation =>
            reservations.push(reservation)
        )
    );

// Keep food manager session alive
window.setInterval(checkListings, 10000);

var confirmModal = document.getElementById('confirmModal');
var placeModal = document.getElementById('placeOrderModal');
var deleteModal = document.getElementById('deleteModal');

function getReservation(id) {
    var reservation = null;

    for (var i = 0; i < reservations.length; i++) {
        reservation = reservations[i];
        if (reservation.reservationId === parseInt(id)) {
            break;
        }
    }
    return reservation;
}

function updatePickupListingQuantity(listing) {
    let select = placeModal.querySelector("#selectedQuantity");
    select.innerHTML = "";
    for (let i = 1; i <= listing.quantityRemaining; i++) {
        let option = document.createElement("option");
        option.value = i.toString();
        option.innerText = i.toString();
        select.appendChild(option);
    }
}

deleteModal.querySelector("#deleteListingButton").addEventListener('click', function event() {
    console.log("Deleting");
    window.location = 'dashboard.php?action=delete&listingId=' + listingIdToDelete + foodstop;
});

deleteModal.addEventListener('show.bs.modal', function (event) {
    let sourceButton = event.relatedTarget;
    listingIdToDelete = sourceButton.getAttribute("data-bs-id");
});


placeModal.querySelector("#selectedListing").addEventListener('change', function (event) {
    let select = placeModal.querySelector("#selectedListing");
    listings.forEach(listing => {
        if (parseInt(listing.listingId) === parseInt(select.value)) {
            updatePickupListingQuantity(listing);
        }
    });

});

placeModal.querySelector("#submit").addEventListener("click", function (event) {
    let listingId = placeModal.querySelector("#selectedListing").value;
    let quantity = placeModal.querySelector("#selectedQuantity").value;
    window.location = 'dashboard.php?action=place&listingId=' + listingId + '&quantity=' + quantity + foodstop;
});

placeModal.addEventListener('show.bs.modal', function (event) {
    // Show listings
    let select = placeModal.querySelector("#selectedListing");
    select.innerHTML = "";
    for (let i = 0; i < listings.length; i++) {
        let listing = listings[i];
        let option = document.createElement("option");
        option.value = listing.listingId;
        option.innerText = listing.title + " (" + listing.quantityRemaining + " Remaining)";
        select.appendChild(option);
    }

    updatePickupListingQuantity(listings[0]);

});
confirmModal.querySelector("#submit").addEventListener("click", function (event) {
    let reservation = getReservation(modalReservationId);
    let quantityRetrieved = confirmModal.querySelector("#selectedAmount").value;
    window.location = 'dashboard.php?action=retrieve&reservationId=' + reservation.reservationId + '&quantity=' + quantityRetrieved + foodstop;
});

confirmModal.addEventListener('show.bs.modal', function (event) {
    let sourceButton = event.relatedTarget;

    modalReservationId = sourceButton.getAttribute("data-bs-id");
    let reservation = getReservation(modalReservationId);

    //TODO: Check if reservation is null
    let quantity = reservation.quantity;

    confirmModal.querySelector("#selectedAmount").innerHTML = "";
    for (var x = quantity; x > 0; x--) {
        var option = document.createElement("option");
        option.value = x;
        option.innerText = x;
        confirmModal.querySelector("#selectedAmount").appendChild(option);
    }
});