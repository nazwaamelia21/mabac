<?php
session_start();require_once 'db.php';require_once 'model.php';if(!isset($_SESSION['username'])){header('Location:index.php');exit;}
$codes=['C1','C2','C3','C4','C5'];
$cm=get_criteria_model();
$bmw=get_bmw_settings();
$weights=model_weights_for_codes($codes);

if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='save_criteria'){
    $result=save_criteria_model($codes,$_POST,$cm);
    if($result['ok']){
        header('Location:kriteria.php?saved=1');
        exit;
    }
    header('Location:kriteria.php?saved=0&error='.rawurlencode(implode(' | ',$result['errors'])));
    exit;
}
$cm=get_criteria_model();$weights=model_weights_for_codes($codes);
$saveStatus='';
if(isset($_GET['saved'])){
    if($_GET['saved']==='1') $saveStatus='<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>Perubahan kriteria berhasil disimpan ke database.</div>';
    else $saveStatus='<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-1"></i>Perubahan gagal disimpan: '.htmlspecialchars($_GET['error']??'Unknown error',ENT_QUOTES).'</div>';
}
$displayWeights=[];foreach($codes as $c)$displayWeights[$c]=number_format($weights[$c]*100,2,',','.').'%';
$alternatif=[];$q=mysqli_query($koneksi,'SELECT * FROM tbl_alternatif ORDER BY id_alternatif ASC');while($q&&($r=mysqli_fetch_assoc($q)))$alternatif[]=$r;$ranking=[];
if($alternatif){$min=$max=$R=$V=$BAA=$S=[];foreach($codes as $c){$v=array_map(fn($a)=>(float)$a[$c],$alternatif);$min[$c]=min($v);$max[$c]=max($v);}foreach($alternatif as $a){$x=[];foreach($codes as $c){$d=$max[$c]-$min[$c];$x[$c]=$d==0?0:($cm[$c]['jenis']==='Benefit'?((float)$a[$c]-$min[$c])/$d:($max[$c]-(float)$a[$c])/$d);}$R[]=$x;}foreach($R as $x){$v=[];foreach($codes as $c)$v[$c]=$weights[$c]*($x[$c]+1);$V[]=$v;}$n=count($V);foreach($codes as $c){$p=1;foreach($V as $v)$p*=$v[$c];$BAA[$c]=$p>0?pow($p,1/$n):0;}foreach($V as $i=>$v){$s=0;foreach($codes as $c)$s+=$v[$c]-$BAA[$c];$S[$i]=$s;$ranking[]=['id'=>$alternatif[$i]['subkriteria'],'nama'=>$alternatif[$i]['karyawan'],'nilai'=>$s];}usort($ranking,fn($a,$b)=>$b['nilai']<=>$a['nilai']);foreach($ranking as $i=>&$x)$x['rank']=$i+1;unset($x);}
function fnum($x,$d=6){return number_format((float)$x,$d,'.','');}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kriteria - SPK MABAC</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color:#f8f9fa; font-family: 'Segoe UI',sans-serif; }
.card { box-shadow:0 5px 20px rgba(0,0,0,0.1); }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
/* INLINE THEME CSS */
:root{
  --bg:#0b0d10;
  --panel:#14181d;
  --panel2:#1a1f25;
  --line:rgba(255,255,255,.09);
  --text:#f7f7f8;
  --muted:#a8b0bb;
  --red:#e21b23;
  --red2:#ff3940;
  --yellow:#f4c430;
  --blue:#2f7eea;
  --green:#31b36b;
  --purple:#8b5cf6;
  --cyan:#20b8c7;
}
*{box-sizing:border-box}
body.app-body{font-family:'Poppins','Segoe UI',sans-serif;background:radial-gradient(circle at 75% 0%,rgba(226,27,35,.08),transparent 28%),linear-gradient(180deg,#090b0e 0%,#11151a 100%);color:var(--text);min-height:100vh}
.app-sidebar{position:fixed;left:0;top:0;bottom:0;width:255px;background:linear-gradient(180deg,#090b0e,#11151a);border-right:1px solid var(--line);z-index:1030;padding:20px 14px;overflow-y:auto}
.sidebar-logo{display:block;text-align:center;padding:4px 20px 18px;border-bottom:1px solid var(--line);margin-bottom:18px}
.sidebar-logo img{width:125px;height:125px;object-fit:contain;display:block;margin:auto;filter:drop-shadow(0 8px 18px rgba(0,0,0,.35))}
.sidebar-title{font-size:15px;font-weight:700;margin-top:7px;color:#fff}.sidebar-subtitle{font-size:11px;color:var(--muted);line-height:1.4}
.sidebar-link{display:flex;align-items:center;gap:12px;color:#d9dee5;text-decoration:none;padding:12px 13px;border-radius:11px;margin:4px 0;font-weight:600;font-size:14px;transition:.2s}
.sidebar-link i{width:20px;text-align:center;color:#c6cbd2}.sidebar-link:hover{background:rgba(255,255,255,.07);color:#fff;transform:translateX(2px)}.sidebar-link.active{background:linear-gradient(90deg,var(--red),#c9141b);color:#fff;box-shadow:0 8px 18px rgba(226,27,35,.2)}.sidebar-link.active i{color:#fff}
.sidebar-divider{height:1px;background:var(--line);margin:15px 5px}.sidebar-foot{border:1px solid var(--line);border-radius:13px;padding:12px;color:var(--muted);font-size:11px;line-height:1.55;margin-top:18px}.sidebar-foot strong{color:#fff}
.app-topbar{position:fixed;left:255px;right:0;top:0;height:64px;background:rgba(9,11,14,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--line);z-index:1020;display:flex;align-items:center;justify-content:space-between;padding:0 26px}
.topbar-brand{font-size:18px;font-weight:700}.topbar-brand span{color:var(--red)}.topbar-user{font-size:13px;color:#dfe3e8}.topbar-user i{color:var(--red);margin-right:7px}
.page-shell{margin-left:255px;padding:92px 30px 50px;max-width:none}
.hero-climb{position:relative;overflow:hidden;border:1px solid var(--line);border-radius:22px;background:linear-gradient(100deg,rgba(14,17,21,.98),rgba(20,24,29,.93));color:#fff;padding:34px 38px;box-shadow:0 18px 50px rgba(0,0,0,.28);min-height:245px}
.hero-climb:before{content:'';position:absolute;right:0;top:0;width:48%;height:100%;background:linear-gradient(90deg,transparent,rgba(0,0,0,.15)),linear-gradient(135deg,rgba(226,27,35,.12),rgba(244,196,48,.08));clip-path:polygon(28% 0,100% 0,100% 100%,0 100%)}
.hero-climb:after{content:'';position:absolute;right:-55px;bottom:-75px;width:270px;height:270px;border:6px solid rgba(244,196,48,.16);border-radius:50%;transform:rotate(-20deg)}
.hero-title{position:relative;z-index:2;font-weight:700;font-size:clamp(1.65rem,3vw,2.45rem);margin-bottom:10px;max-width:720px}.hero-title .accent{color:var(--red)}
.hero-subtitle{position:relative;z-index:2;color:#c6cbd2;max-width:720px;margin-bottom:20px;line-height:1.7}.hero-rope{display:none}
.badge-med{position:relative;z-index:2;background:rgba(255,255,255,.06);border:1px solid var(--line);color:#e8ebef;border-radius:999px;padding:7px 12px;font-size:.78rem;font-weight:600;display:inline-flex;gap:7px;align-items:center}.badge-med i{color:var(--yellow)}
.med-note,.climb-note{border:1px solid var(--line);border-left:4px solid var(--red);background:#15191e;color:#cfd4da;border-radius:12px;padding:12px 15px}.climb-note{border-left-color:var(--yellow)}
.info-card,.card-dashboard{border:1px solid var(--line)!important;border-radius:16px!important;background:linear-gradient(145deg,#15191e,#101318)!important;color:var(--text)!important;box-shadow:0 12px 32px rgba(0,0,0,.2)!important;transition:.22s}.info-card:hover,.card-dashboard:hover{transform:translateY(-4px);border-color:rgba(255,255,255,.16)!important}
.icon-box{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;background:rgba(226,27,35,.12);color:var(--red);border:1px solid rgba(226,27,35,.2)}
.section-title{font-weight:700;color:#fff}.text-muted{color:var(--muted)!important}.display-5{color:#fff}.footer-note{color:var(--muted);font-size:.84rem}
.table,.table-responsive{color:#e8ebef}.table{--bs-table-bg:transparent;--bs-table-color:#e8ebef;--bs-border-color:rgba(255,255,255,.08)}.table thead th,.table-primary th{background:#1b2026!important;color:#fff!important;border-color:rgba(255,255,255,.08)!important}.table-striped>tbody>tr:nth-of-type(odd)>*{color:#e8ebef;background:rgba(255,255,255,.025)}.table td,.table th{border-color:rgba(255,255,255,.08)}
.card{background:#15191e;color:#eef1f4;border:1px solid var(--line)}.form-label{color:#dce1e6}.form-control,.form-select{background:#0f1317;color:#f1f3f5;border-color:#30363d}.form-control:focus,.form-select:focus{background:#0f1317;color:#fff;border-color:var(--red);box-shadow:0 0 0 .2rem rgba(226,27,35,.15)}
.alert-info{background:rgba(32,184,199,.09);border-color:rgba(32,184,199,.22);color:#cbeef2}.btn-primary{background:var(--blue);border-color:var(--blue)}.btn-success{background:var(--green);border-color:var(--green)}.btn-danger{background:var(--red);border-color:var(--red)}
.dashboard-stat{border:1px solid var(--line);background:#15191e;border-radius:16px;padding:20px;height:100%;box-shadow:0 12px 28px rgba(0,0,0,.18)}.dashboard-stat .stat-icon{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:14px}.stat-red{background:rgba(226,27,35,.14);color:#ff4a51}.stat-yellow{background:rgba(244,196,48,.14);color:#ffd75c}.stat-blue{background:rgba(47,126,234,.14);color:#69a5ff}.stat-green{background:rgba(49,179,107,.14);color:#61d891}.stat-purple{background:rgba(139,92,246,.14);color:#b59aff}.stat-cyan{background:rgba(32,184,199,.14);color:#63e0ea}
.quick-card{display:block;text-decoration:none;border:1px solid var(--line);background:#15191e;border-radius:14px;padding:15px;color:#fff;height:100%;transition:.2s}.quick-card:hover{transform:translateY(-3px);border-color:rgba(255,255,255,.18);color:#fff}.quick-card i{font-size:1.25rem;margin-bottom:10px}.quick-card small{display:block;color:var(--muted);margin-top:3px}
.login-bg{background:radial-gradient(circle at 15% 20%,rgba(226,27,35,.12),transparent 24%),radial-gradient(circle at 85% 75%,rgba(244,196,48,.08),transparent 26%),linear-gradient(135deg,#07090b,#12171c 55%,#090b0e);position:relative;overflow:hidden}.login-bg:before{content:'';position:absolute;inset:0;background:linear-gradient(120deg,transparent 55%,rgba(226,27,35,.05));pointer-events:none}
.login-card{width:min(430px,92vw);border:1px solid var(--line)!important;background:rgba(18,22,27,.92)!important;backdrop-filter:blur(14px);box-shadow:0 24px 70px rgba(0,0,0,.45);border-radius:22px!important;color:#fff}.login-brand{width:110px;height:110px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center}.login-brand img{width:105px;height:105px;object-fit:contain}.login-card .form-control{background:#0d1115}.login-card .input-group-text{background:#0d1115;border-color:#30363d;color:#cfd5db}.btn-login{background:linear-gradient(90deg,var(--red),#b9131a);color:#fff;border:0;font-weight:700}.btn-login:hover{background:linear-gradient(90deg,#ff3038,var(--red));color:#fff}
.brand-logo{width:42px;height:42px;object-fit:contain;vertical-align:middle;margin-right:8px}.page-title{font-weight:700;color:#fff}.subtle{color:var(--muted)}
@media(max-width:900px){.app-sidebar{width:78px;padding:14px 8px}.sidebar-logo{padding:4px 2px 14px}.sidebar-logo img{width:54px;height:54px}.sidebar-title,.sidebar-subtitle,.sidebar-link span,.sidebar-foot{display:none}.sidebar-link{justify-content:center;padding:12px}.app-topbar{left:78px}.page-shell{margin-left:78px;padding-left:18px;padding-right:18px}.hero-climb{padding:28px 24px}}


/* ===== Knowledge Graph ===== */
.kg-hero{position:relative;overflow:hidden;border:1px solid var(--line);border-radius:20px;padding:26px 28px;background:linear-gradient(135deg,#15191e 0%,#101318 65%,rgba(226,27,35,.10) 100%);box-shadow:0 14px 40px rgba(0,0,0,.24)}
.kg-hero h1{font-size:clamp(1.5rem,2.8vw,2.15rem);font-weight:700;margin:0 0 8px}
.kg-hero p{color:var(--muted);max-width:820px;margin:0;line-height:1.7}
.kg-badge{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(244,196,48,.28);background:rgba(244,196,48,.08);color:#f7d86b;border-radius:999px;padding:7px 12px;font-size:.78rem;font-weight:700;margin-bottom:12px}
.kg-board{position:relative;border:1px solid var(--line);border-radius:20px;background:radial-gradient(circle at 50% 45%,rgba(47,126,234,.07),transparent 28%),#0e1115;min-height:520px;overflow:hidden;padding:34px}
.kg-board:before,.kg-board:after{content:"";position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.035);pointer-events:none}
.kg-board:before{width:430px;height:430px;left:50%;top:50%;transform:translate(-50%,-50%)}
.kg-board:after{width:680px;height:680px;left:50%;top:50%;transform:translate(-50%,-50%)}
.kg-center{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:190px;height:112px;border-radius:18px;background:linear-gradient(135deg,#e21b23,#a90f15);border:1px solid rgba(255,255,255,.18);box-shadow:0 18px 45px rgba(226,27,35,.25);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;z-index:3;color:#fff}
.kg-center i{font-size:1.45rem;margin-bottom:5px}.kg-center strong{font-size:1.05rem}.kg-center small{opacity:.85}
.kg-node{position:absolute;z-index:4;width:205px;min-height:92px;border:1px solid var(--line);border-radius:16px;padding:14px 15px;background:linear-gradient(145deg,#181d23,#11151a);box-shadow:0 12px 30px rgba(0,0,0,.28)}
.kg-node strong{display:block;color:#fff;font-size:.95rem;margin-bottom:3px}.kg-node small{color:var(--muted);line-height:1.45}.kg-node i{font-size:1.15rem;margin-bottom:7px}
.kg-node.med{left:7%;top:10%;border-top:3px solid #ef4444}.kg-node.med i{color:#ef4444}
.kg-node.info{right:7%;top:10%;border-top:3px solid #8b5cf6}.kg-node.info i{color:#8b5cf6}
.kg-node.climb{left:7%;bottom:10%;border-top:3px solid #f4c430}.kg-node.climb i{color:#f4c430}
.kg-node.data{right:7%;bottom:10%;border-top:3px solid #20b8c7}.kg-node.data i{color:#20b8c7}
.kg-line{position:absolute;height:2px;background:linear-gradient(90deg,rgba(255,255,255,.05),rgba(244,196,48,.6),rgba(255,255,255,.05));z-index:2;transform-origin:left center}
.kg-line.l1{width:29%;left:25%;top:29%;transform:rotate(16deg)}
.kg-line.l2{width:29%;right:25%;top:29%;transform:rotate(164deg)}
.kg-line.l3{width:29%;left:25%;bottom:29%;transform:rotate(-16deg)}
.kg-line.l4{width:29%;right:25%;bottom:29%;transform:rotate(-164deg)}
.kg-flow{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:10px;margin-top:20px}
.kg-pill{border:1px solid var(--line);background:#15191e;border-radius:999px;padding:8px 13px;color:#dce1e7;font-size:.8rem}
.kg-pill i{margin-right:6px}
.kg-rel-card{height:100%;border:1px solid var(--line);border-radius:16px;background:linear-gradient(145deg,#15191e,#101318);padding:20px}
.kg-rel-card h5{font-size:1rem;margin-bottom:12px}.kg-rel-card ul{margin:0;padding-left:18px;color:var(--muted);line-height:1.8}
@media(max-width:900px){.kg-board{min-height:700px}.kg-node{width:180px}.kg-node.med{left:4%;top:6%}.kg-node.info{right:4%;top:6%}.kg-node.climb{left:4%;bottom:6%}.kg-node.data{right:4%;bottom:6%}}
@media(max-width:650px){.kg-board{min-height:780px;padding:18px}.kg-center{width:165px}.kg-node{width:145px}.kg-node strong{font-size:.82rem}.kg-node small{font-size:.7rem}.kg-node.med{left:2%;top:4%}.kg-node.info{right:2%;top:4%}.kg-node.climb{left:2%;bottom:4%}.kg-node.data{right:2%;bottom:4%}.kg-line{display:none}}

/* ===== Hero Panjat Tebing Aesthetic ===== */
.hero-climb{
  background-image:
    linear-gradient(90deg,rgba(7,9,11,.97) 0%,rgba(7,9,11,.88) 34%,rgba(7,9,11,.48) 62%,rgba(7,9,11,.25) 100%),
    url('hero-climbing.png');
  background-size:cover;
  background-position:center right;
  min-height:285px;
  display:flex;
  flex-direction:column;
  justify-content:center;
  border-color:rgba(255,255,255,.12);
}
.hero-climb:before{display:none}
.hero-climb:after{content:'';position:absolute;right:-90px;bottom:-120px;width:330px;height:330px;border:7px solid rgba(244,196,48,.18);border-radius:50%;transform:rotate(-20deg);box-shadow:-70px -30px 0 -60px rgba(226,27,35,.5)}
.hero-title{max-width:690px;text-shadow:0 2px 16px rgba(0,0,0,.55)}
.hero-subtitle{max-width:650px;text-shadow:0 1px 10px rgba(0,0,0,.55)}
.hero-climb .badge-med{backdrop-filter:blur(8px);background:rgba(5,7,9,.48);border-color:rgba(255,255,255,.15)}
@media(max-width:900px){.hero-climb{min-height:300px;background-position:62% center}}

</style>
</head>
<body class="app-body">
<aside class="app-sidebar">
  <a href="dashboard.php" class="sidebar-logo text-decoration-none">
    <img src="assets/logo-fpti.png" alt="Logo Federasi Panjat Tebing Indonesia">
    <div class="sidebar-title">SPK MABAC</div>
    <div class="sidebar-subtitle">Risiko Cedera Atlet Panjat Tebing</div>
  </a>
  <a class="sidebar-link" href="dashboard.php"><i class="fas fa-house"></i><span>Dashboard</span></a>
  <a class="sidebar-link" href="alternatif.php"><i class="fas fa-users"></i><span>Alternatif (Atlet)</span></a>
  <a class="sidebar-link active" href="kriteria.php"><i class="fas fa-list-check"></i><span>Kriteria</span></a>
  <a class="sidebar-link" href="perhitungan.php"><i class="fas fa-calculator"></i><span>Perhitungan MABAC</span></a>
  <a class="sidebar-link" href="knowledge_graph.php"><i class="fas fa-diagram-project"></i><span>Knowledge Graph</span></a>
  <div class="sidebar-divider"></div>
  <a class="sidebar-link" href="logout.php"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
  <div class="sidebar-foot"><strong>SPK Risiko Cedera</strong><br>Metode MABAC untuk membantu penilaian risiko berdasarkan data atlet.</div>
</aside>
<header class="app-topbar"><div class="topbar-brand">SPK MABAC <span>|</span> Risiko Cedera Atlet Panjat Tebing</div><div class="topbar-user"><i class="fas fa-circle-user"></i><?= htmlspecialchars($_SESSION['username']) ?></div></header>

<div class="container page-shell">
  <div class="climb-note mb-4"><i class="fas fa-heart-pulse me-2"></i><strong>Parameter Kriteria:</strong> tabel berikut menjadi acuan skala penilaian C1–C5. Bobot kriteria berasal dari BMW dan digunakan oleh MABAC.</div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Parameter model MABAC</h5>
      <?php if(isset($_GET['saved'])): ?><div class="alert alert-success py-2">Perubahan kriteria berhasil disimpan.</div><?php endif; ?>
      <div class="alert alert-info py-2 small"><strong>Jenis kriteria bisa diubah kapan saja.</strong> Pada kolom <b>Jenis</b>, pilih <b>Cost</b> atau <b>Benefit</b> untuk setiap C1–C5, lalu klik <b>Simpan Perubahan</b>. Perubahan ini langsung dipakai oleh normalisasi MABAC. Bobot tetap dihitung otomatis oleh BMW.</div>
      <form method="post"><input type="hidden" name="action" value="save_criteria"><div class="small text-muted mb-2">💡 <b>Cost/Benefit tidak dikunci.</b> Silakan ganti jenis tiap kriteria sesuai kebutuhan penelitian.</div><div class="table-responsive"><table class="table table-bordered text-center align-middle mb-0">
      <thead class="table-primary"><tr><th>Kode</th><th>Kriteria</th><th>Bobot BMW</th><th>Jenis</th><th>Skala 1</th><th>Skala 2</th><th>Skala 3</th><th>Skala 4</th><th>Skala 5</th></tr></thead><tbody>
      <?php foreach($codes as $c): ?><tr><td><strong><?= $c ?></strong></td><td><input class="form-control" name="nama[<?= $c ?>]" value="<?= htmlspecialchars($cm[$c]['nama'],ENT_QUOTES) ?>" required></td><td><strong><?= $displayWeights[$c] ?></strong><div class="small text-muted">dari BMW</div></td><td><select class="form-select" name="jenis[<?= $c ?>]"><option value="Cost" <?= $cm[$c]['jenis']==='Cost'?'selected':'' ?>>Cost</option><option value="Benefit" <?= $cm[$c]['jenis']==='Benefit'?'selected':'' ?>>Benefit</option></select></td><?php for($i=1;$i<=5;$i++): ?><td><input class="form-control" name="skala[<?= $c ?>][<?= $i ?>]" value="<?= htmlspecialchars($cm[$c]['s'][$i],ENT_QUOTES) ?>" required></td><?php endfor; ?></tr><?php endforeach; ?>
      <tr class="table-primary fw-bold"><td colspan="2">TOTAL BOBOT</td><td>100%</td><td colspan="6"></td></tr></tbody></table></div><div class="mt-3"><button class="btn btn-success"><i class="fas fa-save me-1"></i>Simpan Perubahan Kriteria</button> <a class="btn btn-outline-light" href="bmw.php">Atur Bobot BMW</a></div></form>
      <div class="mt-3 small text-muted">Best BMW: <strong><?= htmlspecialchars($bmw['best']) ?> – <?= htmlspecialchars($cm[$bmw['best']]['nama']) ?></strong> | Worst BMW: <strong><?= htmlspecialchars($bmw['worst']) ?> – <?= htmlspecialchars($cm[$bmw['worst']]['nama']) ?></strong>.</div>
    </div>
  </div>

  <div class="card mb-4"><div class="card-body"><h5 class="card-title">Bobot BMW</h5>
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle mb-0">
          <thead class="table-primary"><tr><th>Kode</th><th>Kriteria</th><th>Bobot</th><th>Tipe</th></tr></thead>
          <tbody>
            <?php foreach($codes as $c): ?><tr><td><?= $c ?></td><td class="text-start"><?= $cm[$c]['nama'] ?></td><td><?= $displayWeights[$c] ?></td><td><?= $cm[$c]['jenis'] ?></td></tr><?php endforeach; ?>
            <tr class="table-primary fw-bold"><td colspan="2">Total</td><td>100%</td><td>-</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title text-success">Tabel Output Hasil</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle mb-0">
          <thead class="table-primary"><tr><th>Ranking</th><th>SubAlternatif</th><th>Nama Atlet</th><th>Nilai S MABAC</th></tr></thead>
          <tbody>
          <?php if(count($ranking)>0): foreach($ranking as $item): ?>
            <tr><td><strong><?= $item['rank'] ?></strong></td><td><?= htmlspecialchars($item['id']) ?></td><td><?= htmlspecialchars($item['nama']) ?></td><td><?= fnum($item['nilai']) ?></td></tr>
          <?php endforeach; else: ?><tr><td colspan="4" class="text-center text-muted">Belum ada data atlet untuk menghasilkan ranking.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <p class="mt-3 mb-0 small text-muted">Hasil output dihitung dari data atlet yang tersimpan dan menggunakan bobot BMW yang sama dengan halaman Perhitungan MABAC.</p>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
