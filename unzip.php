<?php
$zipFile = 'vendor.zip';

if (!file_exists($zipFile)) {
    die("File vendor.zip tidak ditemukan di folder ini!");
}

$zip = new ZipArchive;
$res = $zip->open($zipFile);
if ($res === TRUE) {
    // Ekstrak ke folder htdocs/vendor (karena struktur aslinya adalah folder vendor di dalam zip)
    // Jika isi zip sudah berupa folder 'vendor', kita ekstrak di direktori saat ini
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "<h1>SUKSES!</h1>";
    echo "Semua file vendor berhasil diekstrak dengan sempurna!<br>";
    echo "Silakan hapus file unzip.php dan vendor.zip ini dari server untuk keamanan.";
} else {
    echo "<h1>GAGAL!</h1>";
    echo "Gagal membuka file ZIP. Kode error: " . $res;
}
?>
