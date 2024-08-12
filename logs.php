<?php
include 'database.php';
$database = new Database();
$db = $database->getConnection();

$query = "SELECT suhu, suhu2, tds, created_at FROM status ORDER BY created_at ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
}

$json_data = json_encode($data);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs Hidroponik</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="">
    <nav class="bg-gray-200 shadow shadow-gray-300 w-full px-8 md:px-auto">
        <div class="md:h-16 h-28 mx-auto md:px-4 container flex items-center justify-between flex-wrap md:flex-nowrap">
            <div class="text-indigo-500 md:order-1">
                <img src="logo.png" class="w-28 h-24">
            </div>
            <div class="block md:hidden">
                <button id="nav-toggle" class="flex items-center px-3 py-2 border rounded text-gray-600 border-gray-600">
                    <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <title>Menu</title>
                        <path d="M0 3h20v2H0zM0 7h20v2H0zM0 11h20v2H0z" />
                    </svg>
                </button>
            </div>
            <div class="text-gray-500 order-3 w-full md:w-auto md:order-2 hidden md:block" id="nav-content">
                <ul class="flex font-semibold justify-between">
                    <li class="md:px-4 md:py-2 hover:text-indigo-400"><a href="main.php">Beranda</a></li>
                    <li class="md:px-4 md:py-2 text-indigo-500"><a href="#">Logs</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8 bg-gray-100">
        <div class="bg-white  p-10 rounded-lg">
            <div class="text-center mb-8">
                <h2 class="font-semibold text-3xl text-gray-900">Logs Hidroponik</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mx-auto">
                <div>
                    <h3 class="text-center font-semibold text-lg mb-4">Suhu</h3>
                    <canvas id="suhuChart"></canvas>
                </div>
                <div>
                    <h3 class="text-center font-semibold text-lg mb-4">Suhu 2</h3>
                    <canvas id="suhu2Chart"></canvas>
                </div>
            </div>
            <div class="flex justify-center mt-8">
                <div class="w-full md:w-1/2">
                    <h3 class="text-center font-semibold text-lg mb-4">TDS</h3>
                    <canvas id="tdsChart"></canvas>
                </div>
            </div>
            <div class="text-center mt-12">
                <a href="main.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const data = <?php echo $json_data; ?>;

            if (data.length === 0) {
                console.error('No data available to display in chart.');
                return;
            }

            const labels = data.map(row => {
                const date = new Date(row.created_at);
                if (isNaN(date)) {
                    console.warn('Invalid date encountered:', row.created_at);
                    return 'Invalid Date';
                }
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}-${month}-${year} ${hours}:${minutes}`;
            }).filter(label => label !== 'Invalid Date');

            console.log('Formatted labels:', labels);

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

        document.getElementById('nav-toggle').addEventListener('click', function() {
            const navContent = document.getElementById('nav-content');
            if (navContent.classList.contains('hidden')) {
                navContent.classList.remove('hidden');
            } else {
                navContent.classList.add('hidden');
            }
        });
    </script>
</body>

</html>