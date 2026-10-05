<?php
session_start();
if (!isset($_SESSION['username'])) { header("location:index.php"); exit; }
require_once "db.php";
require_once "model.php";
$codes=['C1','C2','C3','C4','C5'];
$cm=get_criteria_model();
$bmw=get_bmw_settings();
$weights=model_weights_for_codes($codes);
$jml_alternatif = 0;
$qAlt=mysqli_query($koneksi,"SELECT COUNT(*) AS jml FROM tbl_alternatif");
if($qAlt && ($ra=mysqli_fetch_assoc($qAlt))) $jml_alternatif=(int)$ra['jml'];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Knowledge Graph - SPK MABAC</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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
.kg-node.data{right:7%;bottom:10%;border-top:3px solid #20b8c7}.kg-node.data i{color:#20b8c7}.kg-node.bmw{left:50%;bottom:5%;transform:translateX(-50%);border-top:3px solid #8b5cf6;width:220px}.kg-node.bmw i{color:#8b5cf6}
.kg-line{position:absolute;height:2px;background:linear-gradient(90deg,rgba(255,255,255,.05),rgba(244,196,48,.6),rgba(255,255,255,.05));z-index:2;transform-origin:left center}
.kg-line.l1{width:29%;left:25%;top:29%;transform:rotate(16deg)}
.kg-line.l2{width:29%;right:25%;top:29%;transform:rotate(164deg)}
.kg-line.l3{width:29%;left:25%;bottom:29%;transform:rotate(-16deg)}
.kg-line.l4{width:29%;right:25%;bottom:29%;transform:rotate(-164deg)}.kg-line.l5{width:18%;left:41%;bottom:20%;transform:rotate(-90deg)}
.kg-flow{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:10px;margin-top:20px}
.kg-pill{border:1px solid var(--line);background:#15191e;border-radius:999px;padding:8px 13px;color:#dce1e7;font-size:.8rem}
.kg-pill i{margin-right:6px}
.kg-rel-card{height:100%;border:1px solid var(--line);border-radius:16px;background:linear-gradient(145deg,#15191e,#101318);padding:20px}
.kg-rel-card h5{font-size:1rem;margin-bottom:12px}.kg-rel-card ul{margin:0;padding-left:18px;color:var(--muted);line-height:1.8}
@media(max-width:900px){.kg-board{min-height:760px}.kg-node{width:180px}.kg-node.med{left:4%;top:6%}.kg-node.info{right:4%;top:6%}.kg-node.climb{left:4%;bottom:6%}.kg-node.data{right:4%;bottom:6%}}
@media(max-width:650px){.kg-board{min-height:820px;padding:18px}.kg-center{width:165px}.kg-node{width:145px}.kg-node strong{font-size:.82rem}.kg-node small{font-size:.7rem}.kg-node.med{left:2%;top:4%}.kg-node.info{right:2%;top:4%}.kg-node.climb{left:2%;bottom:4%}.kg-node.data{right:2%;bottom:4%}.kg-node.bmw{left:50%;bottom:1%;transform:translateX(-50%);width:145px}.kg-line{display:none}}

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
  <a class="sidebar-link" href="kriteria.php"><i class="fas fa-list-check"></i><span>Kriteria</span></a>
  <a class="sidebar-link" href="bmw.php"><i class="fas fa-scale-balanced"></i><span>BMW</span></a>
  <a class="sidebar-link" href="perhitungan.php"><i class="fas fa-calculator"></i><span>Perhitungan MABAC</span></a>
  <a class="sidebar-link active" href="knowledge_graph.php"><i class="fas fa-diagram-project"></i><span>Knowledge Graph</span></a>
  <div class="sidebar-divider"></div>
  <a class="sidebar-link" href="logout.php"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
  <div class="sidebar-foot"><strong>SPK Risiko Cedera</strong><br>Representasi hubungan pengetahuan medis, informatika, panjat tebing, data, dan MABAC.</div>
</aside>
<header class="app-topbar">
  <div class="topbar-brand">SPK MABAC <span>|</span> Knowledge Graph</div>
  <div class="topbar-user"><i class="fas fa-circle-user"></i><?= htmlspecialchars($_SESSION['username']) ?></div>
</header>

<main class="page-shell">
  <section class="kg-hero mb-4">
    <div class="kg-badge"><i class="fas fa-diagram-project"></i> Knowledge Graph Sistem</div>
    <h1>Hubungan Pengetahuan SPK Risiko Cedera</h1>
    <p>Representasi hubungan antara bidang medis, informatika, panjat tebing, data atlet, kriteria penelitian, dan metode MABAC yang digunakan untuk menghasilkan peringkat risiko cedera.</p>
  </section>

  <section class="kg-board mb-4">
    <div class="kg-line l1"></div><div class="kg-line l2"></div><div class="kg-line l3"></div><div class="kg-line l4"></div><div class="kg-line l5"></div>
    <div class="kg-node med"><i class="fas fa-heart-pulse"></i><strong>Medis</strong><small>Cedera, riwayat cedera, keluhan nyeri dan faktor risiko.</small></div>
    <div class="kg-node info"><i class="fas fa-laptop-code"></i><strong>Informatika</strong><small>SPK, PHP, MySQL, pengolahan data dan algoritma.</small></div>
    <div class="kg-node climb"><i class="fas fa-mountain"></i><strong>Panjat Tebing</strong><small>Frekuensi, durasi, intensitas latihan dan aktivitas atlet.</small></div>
    <div class="kg-node data"><i class="fas fa-database"></i><strong>Data & Kriteria</strong><small><?= $jml_alternatif ?> alternatif atlet dan 5 kriteria penelitian.</small></div>
    <div class="kg-node bmw"><i class="fas fa-scale-balanced"></i><strong>BMW</strong><small>Menentukan bobot kriteria berdasarkan Best, Worst, BO dan OW.</small></div>
    <div class="kg-center"><i class="fas fa-chart-line"></i><strong>MABAC</strong><small>Mengolah bobot dan data menjadi nilai risiko serta ranking.</small></div>
  </section>

  <div class="kg-flow mb-4">
    <span class="kg-pill"><i class="fas fa-mountain" style="color:var(--yellow)"></i>Panjat Tebing</span><i class="fas fa-arrow-right" style="color:var(--muted)"></i>
    <span class="kg-pill"><i class="fas fa-database" style="color:var(--cyan)"></i>Data & Kriteria</span><i class="fas fa-arrow-right" style="color:var(--muted)"></i>
    <span class="kg-pill"><i class="fas fa-scale-balanced" style="color:var(--purple)"></i>BMW → Bobot</span><i class="fas fa-arrow-right" style="color:var(--muted)"></i>
    <span class="kg-pill"><i class="fas fa-calculator" style="color:var(--blue)"></i>MABAC</span><i class="fas fa-arrow-right" style="color:var(--muted)"></i>
    <span class="kg-pill"><i class="fas fa-ranking-star" style="color:var(--green)"></i>Ranking Risiko</span>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="kg-rel-card">
        <h5><i class="fas fa-link me-2" style="color:var(--cyan)"></i>Relasi Kriteria → MABAC</h5>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead><tr><th>Kode</th><th>Kriteria</th><th>Bobot</th><th>Tipe</th></tr></thead>
            <tbody>
            <?php foreach($codes as $c): $w=$weights[$c]??0; ?>
              <tr><td><strong><?= htmlspecialchars($c) ?></strong></td><td><?= htmlspecialchars($cm[$c]['nama']) ?></td><td><?= number_format($w*100,2,',','.') ?>%</td><td><span class="badge <?= $cm[$c]['jenis']==='Cost'?'bg-danger':'bg-success' ?>"><?= htmlspecialchars($cm[$c]['jenis']) ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="kg-rel-card">
        <h5><i class="fas fa-route me-2" style="color:var(--yellow)"></i>Alur Pengetahuan</h5>
        <ul>
          <li><strong>Panjat tebing</strong> menghasilkan konteks latihan dan aktivitas atlet.</li>
          <li><strong>Medis</strong> memberikan konteks faktor cedera yang dinilai.</li>
          <li><strong>Informatika</strong> menyediakan SPK, database dan algoritma.</li>
          <li><strong>Data & kriteria</strong> menjadi input penilaian.</li>
          <li><strong>BMW</strong> menentukan bobot kriteria sebelum perhitungan MABAC.</li>
          <li><strong>MABAC</strong> mengolah data menjadi nilai dan ranking risiko.</li>
        </ul>
      </div>
    </div>
  </div>

  <p class="footer-note mt-3 mb-0"><i class="fas fa-circle-info me-1"></i> Knowledge Graph ini merupakan representasi hubungan pengetahuan dalam sistem, bukan diagnosis medis.</p>
</main>
</body>
</html>
