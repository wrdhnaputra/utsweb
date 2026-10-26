<?php
require_once __DIR__ . '/connect.php';

// Membuat tabel baru
$sql = "CREATE TABLE IF NOT EXISTS nilaimahasiswa_baru (
    NIM VARCHAR(10) NOT NULL PRIMARY KEY,
    NamaMahasiswa VARCHAR(100) NOT NULL,
    NilaiAkhir DECIMAL(5,2) NOT NULL,
    NilaiHuruf CHAR(1) NOT NULL,
    Predikat VARCHAR(50) NOT NULL,
    created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    foto VARCHAR(255) DEFAULT NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Tabel nilaimahasiswa_baru berhasil dibuat!";
} else {
    echo "Gagal membuat tabel: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
