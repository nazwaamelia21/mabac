<?php
session_start();if(!isset($_SESSION['username'])){header('Location:index.php');exit;}require_once 'db.php';require_once 'model.php';
function esc($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
$codes=['C1','C2','C3','C4','C5'];$cm=get_criteria_model();$set=get_bmw_settings();$best=$set['best'];$worst=$set['worst'];$bo=$set['bo'];$ow=$set['ow'];$weights=$set['weights'];$xi=0;$saved=false;
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['calculate'])){$best=in_array($_POST['best']??'', $codes,true)?$_POST['best']:$best;$worst=in_array($_POST['worst']??'', $codes,true)?$_POST['worst']:$worst;if($best===$worst){$worst=$best==='C1'?'C2':'C1';}foreach($codes as $c){$bo[$c]=max(1,min(9,(int)($_POST['bo'][$c]??3)));$ow[$c]=max(1,min(9,(int)($_POST['ow'][$c]??3)));}$score=array_fill_keys($codes,1.0);$score[$best]=1.0;for($iter=0;$iter<200;$iter++){foreach($codes as $c){if($c===$best)continue;$score[$c]=($score[$best]/$bo[$c]+$ow[$c]*($score[$worst]??1))/2;}$sum=array_sum($score);if($sum>0)foreach($score as $c=>$v)$score[$c]=$v/$sum;}$weights=$score;$sum=array_sum($weights);if($sum>0)foreach($weights as $c=>$v)$weights[$c]=$v/$sum;foreach($codes as $c){$e1=abs($weights[$best]-$bo[$c]*$weights[$c]);$e2=abs($weights[$c]-$ow[$c]*$weights[$worst]);$xi=max($xi,$e1,$e2);}save_bmw_settings($best,$worst,$bo,$ow,$weights);$saved=true;}
?>
<?php $C=[];foreach($codes as $c)$C[]=['kode'=>$c,'nama'=>$cm[$c]['nama'],'jenis'=>$cm[$c]['jenis']]; ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>BMW - Best Worst Method</title>
<style>
body{font-family:Arial,sans-serif;background:#111;color:#eee;margin:0}.wrap{max-width:1100px;margin:30px auto;padding:20px}
.card{background:#1b1b1b;border:1px solid #333;border-radius:14px;padding:20px;margin-bottom:20px}
h1,h2{margin-top:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #333;text-align:left}
input,select{width:100%;box-sizing:border-box;background:#222;color:#fff;border:1px solid #555;border-radius:7px;padding:8px}
button{background:#fff;color:#111;border:0;border-radius:8px;padding:10px 16px;font-weight:bold;cursor:pointer}
.total{font-size:18px;font-weight:bold}.note{color:#bbb;font-size:13px}
@media(max-width:700px){.grid{grid-template-columns:1fr}}
</style></head><body><div class="wrap">
<div class="card"><div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap"><h1 style="margin:0">BMW — Best Worst Method</h1><a href="dashboard.php" title="Kembali ke Dashboard" aria-label="Kembali ke Dashboard" style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:10px;background:#fff;color:#111;text-decoration:none;font-size:18px"><i class="fa-solid fa-arrow-left"></i></a></div><?php if($saved): ?><p style="background:#173d26;padding:10px;border-radius:8px;color:#b8f3c8">Bobot BMW berhasil disimpan dan sudah dipakai oleh MABAC.</p><?php endif; ?>
<p>BMW menentukan bobot kriteria secara otomatis. Nama dan tipe kriteria mengikuti halaman Kriteria. Bobot yang sudah dihitung disimpan dan langsung digunakan oleh MABAC.</p>
<form method="post">
<div class="grid">
<div><label>Best Criterion</label><select name="best">
<?php foreach($C as $r): ?><option value="<?=esc($r['kode'])?>" <?=($best===$r['kode']?'selected':'')?>><?=esc($r['kode'].' - '.$r['nama'])?></option><?php endforeach; ?>
</select></div>
<div><label>Worst Criterion</label><select name="worst">
<?php foreach($C as $r): ?><option value="<?=esc($r['kode'])?>" <?=($worst===$r['kode']?'selected':'')?>><?=esc($r['kode'].' - '.$r['nama'])?></option><?php endforeach; ?>
</select></div></div>

<h2>Best-to-Others (BO)</h2><table><tr><th>Kriteria</th><th>BO (1–9)</th></tr>
<?php foreach($C as $r): $c=$r['kode']; ?><tr><td><?=esc($c.' - '.$r['nama'])?></td><td><input type="number" name="bo[<?=esc($c)?>]" min="1" max="9" step="1" value="<?=esc($bo[$c]??1)?>"></td></tr><?php endforeach; ?></table>

<h2>Others-to-Worst (OW)</h2><table><tr><th>Kriteria</th><th>OW (1–9)</th></tr>
<?php foreach($C as $r): $c=$r['kode']; ?><tr><td><?=esc($c.' - '.$r['nama'])?></td><td><input type="number" name="ow[<?=esc($c)?>]" min="1" max="9" step="1" value="<?=esc($ow[$c]??1)?>"></td></tr><?php endforeach; ?></table>
<br><button name="calculate" value="1">Hitung Bobot BMW</button>
</form></div>

<div class="card"><h2>Hasil Bobot BMW</h2>
<table><tr><th>Kode</th><th>Kriteria</th><th>Jenis</th><th>Bobot BMW</th></tr>
<?php $tot=0; foreach($C as $i=>$r): $c=$r['kode']; $w=$weights[$c]??0; $tot+=$w; ?>
<tr><td><?=esc($c)?></td><td><?=esc($r['nama'])?></td><td><?=esc($r['jenis'])?></td><td><?=number_format($w*100,2,',','.')?>%</td></tr>
<?php endforeach; ?><tr><td colspan="3" class="total">TOTAL</td><td class="total"><?=number_format($tot*100,2,',','.')?>%</td></tr></table>
<?php if($_SERVER['REQUEST_METHOD']==='POST'): ?><p class="note">Deviasi maksimum (ξ) ≈ <?=number_format($xi,6,',','.')?>. Bobot dinormalisasi otomatis sehingga total 100%.</p><?php endif; ?>
</div>
</div></body></html>
