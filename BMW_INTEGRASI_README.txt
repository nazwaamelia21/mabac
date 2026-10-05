PEMBARUAN BMW TERINTEGRASI
=============================

Tambahan:
1. bmw.php: Best/Worst, BO, OW, bobot BMW otomatis.
2. ranking.php: halaman khusus untuk menampilkan data hasil/ranking.
3. BMW menggunakan kode C1-C5 sehingga perubahan nama kriteria tetap terhubung.
4. Bobot BMW dinormalisasi otomatis menjadi total 100%.
5. File MABAC dan tampilan lama tidak dihapus.

CATATAN:
- Jika menu dashboard ingin ditambahkan, arahkan link menu ke bmw.php dan ranking.php.
- Integrasi database bobot final ke perhitungan MABAC bergantung pada struktur tabel yang sudah ada; file ini tidak menghapus struktur lama.


KOREKSI FINAL 3 COST + 2 BENEFIT
C1 Frekuensi latihan = Cost
C2 Durasi latihan = Cost
C3 Intensitas latihan (RPE) = Cost
C4 Riwayat cedera = Benefit
C5 Keluhan nyeri = Benefit

CATATAN:
Parameter C4 dan C5 pada rancangan awal menggunakan skor yang semakin tinggi untuk kondisi semakin buruk.
Jika C4/C5 diperlakukan sebagai Benefit, arah skala harus dipahami sebagai nilai kondisi aman/baik.
Untuk konsistensi dengan tipe Benefit, gunakan pemetaan terbalik pada input:
- C4: kondisi paling aman = 5, kondisi paling berisiko = 1
- C5: tidak nyeri = 5, nyeri sangat berat = 1
