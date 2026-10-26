<?php
// definisi nilai parameter server
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db5a";

//create connection to db
//$conn = mysqli_connect("localhost", "root", "");
$conn = mysqli_connect($servername, $username, $password, $dbname);

//check connection berhasil atau tidak
if (!$conn) {
    // jika koneksi gagal
    die("Connection failed: " . mysqli_connect_error());
}

// Query sql to create table
$sql = "CREATE TABLE nilaimahasiswa (
    nim VARCHAR(10) PRIMARY KEY,
    namamhsw VARCHAR(30) NOT NULL,
    nilaiakhir FLOAT(5) NOT NULL,
    nilaihuruf VARCHAR(5),
    predikat VARCHAR(20),
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// menjalankan fungsi mysqli_query
if (mysqli_query($conn, $sql)) {
  echo "Table nilaimahasiswa Berhasil dibuat";
} else {
  echo "Pembuatan Tabel Gagal: " . mysqli_error($conn);
}

// menutup koneksi ke db
mysqli_close($conn);

?>  