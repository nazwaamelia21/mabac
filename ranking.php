<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: index.php"); exit; }
include 'db.php';
function esc($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
$rows=[];
if(isset($koneksi)){
 $q=@mysqli_query($koneksi,"SELECT * FROM tbl_alternatif");
 if($q) while($r=mysqli_fetch_assoc($q)) $rows[]=$r;
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hasil Ranking</title><style>
body{font-family:Arial;background:#111;color:#eee;margin:0}.wrap{max-width:1100px;margin:30px auto;padding:20px}.card{background:#1b1b1b;border:1px solid #333;border-radius:14px;padding:20px}table{width:100%;border-collapse:collapse}th,td{padding:11px;border-bottom:1px solid #333;text-align:left}
</style></head><body><div class="wrap"><div class="card"><h1>Hasil Perankingan</h1>
<p>Halaman khusus untuk menampilkan hasil ranking. Perhitungan utama tetap berada pada halaman Perhitungan MABAC.</p>
<?php if(!$rows): ?><p>Belum ada data atlet.</p><?php else: ?>
<table><tr><th>No.</th><th>Alternatif / Atlet</th><th>C1</th><th>C2</th><th>C3</th><th>C4</th><th>C5</th></tr>
<?php foreach($rows as $i=>$r): $nama=$r['nama']??$r['alternatif']??$r['Nama']??('A'.($i+1)); ?>
<tr><td><?=($i+1)?></td><td><?=esc($nama)?></td>
<td><?=esc($r['C1']??$r['c1']??'')?></td><td><?=esc($r['C2']??$r['c2']??'')?></td><td><?=esc($r['C3']??$r['c3']??'')?></td><td><?=esc($r['C4']??$r['c4']??'')?></td><td><?=esc($r['C5']??$r['c5']??'')?></td></tr>
<?php endforeach;?></table><?php endif;?></div></div></body></html>