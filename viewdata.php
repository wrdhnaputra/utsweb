<?php
require_once __DIR__ . "/connect.php";

// Filter dan parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$predikat_filter = isset($_GET['predikat']) ? trim($_GET['predikat']) : 'all';

$sort_order = (
    isset($_GET['sort']) &&
    strtolower($_GET['sort']) == 'asc'
) ? 'ASC' : 'DESC';

$limit = isset($_GET['limit']) &&
    in_array((int)$_GET['limit'], [5, 10, 15, 29])
    ? (int)$_GET['limit'] : 29;

$page = isset($_GET['page']) && (int)$_GET['page'] > 0
    ? (int)$_GET['page'] : 1;

// Kondisi pencarian
$whereClauses = [];

if ($search !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "NamaMahasiswa LIKE '%$search_escaped%'";
}

if ($predikat_filter !== 'all' && $predikat_filter !== '') {
    $predikat_escaped = mysqli_real_escape_string($conn, $predikat_filter);
    $whereClauses[] = "LOWER(Predikat) = LOWER('$predikat_escaped')";
}

$whereSql = count($whereClauses) > 0
    ? "WHERE " . implode(" AND ", $whereClauses)
    : "";

// Hitung total data
$sql_count = "SELECT COUNT(*) AS total
              FROM nilaimahasiswa $whereSql";

$res_count = mysqli_query($conn, $sql_count);

if (!$res_count) {
    die("Gagal menghitung data: " . mysqli_error($conn));
}

$row_count = mysqli_fetch_assoc($res_count);
$total_data = (int)$row_count['total'];

// Pagination
$total_pages = (int)ceil($total_data / $limit);
$page = min($page, max(1, $total_pages));
$offset = ($page - 1) * $limit;

// Ambil data dan urutkan nilai
$sql = "SELECT * FROM nilaimahasiswa
        $whereSql
        ORDER BY NilaiAkhir $sort_order
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Rekap Nilai Mahasiswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="./css/adminlte.css" />
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-6">
              <h1 class="mb-0 fs-3">Rekap Nilai Mahasiswa</h1>
            </div>
          </div>
        </div>
      </div>
      
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header">
                  <!-- Form Fitur Filtering, Searching, Sorting & Limit -->
                  <form method="GET" action="viewdata.php" id="filterForm">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-lg-3">
                        <h3 class="card-title">Directory Nilai</h3>
                      </div>
                      <div class="col-12 col-lg-9">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2 align-items-center">
                          
                          <!-- Search Nama -->
                          <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="search" class="form-control" placeholder="Cari Nama..." value="<?php echo htmlspecialchars($search); ?>" style="width: 150px" />
                          </div>

                          <!-- Filter Predikat -->
                          <select name="predikat" class="form-select form-select-sm w-auto">
                            <option value="all" <?php echo ($predikat_filter == 'all') ? 'selected' : ''; ?>>Semua Predikat</option>
                            <option value="Sangat Memuaskan" <?php echo (strtolower($predikat_filter) == 'sangat memuaskan') ? 'selected' : ''; ?>>Sangat Memuaskan</option>
                            <option value="Memuaskan" <?php echo (strtolower($predikat_filter) == 'memuaskan') ? 'selected' : ''; ?>>Memuaskan</option>
                            <option value="Cukup Memuaskan" <?php echo (strtolower($predikat_filter) == 'cukup memuaskan') ? 'selected' : ''; ?>>Cukup Memuaskan</option>
                            <option value="Kurang Memuaskan" <?php echo (strtolower($predikat_filter) == 'kurang memuaskan') ? 'selected' : ''; ?>>Kurang Memuaskan</option>
                            <option value="Tidak Lulus" <?php echo (strtolower($predikat_filter) == 'tidak lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                          </select>

                          <!-- Sort Urutan Nilai -->
                          <select name="sort" class="form-select form-select-sm w-auto">
                            <option value="desc" <?php echo ($sort_order == 'DESC') ? 'selected' : ''; ?>>Nilai: Tinggi - Rendah</option>
                            <option value="asc" <?php echo ($sort_order == 'ASC') ? 'selected' : ''; ?>>Nilai: Rendah - Tinggi</option>
                          </select>

                          <!-- Limit Per Page -->
                          <select name="limit" class="form-select form-select-sm w-auto">
                            <option value="5" <?php echo ($limit == 5) ? 'selected' : ''; ?>>5 / hal</option>
                            <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10 / hal</option>
                            <option value="15" <?php echo ($limit == 15) ? 'selected' : ''; ?>>14/ hal</option>
                            <option value="29" <?php echo ($limit == 29) ? 'selected' : ''; ?>> 29 / hal</option>
                          </select>

                          <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-filter"></i> Terapkan</button>

                          <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-user">
                            <i class="bi bi-person-plus-fill me-1"></i> Tambah Data
                          </button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Table Body -->
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
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
                          <th>Aksi</th>
                      </tr>
                  </thead>
                      <tbody>
                        <?php if (mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) { 
                        ?>
                          <tr>
                            <td><?= htmlspecialchars($row['NIM']) ?></td>

                            <td><?= htmlspecialchars($row['NamaMahasiswa']) ?></td>

                            <td>
                                <?php
                                $fotoPath = !empty($row['foto']) &&
                                            file_exists(__DIR__ . '/uploads/' . $row['foto'])
                                            ? 'uploads/' . $row['foto']
                                            : '';
                                ?>

                                <?php if ($fotoPath !== ''): ?>
                                    <img
                                        src="<?= htmlspecialchars($fotoPath) ?>"
                                        alt="Foto mahasiswa"
                                        width="45"
                                        height="45"
                                        style="object-fit: cover; border-radius: 50%;"
                                    >
                                <?php else: ?>
                                    Tidak ada foto
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($row['NilaiAkhir']) ?></td>
                            <td><?= htmlspecialchars($row['NilaiHuruf']) ?></td>
                            <td><?= htmlspecialchars($row['Predikat']) ?></td>
                            <td><?= htmlspecialchars($row['created_date']) ?></td>
                            <td><?= htmlspecialchars($row['modified_date']) ?></td>

                            <!-- Tombol Edit dan Hapus tetap di kolom Aksi -->
                            <td class="text-end">
                                  <div class="btn-group btn-group-sm">
                                      <button type="button"
                                              class="btn btn-outline-secondary"
                                              data-bs-toggle="modal"
                                              data-bs-target="#modalEdit"
                                              data-nim="<?= htmlspecialchars($row['NIM'], ENT_QUOTES) ?>"
                                              data-nama="<?= htmlspecialchars($row['NamaMahasiswa'], ENT_QUOTES) ?>"
                                              data-nilai="<?= htmlspecialchars($row['NilaiAkhir'], ENT_QUOTES) ?>">
                                          <i class="bi bi-pencil"></i>
                                      </button>

                                      <button type="button"
                                              class="btn btn-outline-danger"
                                              data-bs-toggle="modal"
                                              data-bs-target="#modal-delete-user"
                                              data-nim="<?= htmlspecialchars($row['NIM'], ENT_QUOTES) ?>">
                                          <i class="bi bi-trash"></i>
                                      </button>
                                  </div>
                             </td>
                          </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                              <td colspan="9" class="text-center py-4">Data tidak ditemukan!</td>
                            </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Footer Pagination -->
                <div class="card-footer clearfix">
                  <div class="float-start pt-1 fs-7 text-body-secondary">
                    Menampilkan <?php echo ($total_data > 0) ? ($offset + 1) : 0; ?> - <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> entri
                  </div>
                  <?php if ($total_pages > 1): ?>
                    <ul class="pagination pagination-sm m-0 float-end">
                      <!-- Prev -->
                      <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&sort=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>">&laquo;</a>
                      </li>

                      <!-- Page Numbers -->
                      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                          <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&sort=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a>
                        </li>
                      <?php endfor; ?>

                      <!-- Next -->
                      <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&sort=<?php echo $sort_order; ?>&limit=<?php echo $limit; ?>">&raquo;</a>
                      </li>
                    </ul>
                  <?php endif; ?>
                </div>

              </div>
            </div>
          </div>

          <!-- Modal Tambah Data -->
          <div class="modal fade" id="modal-add-user" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="viewdata_nilai.php" method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="NilaiHuruf" id="add_hidden_huruf">
                  <input type="hidden" name="predikat" id="add_hidden_predikat">
                  <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">NIM</label>
                      <input type="text" minlength="10" maxlength="10" placeholder="Contoh: 3xxxx16xxx" class="form-control" name="NIM" required />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nama Lengkap</label>
                      <input type="text" class="form-control" id="NamaMahasiswa" name="NamaMahasiswa" placeholder="Contoh: Ika Arynita" required />
                    </div>
                    <!-- Input Upload Foto -->
                    <div class="mb-3">
                      <label class="form-label">Upload Foto</label>
                      <input type="file" class="form-control" name="foto" accept="image/*" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nilai Akhir</label>
                      <input type="number" step="0.01" class="form-control" id="NilaiAkhir" name="NilaiAkhir"  placeholder="Contoh Nilai: 90.50" min="0.00" max="100.00" required />
                    </div>
                    <div class="row mb-3">
                      <div class="col-6">
                        <label class="form-label">Nilai Huruf</label>
                        <input type="text" id="add_NilaiHuruf" class="form-control" readonly />
                      </div>
                      <div class="col-6">
                        <label class="form-label">Predikat</label>
                        <input type="text" id="add_predikat" class="form-control" readonly />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal Edit Data -->
          <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h5 class="modal-title">Edit Data Nilai</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="editnilai.php" method="POST" enctype="multipart/form-data">
                          <div class="modal-body">
                              <input type="hidden" name="NIM" id="edit_NIM">
                              <div class="mb-3">
                              <label class="form-label">Nilai Akhir</label>
                              <input type="number" step="0.01" class="form-control"
                                    id="NilaiAkhir" name="NilaiAkhir"
                                    placeholder="Contoh Nilai: 90.50"
                                    min="0.00" max="100.00" required />

                              <small class="form-text text-muted">
                                Contoh : 90.50
                              </small>
                            </div>
                              <div class="mb-3">
                                  <label class="form-label">Nama Lengkap</label>
                                  <input type="text" name="namalengkap" id="edit_nama" class="form-control" required>
                              </div>
                              <!-- Input Update Foto -->
                              <div class="mb-3">
                                  <label class="form-label">Ganti Foto (Opsional)</label>
                                  <input type="file" name="foto" class="form-control" accept="image/*">
                              </div>
                              <div class="mb-3">
                                  <label class="form-label">Nilai Akhir</label>
                                  <input type="number" step="0.01" name="nilaiakhir" id="edit_nilai" min="0" max="100" class="form-control" required>
                              </div>
                              <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label">Nilai Huruf</label>
                                    <input type="text" id="edit_NilaiHuruf" class="form-control" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Predikat</label>
                                    <input type="text" id="edit_predikat" class="form-control" readonly>
                                </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-warning">Perbarui</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>

          <!-- Modal Hapus -->
          <div class="modal fade" id="modal-delete-user" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <form action="hapusnilai.php" method="POST">
                          <div class="modal-header">
                              <h5 class="modal-title">Hapus Data</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                              <input type="hidden" name="NIM" id="delete_NIM">
                              <p class="mb-0">Apakah Anda yakin ingin menghapus data dengan NIM <strong id="delete_nim_text"></strong>?</p>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-danger">Hapus Data</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
  <script>
  function hitungKonversi(nilai) {
    let huruf = '';
    let predikat = '';

    if (nilai > 80.50 && nilai <= 100) {
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

  document.addEventListener('DOMContentLoaded', function () {
    // Live calculate Tambah Data
    const inputNilaiAdd = document.getElementById('NilaiAkhir');
    if (inputNilaiAdd) {
      inputNilaiAdd.addEventListener('input', function () {
        const nilai = parseFloat(this.value) || 0;
        const hasil = hitungKonversi(nilai);
        document.getElementById('add_NilaiHuruf').value = hasil.huruf;
        document.getElementById('add_predikat').value = hasil.predikat;
        document.getElementById('add_hidden_huruf').value = hasil.huruf;
        document.getElementById('add_hidden_predikat').value = hasil.predikat;
      });
    }

    // Modal Edit
    const modalEdit = document.getElementById('modalEdit');
    if (modalEdit) {
      modalEdit.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const NIM = button.getAttribute('data-nim') || '';
        const nama = button.getAttribute('data-nama') || '';
        const nilai = button.getAttribute('data-nilai') || '0';
        
        modalEdit.querySelector('#edit_NIM').value = NIM;
        modalEdit.querySelector('#edit_NIM_show').value = NIM;
        modalEdit.querySelector('#edit_nama').value = nama;
        modalEdit.querySelector('#edit_nilai').value = nilai;

        const hasil = hitungKonversi(parseFloat(nilai) || 0);
        modalEdit.querySelector('#edit_NilaiHuruf').value = hasil.huruf;
        modalEdit.querySelector('#edit_predikat').value = hasil.predikat;
      });

      const inputNilaiEdit = document.getElementById('edit_nilai');
      if (inputNilaiEdit) {
        inputNilaiEdit.addEventListener('input', function () {
          const nilai = parseFloat(this.value) || 0;
          const hasil = hitungKonversi(nilai);
          document.getElementById('edit_NilaiHuruf').value = hasil.huruf;
          document.getElementById('edit_predikat').value = hasil.predikat;
        });
      }
    }

    // Modal Delete
    const modalDelete = document.getElementById('modal-delete-user');
    if (modalDelete) {
      modalDelete.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const NIM = button.getAttribute('data-nim') || '';
        modalDelete.querySelector('#delete_NIM').value = NIM;
        modalDelete.querySelector('#delete_nim_text').textContent = NIM;
      });
    }
  });
  </script>
</body>
</html>