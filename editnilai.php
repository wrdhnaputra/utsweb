<?php
require_once __DIR__ . "/connect.php";
mysqli_select_db($conn, "db5a");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = mysqli_real_escape_string($conn, $_POST['NIM'] ?? '');
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['namalengkap'] ?? '');
    $nilai_akhir = isset($_POST['nilaiakhir']) ? floatval($_POST['nilaiakhir']) : -1;
    $modified_date = date('Y-m-d H:i:s');

    // Validasi data
    if ($nim === '' || $nama_lengkap === '' || $nilai_akhir < 0 || $nilai_akhir > 100) {
        die("Data tidak lengkap atau nilai tidak valid.");
    }

    // Hitung ulang huruf & predikat
    function getNilaiHuruf($nilai) {
        if ($nilai >= 0 && $nilai <= 34.50) return ["E", "Tidak Lulus"];
        elseif ($nilai > 34.50 && $nilai <= 50.50) return ["D", "Kurang Memuaskan"];
        elseif ($nilai > 50.50 && $nilai <= 65.50) return ["C", "Cukup Memuaskan"];
        elseif ($nilai > 65.50 && $nilai <= 80.50) return ["B", "Memuaskan"];
        elseif ($nilai > 80.50 && $nilai <= 100.00) return ["A", "Sangat Memuaskan"];
        else return ["Invalid", "Nilai tidak valid"];
    }

    $hasil = getNilaiHuruf($nilai_akhir);
    $nilai_huruf = $hasil[0];
    $predikat = $hasil[1];

    // Cek upload foto baru
    $foto_update = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            die("Foto gagal diunggah.");
        }

        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $allowedExtensions, true)) {
            die("Format foto harus JPG, JPEG, PNG, GIF, atau WEBP.");
        }

        if (getimagesize($fileTmpPath) === false) {
            die("File yang diunggah bukan gambar yang valid.");
        }

        $newFileName = uniqid('foto_', true) . '.' . $fileExtension;
        $uploadFileDir = __DIR__ . '/uploads/';

        if (!is_dir($uploadFileDir) && !mkdir($uploadFileDir, 0755, true)) {
            die("Folder uploads gagal dibuat.");
        }

        if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
            $foto_escaped = mysqli_real_escape_string($conn, $newFileName);
            $foto_update = ", foto = '$foto_escaped'";
        } else {
            die("Foto gagal disimpan ke folder uploads.");
        }
    }

    $sql = "UPDATE nilaimahasiswa SET
            NamaMahasiswa = '$nama_lengkap',
            NilaiAkhir = '$nilai_akhir',
            NilaiHuruf = '$nilai_huruf',
            Predikat = '$predikat',
            modified_date = '$modified_date'
            $foto_update
            WHERE NIM = '$nim'";

    if (mysqli_query($conn, $sql)) {
        header("Location: viewdata.php");
        exit();
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>