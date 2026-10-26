<?php
require_once __DIR__ . '/connect.php';

// Mengambil data dari form
$nim   = trim($_POST['NIM'] ?? '');
$nama  = trim($_POST['NamaMahasiswa'] ?? '');
$nilai = trim($_POST['NilaiAkhir'] ?? '');

// Validasi input
if ($nim === '' || $nama === '' || $nilai === '') {
    die("Data tidak lengkap. Silakan isi semua kolom.");
}

if (!preg_match('/^[0-9]{10}$/', $nim)) {
    die("NIM harus terdiri dari 10 digit angka.");
}

if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
    die("Nilai akhir harus berupa angka antara 0 sampai 100.");
}

$nilai = (float) $nilai;

// Menentukan nilai huruf dan predikat
if ($nilai <= 34.50) {
    $huruf = 'E';
    $predikat = 'Tidak Lulus';
} elseif ($nilai <= 50.50) {
    $huruf = 'D';
    $predikat = 'Kurang Memuaskan';
} elseif ($nilai <= 65.50) {
    $huruf = 'C';
    $predikat = 'Cukup Memuaskan';
} elseif ($nilai <= 80.50) {
    $huruf = 'B';
    $predikat = 'Memuaskan';
} else {
    $huruf = 'A';
    $predikat = 'Sangat Memuaskan';
}

// Upload foto (opsional)
$foto = NULL;

if (isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        die("Upload foto gagal.");
    }

    $folder = __DIR__ . '/uploads/';

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $ekstensi = strtolower(
        pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)
    );

    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];

    if (!in_array($ekstensi, $ekstensi_diizinkan, true)) {
        die("Foto harus berformat JPG, JPEG, atau PNG.");
    }

    // Memastikan file benar-benar gambar
    if (getimagesize($_FILES['foto']['tmp_name']) === false) {
        die("File yang diunggah bukan gambar yang valid.");
    }

    $namaFoto = uniqid('foto_', true) . '.' . $ekstensi;
    $tujuan = $folder . $namaFoto;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        die("Gagal menyimpan foto.");
    }

    $foto = $namaFoto;
}

// Query INSERT
$sql = "INSERT INTO nilaimahasiswa_baru
        (NIM, NamaMahasiswa, NilaiAkhir, NilaiHuruf, Predikat, foto)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal disiapkan: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssdsss",
    $nim,
    $nama,
    $nilai,
    $huruf,
    $predikat,
    $foto
);

// Menjalankan query
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: viewdata.php?status=berhasil");
    exit;
} else {
    $pesan = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    die("Gagal menyimpan data: " . $pesan);
}
?>
```