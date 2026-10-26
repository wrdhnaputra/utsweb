<?php
require_once __DIR__ . '/connect.php';
mysqli_select_db($conn, "db5a");

// Ambil data dari formulir
$nim = trim($_POST['NIM'] ?? '');
$nm_lengkap = trim($_POST['NamaMahasiswa'] ?? '');
$nilai_akhir = isset($_POST['NilaiAkhir'])
    ? (float) $_POST['NilaiAkhir']
    : -1;

// Validasi input
if (
    $nim === '' ||
    $nm_lengkap === '' ||
    !isset($_POST['NilaiAkhir']) ||
    $nilai_akhir < 0 ||
    $nilai_akhir > 100
) {
    die("Data tidak lengkap atau nilai tidak valid.");
}

// Menghitung nilai huruf dan predikat
function getNilaiHuruf($nilai) {
    if ($nilai >= 0 && $nilai <= 34.50) {
        return ["E", "Tidak Lulus"];
    } elseif ($nilai > 34.50 && $nilai <= 50.50) {
        return ["D", "Kurang Memuaskan"];
    } elseif ($nilai > 50.50 && $nilai <= 65.50) {
        return ["C", "Cukup Memuaskan"];
    } elseif ($nilai > 65.50 && $nilai <= 80.50) {
        return ["B", "Memuaskan"];
    } elseif ($nilai > 80.50 && $nilai <= 100) {
        return ["A", "Sangat Memuaskan"];
    }

    return ["", ""];
}

$hasil = getNilaiHuruf($nilai_akhir);
$nilai_huruf = $hasil[0];
$predikat = $hasil[1];

// Foto bersifat opsional
$foto_nama = "";

if (isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        die("Foto gagal diunggah.");
    }

    $fileTmpPath = $_FILES['foto']['tmp_name'];
    $fileName = $_FILES['foto']['name'];
    $fileExtension = strtolower(
        pathinfo($fileName, PATHINFO_EXTENSION)
    );

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($fileExtension, $allowedExtensions, true)) {
        die("Format foto harus JPG, JPEG, PNG, GIF, atau WEBP.");
    }

    // Pastikan file yang diunggah benar-benar gambar
    if (getimagesize($fileTmpPath) === false) {
        die("File yang diunggah bukan gambar yang valid.");
    }

    // Buat folder uploads jika belum tersedia
    $uploadDir = __DIR__ . '/uploads/';

    if (!is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true)) {
        die("Folder uploads gagal dibuat.");
    }

    // Nama file dibuat unik
    $newFileName = uniqid('foto_', true) . '.' . $fileExtension;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        die("Foto gagal disimpan ke folder uploads.");
    }

    $foto_nama = $newFileName;
}

// Simpan ke tabel nilaimahasiswa
$sql = "INSERT INTO nilaimahasiswa
        (NIM, NamaMahasiswa, NilaiAkhir, NilaiHuruf,
         Predikat, foto, created_date, modified_date)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal disiapkan: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssdsss",
    $nim,
    $nm_lengkap,
    $nilai_akhir,
    $nilai_huruf,
    $predikat,
    $foto_nama
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header('Location: viewdata.php');
    exit();
} else {
    echo "Data gagal disimpan: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>