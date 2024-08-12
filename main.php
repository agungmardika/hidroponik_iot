<?php
include 'database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT suhu, suhu2, tds, created_at FROM status ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
	$suhu = $row['suhu'];
	$suhu2 = $row['suhu2'];
	$tds = $row['tds'];
	$created_at = $row['created_at'];
	$formatted_date = date('j F H:i', strtotime($created_at));

	// Menentukan kondisi air berdasarkan nilai TDS
	if ($tds >= 560 && $tds <= 840) {
		$kondisi_air = "Kondisi Baik";
		$kondisi_warna = "bg-green-600";
		$icon = '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>';
	} else {
		$kondisi_air = "Kondisi Tidak Baik";
		$kondisi_warna = "bg-red-600";
		$icon = '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path></svg>';
	}
} else {
	$suhu = $suhu2 = $tds = 'Data tidak tersedia';
	$formatted_date = 'Data tidak tersedia';
	$kondisi_air = "Data tidak tersedia";
	$kondisi_warna = "bg-gray-500";
	$icon = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hydroponic Monitoring System Online</title>
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
			<div class="text-gray-500 order-1 flex justify-center w-full md:w-auto md:order-2 md:block" id="nav-content">
				<ul class="flex font-semibold justify-between">
					<li class="md:px-4 md:py-2  text-indigo-500"><a href="main.php">Beranda</a></li>
					<li class="md:px-4 md:py-2 hover:text-indigo-400"><a href="logs.php">Logs</a></li>
				</ul>
			</div>
			
		</div>
	</nav>
	<main class="container mx-auto px-4 py-8 bg-gray-100 h-screen">
		<div class="">
			<div class="flex justify-center mb-8">
				<h2 class="font-semibold text-3xl text-gray-900">Hydroponic Online Monitoring System </h2>
			</div>
			<div class="flex justify-center mb-10">
				<div class="border border-gray-300 bg-white shadow-lg rounded-lg p-8 w-full md:w-2/3 lg:w-1/2">
					<h2 class="text-2xl font-bold text-black mb-4">Status Hidroponik</h2>
					<div class="text-lg font-semibold mb-2">Suhu: <span class="font-normal"><?php echo $suhu; ?> °C</span></div>
					<div class="text-lg font-semibold mb-2">Suhu 2: <span class="font-normal"><?php echo $suhu2; ?> °C</span></div>
					<div class="text-lg font-semibold mb-2">TDS: <span class="font-normal"><?php echo $tds; ?> ppm</span></div>
					<div class="text-lg font-semibold mb-2">Terakhir diupdate: <span class="font-normal"><?php echo $formatted_date; ?></span></div>
					<div class="text-xl font-semibold mt-6 <?php echo $kondisi_warna; ?> text-white rounded-lg px-4 py-2 text-center flex items-center justify-center h-16">
						<div class="h-6 w-6 flex-shrink-0">
							<?php echo $icon; ?>
						</div>
						<span class="ml-2"><?php echo $kondisi_air; ?></span>
					</div>

					<div class="flex justify-center mt-5">
						<div class="text-center text-lg">
							<p class="text-gray-600">Pantau Perkembangan Tanaman <a href="logs.php" class="underline font-semibold">di sini</a></p>
						</div>
					</div>
				</div>
			</div>
			<div>
			</div>
	</main>
	</div>
</body>

</html>