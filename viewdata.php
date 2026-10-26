<?php
require_once __DIR__ . "/connect.php";

// Pengaturan pencarian dan filter
$search = trim($_GET['search'] ?? '');
$predikat_filter = trim($_GET['predikat'] ?? 'all');

$sort_order = (
    isset($_GET['sort']) &&
    strtolower($_GET['sort']) === 'asc'
) ? 'ASC' : 'DESC';

$allowed_limits = [5, 10, 15, 29];
$limit = isset($_GET['limit']) &&
    in_array((int)$_GET['limit'], $allowed_limits, true)
    ? (int)$_GET['limit'] : 10;

$page = isset($_GET['page']) && (int)$_GET['page'] > 0
    ? (int)$_GET['page'] : 1;

// Menyiapkan kondisi pencarian
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "NamaMahasiswa LIKE ?";
    $params[] = "%{$search}%";
    $types .= 's';
}

$allowed_predicates = [
    'Sangat Memuaskan',
    'Memuaskan',
    'Cukup Memuaskan',
    'Kurang Memuaskan',
    'Tidak Lulus'
];

if (
    $predikat_filter !== 'all' &&
    in_array($predikat_filter, $allowed_predicates, true)
) {
    $where[] = "Predikat = ?";
    $params[] = $predikat_filter;
    $types .= 's';
}

$where_sql = !empty($where)
    ? ' WHERE ' . implode(' AND ', $where)
    : '';

// Menghitung jumlah data
$sql_count = "SELECT COUNT(*) AS total
              FROM nilaimahasiswa_baru
              $where_sql";

$stmt_count = mysqli_prepare($conn, $sql_count);

if (!$stmt_count) {
    die("Query hitung data gagal: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
}

mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);
$count_row = mysqli_fetch_assoc($count_result);

$total_data = (int)$count_row['total'];

mysqli_stmt_close($stmt_count);

// Menghitung pagination
$total_pages = (int)ceil($total_data / $limit);
$page = $total_pages > 0 ? min($page, $total_pages) : 1;
$offset = ($page - 1) * $limit;

// Mengambil data mahasiswa
$sql = "SELECT NIM, NamaMahasiswa, NilaiAkhir, NilaiHuruf,
               Predikat, foto, created_date, modified_date
        FROM nilaimahasiswa_baru
        $where_sql
        ORDER BY NilaiAkhir $sort_order
        LIMIT $limit OFFSET $offset";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query data gagal: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fungsi untuk menampilkan teks dengan aman
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Menyiapkan URL pagination
function pageUrl($pageNumber) {
    $query = $_GET;
    $query['page'] = $pageNumber;

    return '?' . http_build_query($query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Nilai Mahasiswa</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f5f6f8;
            font-family: Arial, sans-serif;
            color: #343a40;
        }

        .page-title {
            font-size: 28px;
            font-weight: 400;
            margin: 22px 0 18px;
        }

        .card {
            border: 1px solid #dedede;
            border-radius: 7px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        .toolbar {
            padding: 18px;
            border-bottom: 1px solid #dee2e6;
        }

        .directory-title {
            font-size: 18px;
            font-weight: 400;
            margin: 0;
        }

        .toolbar-controls {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .toolbar-controls .form-control,
        .toolbar-controls .form-select {
            min-height: 35px;
            font-size: 14px;
        }

        .search-box {
            width: 200px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            white-space: nowrap;
            font-weight: 600;
            padding: 14px 12px;
            background: #fff;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .student-photo {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 50%;
        }

        .no-photo {
            white-space: nowrap;
            font-size: 13px;
        }

        .table-footer {
            padding: 16px 18px;
            color: #6c757d;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 24px;
            }

            .toolbar-controls {
                justify-content: flex-start;
                margin-top: 15px;
            }

            .search-box {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid px-3 px-md-4">

    <h1 class="page-title">Rekap Nilai Mahasiswa</h1>

    <div class="card mb-4">

        <!-- Toolbar pencarian dan filter -->
        <div class="toolbar">
            <form method="GET" action="viewdata.php" id="filterForm">

                <div class="row align-items-center g-3">

                    <div class="col-12 col-lg-2">
                        <h2 class="directory-title">Directory Nilai</h2>
                    </div>

                    <div class="col-12 col-lg-10">
                        <div class="toolbar-controls">

                            <div class="input-group input-group-sm search-box">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="search"
                                    name="search"
                                    class="form-control"
                                    placeholder="Cari Nama..."
                                    value="<?= e($search) ?>"
                                >
                            </div>

                            <select name="predikat"
                                    class="form-select form-select-sm"
                                    style="width: 175px;">

                                <option value="all"
                                    <?= $predikat_filter === 'all' ? 'selected' : '' ?>>
                                    Semua Predikat
                                </option>

                                <?php foreach ($allowed_predicates as $predikat): ?>
                                    <option
                                        value="<?= e($predikat) ?>"
                                        <?= $predikat_filter === $predikat ? 'selected' : '' ?>>
                                        <?= e($predikat) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>

                            <select name="sort"
                                    class="form-select form-select-sm"
                                    style="width: 180px;">

                                <option value="desc"
                                    <?= $sort_order === 'DESC' ? 'selected' : '' ?>>
                                    Nilai: Tinggi - Rendah
                                </option>

                                <option value="asc"
                                    <?= $sort_order === 'ASC' ? 'selected' : '' ?>>
                                    Nilai: Rendah - Tinggi
                                </option>

                            </select>

                            <select name="limit"
                                    class="form-select form-select-sm"
                                    style="width: 95px;">

                                <?php foreach ($allowed_limits as $jumlah): ?>
                                    <option value="<?= $jumlah ?>"
                                        <?= $limit === $jumlah ? 'selected' : '' ?>>
                                        <?= $jumlah ?> / hal
                                    </option>
                                <?php endforeach; ?>

                            </select>

                            <button type="submit"
                                    class="btn btn-sm btn-secondary">
                                <i class="bi bi-funnel"></i>
                                Terapkan
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalTambah">

                                <i class="bi bi-person-plus-fill"></i>
                                Tambah Data
                            </button>

                        </div>
                    </div>

                </div>
            </form>
        </div>

        <!-- Tabel mahasiswa -->
        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Foto</th>
                        <th>Nilai Akhir</th>
                        <th>Nilai Huruf</th>
                        <th>Predikat</th>
                        <th>Created Date</th>
                        <th>Modified Date</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>
                            <td><?= e($row['NIM']) ?></td>

                            <td><?= e($row['NamaMahasiswa']) ?></td>

                            <td>
                                <?php
                                $fotoPath = '';

                                if (!empty($row['foto'])) {
                                    $namaFoto = basename($row['foto']);
                                    $pathFoto = __DIR__ . '/uploads/' . $namaFoto;

                                    if (is_file($pathFoto)) {
                                        $fotoPath = 'uploads/' . $namaFoto;
                                    }
                                }
                                ?>

                                <?php if ($fotoPath !== ''): ?>

                                    <img
                                        src="<?= e($fotoPath) ?>"
                                        alt="Foto mahasiswa"
                                        class="student-photo"
                                    >

                                <?php else: ?>

                                    <span class="no-photo">Tidak ada foto</span>

                                <?php endif; ?>
                            </td>

                            <td><?= e($row['NilaiAkhir']) ?></td>

                            <td><?= e($row['NilaiHuruf']) ?></td>

                            <td><?= e($row['Predikat']) ?></td>

                            <td><?= e($row['created_date']) ?></td>

                            <td><?= e($row['modified_date']) ?></td>

                            <td>
                                <div class="action-buttons">

                                    <div class="btn-group btn-group-sm">

                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            title="Edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit"
                                            data-nim="<?= e($row['NIM']) ?>"
                                            data-nama="<?= e($row['NamaMahasiswa']) ?>"
                                            data-nilai="<?= e($row['NilaiAkhir']) ?>">

                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger"
                                            title="Hapus"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHapus"
                                            data-nim="<?= e($row['NIM']) ?>">

                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>
                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center py-4">
                            Data tidak ditemukan!
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- Footer dan pagination -->
        <div class="table-footer d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                Menampilkan
                <?= $total_data > 0 ? $offset + 1 : 0 ?>
                -
                <?= min($offset + $limit, $total_data) ?>
                dari <?= $total_data ?> entri
            </div>

            <?php if ($total_pages > 1): ?>

                <nav aria-label="Navigasi halaman">

                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="<?= e(pageUrl(max(1, $page - 1))) ?>">
                                &laquo;
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="<?= e(pageUrl($i)) ?>">
                                    <?= $i ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="<?= e(pageUrl(min($total_pages, $page + 1))) ?>">
                                &raquo;
                            </a>
                        </li>

                    </ul>
                </nav>

            <?php endif; ?>

        </div>

    </div>
</div>


<!-- Modal Tambah Data -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <form action="viewdata_nilai.php"
                  method="POST"
                  enctype="multipart/form-data">

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Nilai</h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">NIM</label>

                        <input
                            type="text"
                            name="NIM"
                            class="form-control"
                            minlength="10"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            placeholder="Masukkan NIM 10 digit"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa</label>

                        <input
                            type="text"
                            name="NamaMahasiswa"
                            class="form-control"
                            placeholder="Masukkan nama mahasiswa"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Mahasiswa</label>

                        <input
                            type="file"
                            name="foto"
                            class="form-control"
                            accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai Akhir</label>

                        <input
                            type="number"
                            name="NilaiAkhir"
                            id="nilaiTambah"
                            class="form-control"
                            min="0"
                            max="100"
                            step="0.01"
                            placeholder="Contoh: 90.50"
                            required>
                    </div>

                    <div class="row">

                        <div class="col-6 mb-3">
                            <label class="form-label">Nilai Huruf</label>

                            <input
                                type="text"
                                id="hurufTambah"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Predikat</label>

                            <input
                                type="text"
                                id="predikatTambah"
                                class="form-control"
                                readonly>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Simpan Data
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


<!-- Modal Edit Data -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <form action="editnilai.php"
                  method="POST"
                  enctype="multipart/form-data">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Nilai</h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="NIM" id="editNIM">

                    <div class="mb-3">
                        <label class="form-label">NIM</label>

                        <input type="text"
                               id="editNIMShow"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Mahasiswa</label>

                        <input type="text"
                               name="namalengkap"
                               id="editNama"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ganti Foto (Opsional)</label>

                        <input type="file"
                               name="foto"
                               class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai Akhir</label>

                        <input type="number"
                               name="nilaiakhir"
                               id="editNilai"
                               class="form-control"
                               min="0"
                               max="100"
                               step="0.01"
                               required>
                    </div>

                    <div class="row">

                        <div class="col-6 mb-3">
                            <label class="form-label">Nilai Huruf</label>

                            <input type="text"
                                   id="editHuruf"
                                   class="form-control"
                                   readonly>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Predikat</label>

                            <input type="text"
                                   id="editPredikat"
                                   class="form-control"
                                   readonly>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-warning">
                        Perbarui
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


<!-- Modal Hapus Data -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <form action="hapusnilai.php" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">Hapus Data Mahasiswa</h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="NIM" id="hapusNIM">

                    <p class="mb-0">
                        Apakah kamu yakin ingin menghapus data mahasiswa
                        dengan NIM <strong id="hapusNIMText"></strong>?
                    </p>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Hapus Data
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
function hitungNilai(nilai) {
    let huruf = '';
    let predikat = '';

    if (nilai === '' || isNaN(nilai) || nilai < 0 || nilai > 100) {
        return { huruf: '', predikat: '' };
    }

    if (nilai <= 34.50) {
        huruf = 'E';
        predikat = 'Tidak Lulus';
    } else if (nilai <= 50.50) {
        huruf = 'D';
        predikat = 'Kurang Memuaskan';
    } else if (nilai <= 65.50) {
        huruf = 'C';
        predikat = 'Cukup Memuaskan';
    } else if (nilai <= 80.50) {
        huruf = 'B';
        predikat = 'Memuaskan';
    } else {
        huruf = 'A';
        predikat = 'Sangat Memuaskan';
    }

    return { huruf, predikat };
}


// Perhitungan nilai pada modal tambah
const nilaiTambah = document.getElementById('nilaiTambah');

nilaiTambah.addEventListener('input', function () {
    const hasil = hitungNilai(parseFloat(this.value));

    document.getElementById('hurufTambah').value = hasil.huruf;
    document.getElementById('predikatTambah').value = hasil.predikat;
});


// Modal edit
const modalEdit = document.getElementById('modalEdit');

modalEdit.addEventListener('show.bs.modal', function (event) {
    const tombol = event.relatedTarget;

    const nim = tombol.getAttribute('data-nim') || '';
    const nama = tombol.getAttribute('data-nama') || '';
    const nilai = tombol.getAttribute('data-nilai') || '';

    document.getElementById('editNIM').value = nim;
    document.getElementById('editNIMShow').value = nim;
    document.getElementById('editNama').value = nama;
    document.getElementById('editNilai').value = nilai;

    const hasil = hitungNilai(parseFloat(nilai));

    document.getElementById('editHuruf').value = hasil.huruf;
    document.getElementById('editPredikat').value = hasil.predikat;
});


// Perhitungan nilai saat edit
document.getElementById('editNilai').addEventListener('input', function () {
    const hasil = hitungNilai(parseFloat(this.value));

    document.getElementById('editHuruf').value = hasil.huruf;
    document.getElementById('editPredikat').value = hasil.predikat;
});


// Modal hapus
const modalHapus = document.getElementById('modalHapus');

modalHapus.addEventListener('show.bs.modal', function (event) {
    const tombol = event.relatedTarget;
    const nim = tombol.getAttribute('data-nim') || '';

    document.getElementById('hapusNIM').value = nim;
    document.getElementById('hapusNIMText').textContent = nim;
});
</script>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>