<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Input Nilai Mahasiswa</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[readonly] {
            background-color: #eee;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .kembali {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Form Input Nilai Mahasiswa</h2>

    <form action="viewdata_nilai.php" method="POST"
          enctype="multipart/form-data">

        <label for="NIM">NIM</label>
        <input
            type="text"
            id="NIM"
            name="NIM"
            maxlength="100"
            pattern="[0-9]{10}"
            title="NIM harus terdiri dari 10 digit angka"
            required
        >

        <label for="NamaMahasiswa">Nama Mahasiswa</label>
        <input
            type="text"
            id="NamaMahasiswa"
            name="NamaMahasiswa"
            maxlength="100"
            required
        >

        <label for="NilaiAkhir">Nilai Akhir</label>
        <input
            type="number"
            id="NilaiAkhir"
            name="NilaiAkhir"
            min="0"
            max="100"
            step="0.01"
            oninput="hitungNilai()"
            required
        >

        <label for="NilaiHuruf">Nilai Huruf</label>
        <input
            type="text"
            id="NilaiHuruf"
            name="NilaiHuruf"
            readonly
            required
        >

        <label for="Predikat">Predikat</label>
        <input
            type="text"
            id="Predikat"
            name="Predikat"
            readonly
            required
        >

        <label for="foto">Foto Mahasiswa (Opsional)</label>
        <input
            type="file"
            id="foto"
            name="foto"
            accept=".jpg,.jpeg,.png"
        >

        <button type="submit" name="submit">
            Simpan Data
        </button>
    </form>

    <a href="viewdata.php" class="kembali">
        Kembali ke Data Mahasiswa
    </a>
</div>

<script>
    function hitungNilai() {
        const inputNilai = document.getElementById("NilaiAkhir");
        const nilaiHuruf = document.getElementById("NilaiHuruf");
        const predikat = document.getElementById("Predikat");

        if (inputNilai.value === "") {
            nilaiHuruf.value = "";
            predikat.value = "";
            return;
        }

        const nilai = parseFloat(inputNilai.value);

        if (isNaN(nilai) || nilai < 0 || nilai > 100) {
            nilaiHuruf.value = "";
            predikat.value = "";
            return;
        }

        if (nilai <= 34.50) {
            nilaiHuruf.value = "E";
            predikat.value = "Tidak Lulus";
        } else if (nilai <= 50.50) {
            nilaiHuruf.value = "D";
            predikat.value = "Kurang Memuaskan";
        } else if (nilai <= 65.50) {
            nilaiHuruf.value = "C";
            predikat.value = "Cukup Memuaskan";
        } else if (nilai <= 80.50) {
            nilaiHuruf.value = "B";
            predikat.value = "Memuaskan";
        } else {
            nilaiHuruf.value = "A";
            predikat.value = "Sangat Memuaskan";
        }
    }
</script>

</body>
</html>