<?php
include 'database.php';

// Membuat instance dari class Database dan mendapatkan koneksi
$database = new Database();
$db = $database->getConnection();

// Query untuk mengambil semua data
$query = "SELECT suhu, suhu2, tds, created_at FROM status ORDER BY created_at ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
}

// Mengonversi data ke format JSON
$json_data = json_encode($data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hydroponic Monitoring Logs</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-center mb-8">
            <h2 class="font-semibold text-3xl text-green-700">Hydroponic Monitoring Logs</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-center font-semibold text-lg mb-4">Suhu</h3>
                <canvas id="suhuChart"></canvas>
            </div>
            <div>
                <h3 class="text-center font-semibold text-lg mb-4">Suhu 2</h3>
                <canvas id="suhu2Chart"></canvas>
            </div>
            <div>
                <h3 class="text-center font-semibold text-lg mb-4">TDS</h3>
                <canvas id="tdsChart"></canvas>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const data = <?php echo $json_data; ?>;

            // Check if data is available
            if (data.length === 0) {
                console.error('No data available to display in chart.');
                return;
            }

            const labels = data.map(row => {
                const date = new Date(row.created_at);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');

                if (!isNaN(date)) {
                    return `${day}-${month}-${year} ${hours}:${minutes}`;
                }
                return 'Invalid Date';
            }).filter(label => label !== 'Invalid Date');

            const suhu = data.map(row => row.suhu);
            const suhu2 = data.map(row => row.suhu2);
            const tds = data.map(row => row.tds);

            function createChart(ctx, label, data, borderColor, backgroundColor) {
                return new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            borderColor: borderColor,
                            backgroundColor: backgroundColor,
                            fill: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                type: 'category',
                                title: {
                                    display: true,
                                    text: 'Waktu'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nilai'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            createChart(document.getElementById('suhuChart').getContext('2d'), 'Suhu', suhu, 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 0.2)');
            createChart(document.getElementById('suhu2Chart').getContext('2d'), 'Suhu 2', suhu2, 'rgba(54, 162, 235, 1)', 'rgba(54, 162, 235, 0.2)');
            createChart(document.getElementById('tdsChart').getContext('2d'), 'TDS', tds, 'rgba(75, 192, 192, 1)', 'rgba(75, 192, 192, 0.2)');
        });
    </script>
</body>

</html>