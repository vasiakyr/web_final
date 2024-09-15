// Η συνάρτηση fetchJSON παίρνει ένα αρχείο JSON και επιστρέφει το περιεχόμενό του ως αντικείμενο
async function fetchJSON(file) {
    const response = await fetch(file);
    return response.json();
}

// Η συνάρτηση populateTable γεμίζει τον πίνακα με προϊόντα και κατηγορίες
function populateTable(categories, products) {
    const table = document.getElementById("inventoryTable").getElementsByTagName("tbody")[0]; // Παίρνει το σώμα του πίνακα
    products.forEach((product, index) => { // Για κάθε προϊόν
        const category = categories.find(cat => cat.id === product.category); // Βρίσκει την κατηγορία του προϊόντος
        if (category) { // Αν βρέθηκε κατηγορία
            const row = table.insertRow(); // Προσθέτει νέα γραμμή στον πίνακα
            row.insertCell(0).innerText = category.category_name; // Καταχωρεί το όνομα της κατηγορίας
            row.insertCell(1).innerText = product.name; // Καταχωρεί το όνομα του προϊόντος
            const quantityCell = row.insertCell(2); // Καταχωρεί την ποσότητα στην αποθήκη
            quantityCell.innerText = product.quantityWarehouse; 
            quantityCell.id = `quantity-${index}`; // Ορίζει μοναδικό id για κάθε κελί ποσότητας
            row.insertCell(3).innerText = "5"; // Καταχωρεί μια υπόθεση για την ποσότητα που μεταφέρεται

            // Προσθέτει κουμπί επεξεργασίας ποσότητας στο τελευταίο κελί της γραμμής
            const editCell = row.insertCell(4);
            editCell.innerHTML = `<span class="edit-button" onclick="editQuantity(${index}, ${product.quantityWarehouse})">Edit</span>`;
        }
    });
}

// Η συνάρτηση populateFilter γεμίζει τη λίστα επιλογής κατηγοριών με δεδομένα από το JSON
function populateFilter(categories) {
    const select = document.getElementById("categoryFilter");
    categories.forEach(category => {
        const option = document.createElement("option"); // Δημιουργεί νέο στοιχείο <option>
        option.value = category.category_name; // Ορίζει την τιμή της επιλογής
        option.innerText = category.category_name; // Ορίζει το όνομα που εμφανίζεται
        select.appendChild(option); // Προσθέτει την επιλογή στη λίστα
    });
}

// Η συνάρτηση initialize φορτώνει τα δεδομένα και καλεί τις κατάλληλες συναρτήσεις για τη δημιουργία του πίνακα και του φίλτρου
async function initialize() {
    const categories = await fetchJSON('categories.json'); // Φορτώνει τις κατηγορίες
    const products = await fetchJSON('product.json'); // Φορτώνει τα προϊόντα
    populateTable(categories, products); // Γεμίζει τον πίνακα
    populateFilter(categories); // Γεμίζει το φίλτρο
}

// Η συνάρτηση filterTable φιλτράρει τον πίνακα με βάση την επιλεγμένη κατηγορία
function filterTable() {
    const select = document.getElementById("categoryFilter"); // Παίρνει το φίλτρο επιλογής
    const selectedCategories = Array.from(select.selectedOptions).map(option => option.value.toUpperCase()); // Παίρνει τις επιλεγμένες κατηγορίες
    const table = document.getElementById("inventoryTable");
    const tr = table.getElementsByTagName("tr");

    // Διατρέχει τις γραμμές του πίνακα και εμφανίζει μόνο όσες ταιριάζουν με τις επιλεγμένες κατηγορίες
    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (selectedCategories.includes("ALL") || selectedCategories.includes(txtValue.toUpperCase())) {
                tr[i].style.display = ""; // Εμφανίζει τη γραμμή
            } else {
                tr[i].style.display = "none"; // Κρύβει τη γραμμή
            }
        }
    }
}

// Η συνάρτηση editQuantity δίνει τη δυνατότητα να επεξεργαστούμε την ποσότητα ενός προϊόντος
function editQuantity(index, currentQuantity) {
    const quantityCell = document.getElementById(`quantity-${index}`); // Παίρνει το κελί ποσότητας
    const previousContent = quantityCell.innerHTML; // Αποθηκεύει το αρχικό περιεχόμενο

    // Δημιουργεί πεδίο εισαγωγής για να επεξεργαστούμε την ποσότητα
    quantityCell.innerHTML = `
        <input type="number" id="quantity-input-${index}" value="${currentQuantity}" min="0" style="width: 80px;">
        <button onclick="saveQuantity(${index}, '${previousContent}')">Save</button>
        <button onclick="cancelEdit(${index}, '${previousContent}')">Cancel</button>
    `;
}

// Η συνάρτηση saveQuantity αποθηκεύει τη νέα ποσότητα
function saveQuantity(index, previousContent) {
    const inputField = document.getElementById(`quantity-input-${index}`); // Παίρνει το πεδίο εισαγωγής
    const newQuantity = inputField.value;

    // Ελέγχει αν η νέα ποσότητα είναι έγκυρη και μη αρνητική
    if (newQuantity >= 0) {  
        document.getElementById(`quantity-${index}`).innerText = newQuantity;  // Ενημερώνει το κελί με τη νέα ποσότητα
        console.log(`Quantity for item ${index} updated to: ${newQuantity}`); // Καταγράφει την αλλαγή
    } else {
        alert("Please enter a valid non-negative quantity."); // Εμφανίζει μήνυμα αν η ποσότητα είναι αρνητική
    }
}

// Η συνάρτηση cancelEdit επαναφέρει την αρχική τιμή αν ο χρήστης ακυρώσει την επεξεργασία
function cancelEdit(index, previousContent) {
    document.getElementById(`quantity-${index}`).innerText = previousContent;  // Επαναφέρει την αρχική ποσότητα
}

// Καλεί την initialize όταν φορτωθεί το DOM
document.addEventListener('DOMContentLoaded', initialize);
