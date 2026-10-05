<?php
$koneksi = mysqli_connect("localhost", "root", "", "mabac");
if (!$koneksi) { die("Koneksi gagal: " . mysqli_connect_error()); }
mysqli_set_charset($koneksi, 'utf8mb4');
?>
