<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Nilai Mahasiswa</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 480px;
        }

        .container h3 {
            margin-bottom: 24px;
            color: #2c3e50;
            text-align: center;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #2d3748;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input[readonly] {
            background-color: #edf2f7;
            color: #718096;
            cursor: not-allowed;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="file"]:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15);
            outline: none;
        }

        .row-group {
            display: flex;
            gap: 12px;
        }

        .row-group .form-group {
            flex: 1;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #3182ce;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #2b6cb0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h3>Form Nilai Mahasiswa</h3>
        <!-- Wajib menggunakan enctype="multipart/form-data" untuk pengiriman file -->
        <form action="viewdata.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="nim">NIM</label>
                <input type="text" id="nim" name="nim" minlength="10" maxlength="10" placeholder="Contoh: 3xxxx16xxx" required>
            </div>

            <div class="form-group">
                <label for="nm_lengkap">Nama Lengkap</label>
                <input type="text" id="nm_lengkap" name="nm_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>

            <!-- Field Upload Foto -->
            <div class="form-group">
                <label for="foto">Foto Profil / Mahasiswa</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
                <label for="nilai_akhir">Nilai Akhir (0 - 100)</label>
                <input type="number" id="nilai_akhir" name="nilai_akhir" step="0.01" min="0" max="100" placeholder="Contoh: 85.50" required>
            </div>

            <div class="row-group">
                <div class="form-group">
                    <label for="nilai_huruf">Nilai Huruf (Otomatis)</label>
                    <input type="text" id="nilai_huruf" name="nilai_huruf" placeholder="Huruf" readonly>
                </div>

                <div class="form-group">
                    <label for="predikat">Predikat (Otomatis)</label>
                    <input type="text" id="predikat" name="predikat" placeholder="Predikat" readonly>
                </div>
            </div>

            <button type="submit" class="btn-submit">Simpan Data</button>
        </form>
    </div>

    <script>
        // Fungsi perhitungan otomatis Nilai Huruf dan Predikat
        function hitungKonversi(nilai) {
            let huruf = '';
            let predikat = '';

            if (nilai >= 80.50 && nilai <= 100) {
                huruf = 'A'; predikat = 'Sangat Memuaskan';
            } else if (nilai > 65.50 && nilai <= 80.50) {
                huruf = 'B'; predikat = 'Memuaskan';
            } else if (nilai > 50.50 && nilai <= 65.50) {
                huruf = 'C'; predikat = 'Cukup Memuaskan';
            } else if (nilai > 34.50 && nilai <= 50.50) {
                huruf = 'D'; predikat = 'Kurang Memuaskan';
            } else if (!isNaN(nilai) && nilai >= 0 && nilai <= 34.50) {
                huruf = 'E'; predikat = 'Tidak Lulus';
            }
            return { huruf, predikat };
        }

        document.getElementById('nilai_akhir').addEventListener('input', function () {
            const nilai = parseFloat(this.value);
            if (!isNaN(nilai)) {
                const hasil = hitungKonversi(nilai);
                document.getElementById('nilai_huruf').value = hasil.huruf;
                document.getElementById('predikat').value = hasil.predikat;
            } else {
                document.getElementById('nilai_huruf').value = '';
                document.getElementById('predikat').value = '';
            }
        });
    </script>
</body>
</html>