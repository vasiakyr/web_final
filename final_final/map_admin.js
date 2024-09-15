var map = null;
var draggableMarker = null;
var confirmationModal = document.getElementById('confirmationModal');
var confirmYesButton = document.getElementById('confirmYes');
var confirmNoButton = document.getElementById('confirmNo');
var originalMarkerPosition = null;

var requestsTakenMarkers = [];
var requestsPendingMarkers = [];
var offersTakenMarkers = [];
var offersPendingMarkers = [];
var vehicleMarkers = [];

var currentFilter = null; // Μεταβλητή για το επιλεγμένο φίλτρο

// Συνάρτηση για τον marker της βάσης
function createDraggableMarker() {
    if (!draggableMarker) {
        var blackMarkerIcon = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-black.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            tooltipAnchor: [16, -28],
        });

        // Δημιουργία της βάσης στο κέντρο της Πάτρας
        draggableMarker = L.marker([38.2466, 21.7346], { draggable: true, icon: blackMarkerIcon }).addTo(map);
        originalMarkerPosition = draggableMarker.getLatLng();

        // Event listener for marker dragend
        draggableMarker.on('dragend', function (event) {
            var marker = event.target;
            var position = marker.getLatLng();

            // Show confirmation modal
            confirmationModal.style.display = 'block';

            confirmYesButton.onclick = function () {
                L.popup()
                    .setLatLng(position)
                    //Μήνυμα επιβεβαίωσης μετά απο αλλαγή τοποθεσίας της βάσης
                    .setContent('Base moved!<br> New coordinates: ' + position.lat.toFixed(4) + ', ' + position.lng.toFixed(4)) 
                    .openOn(map);

                originalMarkerPosition = position;
                confirmationModal.style.display = 'none';
            }
            //Μεταφορά του marker στην αρχική του θέση σε περίπτωση άρνησης
            confirmNoButton.onclick = function () {
                marker.setLatLng(originalMarkerPosition);
                confirmationModal.style.display = 'none';
            }
        });
    }
}


document.addEventListener('DOMContentLoaded', function () {
    map = L.map('map').setView([38.2466, 21.7346], 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    createDraggableMarker();

    //Δημιουργία marker για κάθε διαφορετικό απο τα ζητούμενα
    var marker1Icon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    var marker2Icon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    var marker3Icon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    var marker4Icon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    var marker5Icon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });

    requestsPendingMarkers.push(L.marker([38.2533, 21.7354], { icon: marker1Icon }).addTo(map));
    vehicleMarkers.push(L.marker([38.2498, 21.7485], { icon: marker2Icon }).addTo(map));
    offersPendingMarkers.push(L.marker([38.2456, 21.7321], { icon: marker3Icon }).addTo(map));
    requestsTakenMarkers.push(L.marker([38.2432, 21.7501], { icon: marker4Icon }).addTo(map));
    offersTakenMarkers.push(L.marker([38.2439, 21.7400], { icon: marker5Icon }).addTo(map));

   
    // Συνάρτηση για την λειτουργία των φίλτρων
    window.showMarkers = function (selectedMarkers) {
        if (currentFilter === selectedMarkers) {
            showAllMarkers(); // Εάν επιλεχθεί το ίδιο φίλτρο εμφάνισε ξανά όλους τους markers
            currentFilter = null; // Reset της μεταβλητής
        } else {
            hideAllMarkers(); // Σβήσε όλους τους markers αρχικά
            selectedMarkers.forEach(marker => {
                marker.addTo(map); // Δείξε μόνο τους επιλεγμένους
            });

            currentFilter = selectedMarkers;

            // Δείχνε πάντα τον marker της βάσης
            if (!map.hasLayer(draggableMarker)) {
                draggableMarker.addTo(map);
            }
        }
    }

    // Συνάρτηση για να σβήσει όλους τους markers
    function hideAllMarkers() {
        [...requestsTakenMarkers, ...requestsPendingMarkers, ...offersTakenMarkers, ...offersPendingMarkers, ...vehicleMarkers].forEach(marker => {
            if (map.hasLayer(marker)) {
                map.removeLayer(marker);
            }
        });
    }

    // Συνάρτηση για να εμφανίσει όλους τους markers
    function showAllMarkers() {
        [...requestsTakenMarkers, ...requestsPendingMarkers, ...offersTakenMarkers, ...offersPendingMarkers, ...vehicleMarkers].forEach(marker => {
            marker.addTo(map);
        });

        // Επιβεβαίωσε ότι ο marker της βάσης είναι ορατός
        if (!map.hasLayer(draggableMarker)) {
            draggableMarker.addTo(map);
        }
    }

  
});