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
} else {
	$suhu = $suhu2 = $tds = 'Data tidak tersedia';
	$formatted_date = 'Data tidak tersedia';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hydroponic Monitoring System</title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
	<main class="container mx-auto px-4 py-8">
		<div class="flex justify-center mb-8">
			<h2 class="font-semibold text-3xl text-green-700">Hydroponic Monitoring System</h2>
		</div>
		<div class="flex justify-center mb-10">
			<div class="border border-gray-300 bg-white shadow-lg rounded-lg p-8 w-full md:w-2/3 lg:w-1/2">
				<h2 class="text-2xl font-bold text-green-700 mb-4">Status Hidroponik</h2>
				<div class="text-lg font-semibold mb-2">Suhu: <span class="font-normal"><?php echo $suhu; ?> °C</span></div>
				<div class="text-lg font-semibold mb-2">Suhu 2: <span class="font-normal"><?php echo $suhu2; ?> °C</span></div>
				<div class="text-lg font-semibold mb-2">TDS: <span class="font-normal"><?php echo $tds; ?> ppm</span></div>
				<div class="text-lg font-semibold mb-2">Terakhir diupdate: <span class="font-normal"><?php echo $formatted_date; ?></span></div>
				<div class="text-xl font-semibold mt-6 bg-green-600 text-white rounded-lg px-4 py-2 text-center">
					Kondisi Baik
				</div>
				<div class="flex justify-center mt-5">
					<div class="text-center text-lg">
						<p class="text-gray-600">Pantau Perkembangan Tanaman <a href="logs.php" class="underline font-semibold">di sini</a></p>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>

</html>