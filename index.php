<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pension Backlog Status</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        /* Container to hold the map and table side by side */
        #container {
            display: flex;
            height: 600px;
        }
        /* Map styling - occupies 70% width */
        #map {
            width: 70%;
            height: 100%;
        }
        /* Table styling - occupies 30% width */
        #tableContainer {
            width: 30%;
            padding: 10px;
            overflow-y: auto; /* Add scroll for long tables */
            border-left: 1px solid #ddd;
            background-color: #fff; /* Ensure the table background is white for readability */
        }
        #personnelTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        #personnelTable th, #personnelTable td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 14px; /* Adjust font size for better readability */
        }
        #personnelTable th {
            background-color: #f2f2f2;
            text-align: left;
        }
        /* Summary styling */
        #summary {
            margin-bottom: 20px;
            font-weight: bold;
        }
        /* Legend styling */
        #legend {
            background: white;
            line-height: 1.5;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            position: absolute; /* Position it relative to the map */
            bottom: 30px; /* Adjusted to not overlap with other elements */
            left: 10px; /* Adjust position to fit nicely */
            z-index: 1000; /* Make sure it's on top of the map */
        }
        #legend h4 {
            margin: 0 0 5px; /* Spacing for the title */
        }
        .legendColor {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body>

<h2>Pension Backlog Status</h2>

<!-- Export Button -->
<button id="exportBtn">Export Summary</button>

<!-- Container for map and table -->
<div id="container">
    <div id="map"></div>

    <!-- Table container -->
    <div id="tableContainer">
        <div id="summary">Select a district to view details.</div>
        <table id="personnelTable">
            <thead>
                <tr>
                    <th>Army No</th>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="personnelTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Legend for map colors -->
<div id="legend">
    <h4>Personnel Count Legend:</h4>
    <div><span class="legendColor" style="background: #800026;"></span> > 100</div>
    <div><span class="legendColor" style="background: #BD0026;"></span> 51 - 100</div>
    <div><span class="legendColor" style="background: #E31A1C;"></span> 21 - 50</div>
    <div><span class="legendColor" style="background: #FC4E2A;"></span> 11 - 20</div>
    <div><span class="legendColor" style="background: #FD8D3C;"></span> 6 - 10</div>
    <div><span class="legendColor" style="background: #FEB24C;"></span> 1 - 5</div>
    <div><span class="legendColor" style="background: #FFEDA0;"></span> 0</div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<!-- Fetch Polyfill for older browsers -->
<script src="https://unpkg.com/whatwg-fetch@3.6.2/dist/fetch.umd.js"></script>
<script>
// Define district coordinates
const districtCoordinates = {
    "Adjumani": { lat: 3.3892, lng: 31.0334 },
    "Agago": { lat: 2.9785, lng: 33.3022 },
    "Alebtong": { lat: 2.4637, lng: 33.2045 },
    "Amolatar": { lat: 1.7023, lng: 32.4919 },
    "Amudat": { lat: 2.3300, lng: 34.1455 },
    "Apac": { lat: 2.3980, lng: 32.5817 },
    "Arua": { lat: 3.0198, lng: 30.9120 },
    "Buliisa": { lat: 1.5170, lng: 31.3017 },
    "Buikwe": { lat: 0.5710, lng: 33.1374 },
    "Bundibugyo": { lat: 0.2035, lng: 30.0966 },
    "Bushenyi": { lat: -0.5206, lng: 30.2232 },
    "Busia": { lat: 0.4839, lng: 34.0103 },
    "Butaleja": { lat: 0.4870, lng: 33.7380 },
    "Butambala": { lat: -0.1282, lng: 31.7667 },
    "Dokolo": { lat: 1.7777, lng: 33.7221 },
    "Gulu": { lat: 2.7669, lng: 32.3055 },
    "Hoima": { lat: 1.4267, lng: 31.3445 },
    "Ibanda": { lat: -0.4872, lng: 30.2334 },
    "Iganga": { lat: 0.6130, lng: 33.4826 },
    "Isingiro": { lat: -0.6373, lng: 30.7675 },
    "Jinja": { lat: 0.4244, lng: 33.2042 },
    "Kaberamaido": { lat: 1.6288, lng: 33.2376 },
    "Kabale": { lat: -1.0595, lng: 29.9915 },
    "Kabarole": { lat: 0.5074, lng: 30.3088 },
    "Kagadi": { lat: -0.3677, lng: 30.3317 },
    "Kakumiro": { lat: -0.2614, lng: 30.6443 },
    "Kalangala": { lat: -0.1945, lng: 32.2287 },
    "Kampala": { lat: 0.3476, lng: 32.5825 },
    "Kamuli": { lat: 0.4670, lng: 33.1010 },
    "Kapchorwa": { lat: 1.3602, lng: 34.0263 },
    "Kapelebyong": { lat: 2.4435, lng: 33.5762 },
    "Karamoja": { lat: 2.3442, lng: 34.0070 },
    "Kasese": { lat: -0.2284, lng: 29.9201 },
    "Katakwi": { lat: 1.9188, lng: 34.1137 },
    "Kawempe": { lat: 0.3677, lng: 32.5433 },
    "Kayunga": { lat: 0.1341, lng: 32.8464 },
    "Kiboga": { lat: 0.5510, lng: 31.6956 },
    "Kibuku": { lat: 1.0780, lng: 33.5370 },
    "Kiryandongo": { lat: 2.0667, lng: 31.6361 },
    "Kisoro": { lat: -1.0826, lng: 29.6884 },
    "Kitgum": { lat: 3.2657, lng: 32.8771 },
    "Kumi": { lat: 1.1687, lng: 33.9540 },
    "Kween": { lat: 1.3052, lng: 34.0797 },
    "Kyankwanzi": { lat: -0.0374, lng: 31.4961 },
    "Kyamuhunga": { lat: -0.5636, lng: 30.1741 },
    "Lira": { lat: 2.2431, lng: 32.9003 },
    "Luwero": { lat: 0.8770, lng: 32.5731 },
    "Lyantonde": { lat: -0.5746, lng: 30.1296 },
    "Mbarara": { lat: -0.6060, lng: 30.6542 },
    "Masaka": { lat: -0.3126, lng: 31.7280 },
    "Masindi": { lat: 1.6790, lng: 31.7175 },
    "Mayuge": { lat: 0.4161, lng: 33.7755 },
    "Mbale": { lat: 1.0822, lng: 34.1750 },
    "Mityana": { lat: 0.3520, lng: 31.6687 },
    "Moroto": { lat: 2.7360, lng: 34.6371 },
    "Mubende": { lat: -0.4698, lng: 31.6102 },
    "Mukono": { lat: 0.3901, lng: 32.7710 },
    "Nakasongola": { lat: 1.0448, lng: 32.5740 },
    "Nakaseke": { lat: 0.7861, lng: 31.6228 },
    "Namayingo": { lat: 0.3880, lng: 33.6542 },
    "Namusindwa": { lat: 0.9985, lng: 34.1097 },
    "Napak": { lat: 2.2955, lng: 34.2673 },
    "Nebbi": { lat: 2.8357, lng: 30.0248 },
    "Ngora": { lat: 1.4444, lng: 33.8692 },
    "Nwoya": { lat: 2.6440, lng: 31.5225 },
    "Oyam": { lat: 2.3928, lng: 32.8282 },
    "Pader": { lat: 3.1344, lng: 33.1320 },
    "Rakai": { lat: -0.4524, lng: 30.0656 },
    "Rubirizi": { lat: -0.2176, lng: 30.1870 },
    "Rukiga": { lat: -1.0192, lng: 29.8557 },
    "Rukungiri": { lat: -0.5697, lng: 29.9421 },
    "Sembabule": { lat: -0.2618, lng: 31.1543 },
    "Serere": { lat: 1.2708, lng: 33.7991 },
    "Soroti": { lat: 1.6957, lng: 33.6090 },
    "Tororo": { lat: 0.6872, lng: 34.1635 },
    "Wakiso": { lat: 0.1531, lng: 32.4953 },
    "Yumbe": { lat: 3.4301, lng: 31.0545 },
};

// Function to convert strings to title case
function toTitleCase(str) {
    return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

// Function to get color based on personnel count
function getColor(count) {
    return count > 100 ? '#800026' : count > 50 ? '#BD0026' :
           count > 20  ? '#E31A1C' : count > 10  ? '#FC4E2A' :
           count > 5   ? '#FD8D3C' : count > 0   ? '#FEB24C' : '#FFEDA0';
}

// Initialize the map
const map = L.map('map').setView([1.3733, 32.2903], 7); // Centered on Uganda

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Global variable to hold the district data
let currentDistrictData = {};
let districtCounts = {}; // Initialize districtCounts

// Fetch personnel data from PHP script
fetch('get_personnel.php')
    .then(response => response.json())
    .then(data => {
        // Count how many people are in each district and categorize them
        data.forEach(person => {
            const district = toTitleCase(person.district);
            const category = person.category;
            const status = person.status;

            if (districtCounts[district]) {
                districtCounts[district].count += 1;
                districtCounts[district].people.push(person);
                districtCounts[district].categories[category] = (districtCounts[district].categories[category] || 0) + 1;
                districtCounts[district].statuses[status] = (districtCounts[district].statuses[status] || 0) + 1;
            } else {
                districtCounts[district] = {
                    count: 1,
                    people: [person],
                    categories: { [category]: 1 },
                    statuses: { [status]: 1 }
                };
            }
        });

        // Loop through district counts and create circles
        for (const district in districtCounts) {
            const districtData = districtCounts[district];
            const coords = districtCoordinates[district];

            if (coords) {
                const color = getColor(districtData.count);

                // Create a circle without border and add to map
                const circle = L.circle([coords.lat, coords.lng], {
                    fillColor: color,
                    color: color,        // Ensure border color is visible
                    fillOpacity: 0.7,
                    radius: 10000,        // Adjusted radius for visibility
                    stroke: true,         // Ensure a stroke (border) is drawn
                    weight: 1             // Set border weight (thickness)
                }).addTo(map);

                // Add event listener for circle click
                circle.on('click', function() {
                    updateTable(district, districtData);
                    currentDistrictData = districtData; // Store the current district data for export
                });
            } else {
                console.warn(`Coordinates not found for district: ${district}`);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching personnel data:', error);
    });

// Function to update the table and summary when a district is clicked
function updateTable(district, districtData) {
    // Update summary
    const summaryDiv = document.getElementById('summary');
    summaryDiv.innerHTML = `
        <strong>${district} Summary:</strong><br>
        Total Personnel: ${districtData.count}<br>
        (Paid): ${districtData.statuses.PAID || 0}<br>
        (Unclaimed): ${districtData.statuses.UNCLAIMED || 0}<br>
        (Incomplete): ${districtData.statuses.INCOMPLETE || 0}<br>
    `;

    const tableBody = document.getElementById('personnelTableBody');
    tableBody.innerHTML = ''; // Clear previous table data

    districtData.people.forEach(person => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${person.army_no}</td>
            <td>${person.name}</td>
            <td>${person.rank}</td>
            <td>${person.category}</td>
            <td>${person.status}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Function to export current district data to CSV
function exportToCSV() {
    const data = currentDistrictData.people;

    if (data.length === 0) {
        alert('No personnel data available for export.');
        return;
    }

    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Army No,Name,Rank,Category,Status\n"; // Header row

    data.forEach(person => {
        const rowData = `${person.army_no},${person.name},${person.rank},${person.category},${person.status}`;
        csvContent += rowData + "\n"; // Add data row
    });

    // Create a link to download the CSV file
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${currentDistrictData.count}_personnel_data.csv`);
    document.body.appendChild(link); // Required for Firefox

    link.click(); // Simulate click to download
    document.body.removeChild(link); // Clean up
}

// Attach event listener to the export button
document.getElementById('exportBtn').addEventListener('click', exportToCSV);
</script>

</body>
</html>
