      // Πρόβαλε τον χάρτη για την πόλη Πάτρα
      const map = L.map('map').setView([38.246639, 21.734573], 14);

      // Πρόσθεσε ενα tile layer
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19
      }).addTo(map);

      // Τοποθεσία Βάσης
      const baseLocation = [38.246639, 21.734573];
      const maxDistance = 5; // Μέγιστη απόσταση (σε χλμ) των tasks απο την βάση

     // Πρόσθεσε εναν marker για την βάση 
const baseMarker = L.marker(baseLocation, {
  icon: L.icon({
      iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-black.png', // Μαύρος marker
      iconSize: [25, 41],
      iconAnchor: [12, 41]
  })
}).addTo(map).bindPopup("Rescue Base").openPopup();
      // Συνάρτηση που ελέγχει αν ενα task έχει την επιτρεπτόμενη απόσταση απο την βάση 
      function isWithinDistance(lat, lng, baseLat, baseLng, maxDistance) {
          const distance = map.distance([lat, lng], [baseLat, baseLng]) / 1000; // Μετέτρεψε την απόσταση σε χιλιόμετρα
          return distance <= maxDistance;
      }

    // Παραδείγματα προσφορών και αιτημάτων
  const requests = [
  {id: 1, lat: 38.25, lng: 21.74, name: "John Doe", phone: "123-456-7890", date: "2024-08-22", type: "Food", quantity: 10, takenOver: false},
  {id: 2, lat: 38.245, lng: 21.73, name: "Jane Smith", phone: "987-654-3210", date: "2024-08-22", type: "Water", quantity: 20, takenOver: false},
  {id: 3, lat: 38.246, lng: 21.75, name: "Peter Parker", phone: "763-512-3670", date: "2024-08-26", type: "Toys", quantity: 5, takenOver: false}
  ];

  const offers = [
  {id: 1, lat: 38.24, lng: 21.73, name: "Alice Brown", phone: "555-555-5555", date: "2024-08-21", type: "Blankets", quantity: 15, takenOver: false},
  {id: 2, lat: 38.235, lng: 21.735, name: "Bob White", phone: "444-444-4444", date: "2024-08-21", type: "Clothing", quantity: 30, takenOver: false},
  {id: 3, lat: 38.236, lng: 21.73, name: "Michael Perry", phone: "901-765-5313", date: "2024-09-04", type: "Blankets", quantity: 3, takenOver: false}
  ];


      const markers = {};
      let currentTaskLines = []; // Για την αποθήκευση γραμμών που συνδέουν το κάθε task με τον rescuer που το έχει αναλάβει
      let currentTask = null; 

      // Πρόσθεσε markers για τα αιτήματα
      requests.forEach(req => {
          if (isWithinDistance(req.lat, req.lng, baseLocation[0], baseLocation[1], maxDistance)) {
              const marker = L.marker([req.lat, req.lng], {
                  icon: L.icon({
                      iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-yellow.png',
                      iconSize: [25, 41],
                      iconAnchor: [12, 41]
                  })
              }).addTo(map)
              .bindPopup(`
                  <strong>Request</strong><br>
                  Name: ${req.name}<br>
                  Phone: ${req.phone}<br>
                  Date: ${req.date}<br>
                  Type: ${req.type}<br>
                  Quantity: ${req.quantity}<br>
                  <button onclick="handleTask(${req.id}, 'request', [${req.lat}, ${req.lng}])">Take Over Request</button>
              `);
              markers[`request-${req.id}`] = marker;
          }
      });

      // Πρόσθεσε markers για τις προσφορές
      offers.forEach(offer => {
          if (isWithinDistance(offer.lat, offer.lng, baseLocation[0], baseLocation[1], maxDistance)) {
              const marker = L.marker([offer.lat, offer.lng], {
                  icon: L.icon({
                      iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                      iconSize: [25, 41],
                      iconAnchor: [12, 41]
                  })
              }).addTo(map)
              .bindPopup(`
                  <strong>Offer</strong><br>
                  Name: ${offer.name}<br>
                  Phone: ${offer.phone}<br>
                  Date: ${offer.date}<br>
                  Type: ${offer.type}<br>
                  Quantity: ${offer.quantity}<br>
                  <button onclick="handleTask(${offer.id}, 'offer', [${offer.lat}, ${offer.lng}])">Take Over Offer</button>
              `);
              markers[`offer-${offer.id}`] = marker;
          }
      });

      // Παράδειγμα ενος rescuer
      const rescuerMarker = L.marker([38.246639, 21.734573], {
          draggable: true,
          icon: L.icon({
              iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
              iconSize: [25, 41],
              iconAnchor: [12, 41]
          })
      }).addTo(map)
          .bindPopup('You are here')
          .openPopup();

          
// Συνάρτηση που δυναμικά ενημερώνει το pop-up balloon
function updatePopup(marker, taskId, taskType) {
  let taskDetails;
  
  // Βρές τo task βάση του είδους του
  if (taskType === 'request') {
      taskDetails = requests.find(req => req.id === taskId);
  } else if (taskType === 'offer') {
      taskDetails = offers.find(offer => offer.id === taskId);
  }

  // Έλεγξε αν το task έχει αναληφθεί και εμφάνισε το κατάλληλο κουμπί στο pop-up
  let buttonText = taskDetails.takenOver ? 'Show Task' : 'Take Over Task';
  
  marker.setPopupContent(`
      <strong>${taskType.charAt(0).toUpperCase() + taskType.slice(1)}</strong><br>
      Name: ${taskDetails.name}<br>
      Phone: ${taskDetails.phone}<br>
      Date: ${taskDetails.date}<br>
      Type: ${taskDetails.type}<br>
      Quantity: ${taskDetails.quantity}<br>
      <button onclick="handleTask(${taskId}, '${taskType}', [${taskDetails.lat}, ${taskDetails.lng}])">${buttonText}</button>
  `);
}
function getTakenOverTasksCount() {
  let count = 0;
  requests.forEach(req => {
      if (req.takenOver) count++;
  });
  offers.forEach(offer => {
      if (offer.takenOver) count++;
  });
  return count;
}

//Συνάρητησ με διάφορες λειτουργίες
function handleTask(taskId, taskType, taskCoords) {

  const takenOverCount = getTakenOverTasksCount();
  if (takenOverCount >= 4) {
      alert("You can only take over a maximum of  tasks at a time.");
      return;  // Εμπόδισε να πάρει παραπάνω απο 4 tasks
  }

  // Βρές τις πληροφορίες του task βάσει του τύπου του
  let taskDetails;
  if (taskType === 'request') {
      taskDetails = requests.find(req => req.id === taskId);
  } else if (taskType === 'offer') {
      taskDetails = offers.find(offer => offer.id === taskId);
  }

  // Εάν εως τώρα δεν το έχει αναλάβει κανείς άλλαξε την κατάσταση του
  if (!taskDetails.takenOver) {
      taskDetails.takenOver = true;
  }

  //Ενήμερωσε το pop-up του marker
  updatePopup(markers[`${taskType}-${taskId}`], taskId, taskType);

  // Οι πληροφορίες που εμφανίζονται στο πλαίσιο κάτω απο τον χάρτη
  const taskInfoBox = document.getElementById('task-info-box');
  taskInfoBox.innerHTML = `
      <h3>Task Information</h3>
      <p><strong>Task Type:</strong> ${taskType.charAt(0).toUpperCase() + taskType.slice(1)}</p>
      <p><strong>Name:</strong> ${taskDetails.name}</p>
      <p><strong>Phone:</strong> ${taskDetails.phone}</p>
      <p><strong>Date:</strong> ${taskDetails.date}</p>
      <p><strong>Type:</strong> ${taskDetails.type}</p>
      <p><strong>Quantity:</strong> ${taskDetails.quantity}</p>
      <button id="cancel-task-btn">Cancel Task</button>
      <button id="complete-task-btn">Complete Task</button>
  `;
  taskInfoBox.style.display = 'block';  // Show the task info box



  // Σβήσε (εαν υπάρχουν) τις υπάρχουσες γραμμές
  currentTaskLines.forEach(line => map.removeLayer(line));
  currentTaskLines = [];

  // Άλλαξε το χρώμα του marker αν το έχει αναλάβει κανείς , ανάλογα και τι task είναι
  if (taskType === 'request') {
      markers[`request-${taskId}`].setIcon(L.icon({
          iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-orange.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41]
      }));
  } else if (taskType === 'offer') {
      markers[`offer-${taskId}`].setIcon(L.icon({
          iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41]
      }));
  }

  //Εμφάνισε μια γραμμή μεταξύ του rescuer και του task που έχει αναλάβει
  const line = L.polyline([rescuerMarker.getLatLng(), taskCoords], {color: 'black'}).addTo(map);
  currentTaskLines.push(line);

  // Ενήμερωνε συνέχως την γραμμή όσο μετακινείται ο rescuer
  rescuerMarker.on('move', () => {
      line.setLatLngs([rescuerMarker.getLatLng(), taskCoords]);
  });

  // Λειτουργίες με το κουμπί Cancel Task
  document.getElementById('cancel-task-btn').onclick = () => {
      taskInfoBox.style.display = 'none';  // Κρύψε το πλαίσιο με τις πληροφορίες
      // Επανέφερε το χρώμα του marker οπως ηταν στην αρχή
      if (taskType === 'request') {
          markers[`request-${taskId}`].setIcon(L.icon({
              iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-yellow.png',
              iconSize: [25, 41],
              iconAnchor: [12, 41]
          }));
      } else if (taskType === 'offer') {
          markers[`offer-${taskId}`].setIcon(L.icon({
              iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
              iconSize: [25, 41],
              iconAnchor: [12, 41]
          }));
      }
       // Άλλαξε την κατάσταση του task
       taskDetails.takenOver = false;

  
  // Άλλαξε το κουμπί στο pop-up
  updatePopup(markers[`${taskType}-${taskId}`], taskId, taskType);
      currentTaskLines.forEach(line => map.removeLayer(line));  // Αφαίρεσε την γραμμή
  };



// Λειτουργίες με το κουμπί Complete Task
document.getElementById('complete-task-btn').onclick = () => {
    const rescuerLatLng = rescuerMarker.getLatLng();
    const taskLatLng = L.latLng(taskCoords[0], taskCoords[1]);

    // Έλεγξε την απόσταση μεταξύ του rescuer και του task που έχει αναλάβει
    const distance = rescuerLatLng.distanceTo(taskLatLng);  // Απόσταση σε μέτρα

    if (distance <= 50) {
        alert(`Task ${taskType} with ID ${taskId} completed!`);
        // Κρύψε το πλαίσιο με τις πληροφορίες του task
        taskInfoBox.style.display = 'none';

        // Αφαίρεσε το task και την γραμμή
        if (taskType === 'request') {
            map.removeLayer(markers[`request-${taskId}`]);
            const taskIndex = requests.findIndex(req => req.id === taskId);
            requests[taskIndex].takenOver = false; // Απελευθέρωσε το task
        } else if (taskType === 'offer') {
            map.removeLayer(markers[`offer-${taskId}`]);
            const taskIndex = offers.findIndex(offer => offer.id === taskId);
            offers[taskIndex].takenOver = false; // Απελευθέρωσε το task
        }

        currentTaskLines.forEach(line => map.removeLayer(line));  // Αφαίρεσε την γραμμή

        // Ενημέρωσε δυναμικά τον αριθμό των αναληφθέντων tasks
        const takenOverCount = getTakenOverTasksCount();
        console.log(`Updated takenOverCount: ${takenOverCount}`);
        
    } else {
        alert(`You are too far away from the task! You must be within 50 meters to complete it.`);
    }
};
}

// Εμπόδισε τον rescuer να μεταφερθεί σε απόσταση μεγαλύτερη των 5χλμ απο την βάση
rescuerMarker.on('moveend', function (e) {
    const rescuerLatLng = rescuerMarker.getLatLng();
    const baseLatLng = L.latLng(baseLocation[0], baseLocation[1]);
    const maxAllowedDistancefromBase = 5000; 
    // Έλεγξε την απόσταση μεταξύ του rescuer και της βάσης
    const distance = rescuerLatLng.distanceTo(baseLatLng);  // Απόσταση σε μέτρα

    if (distance > maxAllowedDistancefromBase) {  // Έλεγχος για απόσταση μεγαλύτερη των 5χλμ (5000 μέτρα)
        alert("You cannot move further than 5 km from the base!");
        rescuerMarker.setLatLng(baseLatLng); // Επανέφερε τον marker στην βάση
    }
});

// Έλεγχος απόστασης του rescuer απο την βάση προτού πάει στην σελίδα το car 
 document.getElementById('car-link').addEventListener('click', (event) => {
          const rescuerLatLng = rescuerMarker.getLatLng();
          const distanceToBase = map.distance(rescuerLatLng, baseLocation); // in meters
          const maxAllowedDistance = 100; // Μέγιστη επιτρεπτή απόσταση απο την βάση

          if (distanceToBase > maxAllowedDistance) {
              event.preventDefault();
              alert("You are too far from the base! Move closer to the base to access the vehicle.");
          }
      });

     
