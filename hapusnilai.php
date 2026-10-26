<?php
// Koneksi ke database
require_once __DIR__ . "/connect.php";
mysqli_select_db($conn, "db5a");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil NIM dari form modal hapus
    $nim = mysqli_real_escape_string($conn, $_POST['NIM'] ?? '');

    if ($nim === '') {
        die("NIM tidak ditemukan.");
    }

    // Query untuk menghapus data berdasarkan NIM
    $sql = "DELETE FROM nilaimahasiswa_baru WHERE NIM = '$nim'";

    if (mysqli_query($conn, $sql)) {
        // Jika berhasil, arahkan kembali ke halaman tabel
        header("Location: viewdata.php");
        exit();
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>