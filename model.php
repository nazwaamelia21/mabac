<?php
/* Helper model SPK MABAC + BMW.
   Cost/Benefit disimpan permanen pada tabel khusus agar kompatibel
   dengan database lama yang tbl_kriteria-nya hanya berisi C1..C5. */
if (!isset($koneksi)) require_once __DIR__.'/db.php';

function ensure_criteria_store(){
    global $koneksi;
    $sql="CREATE TABLE IF NOT EXISTS tbl_kriteria_model (
        kode VARCHAR(10) NOT NULL,
        nama VARCHAR(255) NOT NULL,
        jenis ENUM('Cost','Benefit') NOT NULL DEFAULT 'Cost',
        skala1 VARCHAR(255) NOT NULL,
        skala2 VARCHAR(255) NOT NULL,
        skala3 VARCHAR(255) NOT NULL,
        skala4 VARCHAR(255) NOT NULL,
        skala5 VARCHAR(255) NOT NULL,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (kode)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if(!mysqli_query($koneksi,$sql)){
        return false;
    }

    $seed=[
        ['C1','Frekuensi latihan','Cost',['≤ 1 kali/minggu','2 kali/minggu','3 kali/minggu','4 kali/minggu','≥ 5 kali/minggu']],
        ['C2','Durasi latihan','Cost',['≤ 60 menit/sesi','61–90 menit','91–120 menit','121–180 menit','> 180 menit']],
        ['C3','Intensitas latihan (RPE)','Cost',['RPE 1–2 (sangat ringan)','RPE 3–4 (ringan)','RPE 5–6 (sedang)','RPE 7–8 (berat)','RPE 9–10 (sangat berat)']],
        ['C4','Riwayat cedera','Benefit',['Cedera berulang/keluhan cedera masih ada','Cedera ≤12 bulan lalu dan sudah pulih','Cedera 13–24 bulan lalu','Cedera >24 bulan lalu dan pulih','Tidak pernah mengalami cedera']],
        ['C5','Keluhan nyeri','Benefit',['NRS 8–10 (sangat berat)','NRS 5–7','NRS 3–4','NRS 1–2 (ringan)','NRS 0 (tidak nyeri)']]
    ];
    $st=mysqli_prepare($koneksi,'INSERT IGNORE INTO tbl_kriteria_model (kode,nama,jenis,skala1,skala2,skala3,skala4,skala5) VALUES (?,?,?,?,?,?,?,?)');
    if(!$st) return false;
    foreach($seed as $x){
        mysqli_stmt_bind_param($st,'ssssssss',$x[0],$x[1],$x[2],$x[3][0],$x[3][1],$x[3][2],$x[3][3],$x[3][4]);
        if(!mysqli_stmt_execute($st)){ mysqli_stmt_close($st); return false; }
    }
    mysqli_stmt_close($st);
    return true;
}

function get_criteria_model(){
    global $koneksi;
    ensure_criteria_store();
    $out=[];
    $q=@mysqli_query($koneksi,"SELECT kode,nama,jenis,skala1,skala2,skala3,skala4,skala5 FROM tbl_kriteria_model ORDER BY FIELD(kode,'C1','C2','C3','C4','C5')");
    while($q && ($r=mysqli_fetch_assoc($q))){
        $out[$r['kode']]=[
            'kode'=>$r['kode'],
            'nama'=>$r['nama'],
            'jenis'=>($r['jenis']==='Benefit'?'Benefit':'Cost'),
            's'=>[1=>$r['skala1'],2=>$r['skala2'],3=>$r['skala3'],4=>$r['skala4'],5=>$r['skala5']]
        ];
    }
    return $out;
}

function save_criteria_model($codes,$posted,$current){
    global $koneksi;
    if(!ensure_criteria_store()) return ['ok'=>false,'errors'=>['Tidak dapat membuat/akses tabel penyimpanan kriteria: '.mysqli_error($koneksi)]];
    mysqli_begin_transaction($koneksi);
    $st=mysqli_prepare($koneksi,'UPDATE tbl_kriteria_model SET nama=?, jenis=?, skala1=?, skala2=?, skala3=?, skala4=?, skala5=? WHERE kode=?');
    if(!$st){
        mysqli_rollback($koneksi);
        return ['ok'=>false,'errors'=>[mysqli_error($koneksi)]];
    }
    $errors=[];
    foreach($codes as $c){
        $nama=trim($posted['nama'][$c]??$current[$c]['nama']);
        $jenis=(($posted['jenis'][$c]??'Cost')==='Benefit')?'Benefit':'Cost';
        $s=[];
        for($i=1;$i<=5;$i++) $s[$i]=trim($posted['skala'][$c][$i]??$current[$c]['s'][$i]);
        mysqli_stmt_bind_param($st,'ssssssss',$nama,$jenis,$s[1],$s[2],$s[3],$s[4],$s[5],$c);
        if(!mysqli_stmt_execute($st)){
            $errors[]=$c.': '.mysqli_stmt_error($st);
            break;
        }
    }
    mysqli_stmt_close($st);

    if(!$errors){
        $check=mysqli_query($koneksi,"SELECT kode,jenis FROM tbl_kriteria_model WHERE kode IN ('C1','C2','C3','C4','C5')");
        $saved=[];
        while($check && ($r=mysqli_fetch_assoc($check))) $saved[$r['kode']]=$r['jenis'];
        foreach($codes as $c){
            $expected=(($posted['jenis'][$c]??'Cost')==='Benefit')?'Benefit':'Cost';
            if(($saved[$c]??null)!==$expected){
                $errors[]=$c.': jenis tidak tersimpan (terbaca '.($saved[$c]??'NULL').')';
            }
        }
    }
    if($errors){
        mysqli_rollback($koneksi);
        return ['ok'=>false,'errors'=>$errors];
    }
    mysqli_commit($koneksi);
    return ['ok'=>true,'errors'=>[]];
}

function default_bmw_settings(){
    return [
        'best'=>'C3','worst'=>'C2',
        'bo'=>['C1'=>3,'C2'=>7,'C3'=>1,'C4'=>2,'C5'=>4],
        'ow'=>['C1'=>3,'C2'=>1,'C3'=>7,'C4'=>5,'C5'=>2],
        'weights'=>['C1'=>0.15777262,'C2'=>0.05568445,'C3'=>0.43155452,'C4'=>0.23665893,'C5'=>0.11832947]
    ];
}
function get_bmw_settings(){
    $d=default_bmw_settings();
    if(isset($_SESSION['bmw_settings']) && is_array($_SESSION['bmw_settings'])){
        $s=$_SESSION['bmw_settings'];
        foreach(['best','worst'] as $k) if(isset($s[$k])) $d[$k]=$s[$k];
        foreach(['bo','ow','weights'] as $k) if(isset($s[$k]) && is_array($s[$k])) $d[$k]=array_merge($d[$k],$s[$k]);
    }
    return $d;
}
function save_bmw_settings($best,$worst,$bo,$ow,$weights){
    $_SESSION['bmw_settings']=['best'=>$best,'worst'=>$worst,'bo'=>$bo,'ow'=>$ow,'weights'=>$weights];
}
function model_weights_for_codes($codes){
    $s=get_bmw_settings();$out=[];
    foreach($codes as $c)$out[$c]=(float)($s['weights'][$c]??0);
    $sum=array_sum($out);
    if($sum>0)foreach($out as $c=>$v)$out[$c]=$v/$sum;
    return $out;
}
?>