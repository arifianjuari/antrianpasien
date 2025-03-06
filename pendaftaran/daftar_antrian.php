<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Daftar Antrian Pasien</h2>
        <div>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterSection">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </div>
    </div>

    <div class="collapse show" id="filterSection">
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="hari" class="form-label">Hari</label>
                        <select class="form-select" id="hari" name="hari">
                            <option value="">Semua Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tempat" class="form-label">Tempat Praktek</label>
                        <select class="form-select" id="tempat" name="tempat">
                            <option value="">Semua Tempat</option>
                            <?php foreach ($tempat_praktek as $tp): ?>
                                <option value="<?= htmlspecialchars($tp['ID_Tempat_Praktek']) ?>">
                                    <?= htmlspecialchars($tp['Nama_Tempat']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="dokter" class="form-label">Dokter</label>
                        <select class="form-select" id="dokter" name="dokter">
                            <option value="">Semua Dokter</option>
                            <?php foreach ($dokter as $d): ?>
                                <option value="<?= htmlspecialchars($d['ID_Dokter']) ?>">
                                    <?= htmlspecialchars($d['Nama_Dokter']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (empty($antrian)): ?>
        <div class="alert alert-info">
            Tidak ada data antrian untuk filter yang dipilih.
        </div>
    <?php else: ?>
        <!-- Tabel antrian -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Nama Pasien</th>
                        <th>Hari</th>
                        <th>Jadwal</th>
                        <th>Tempat Praktek</th>
                        <th>Dokter</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($antrian as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['ID_Pendaftaran']) ?></td>
                            <td><?= htmlspecialchars($a['nm_pasien']) ?></td>
                            <td><?= htmlspecialchars($a['Hari']) ?></td>
                            <td><?= htmlspecialchars($a['Jam_Mulai']) ?> - <?= htmlspecialchars($a['Jam_Selesai']) ?></td>
                            <td><?= htmlspecialchars($a['Nama_Tempat']) ?></td>
                            <td><?= htmlspecialchars($a['Nama_Dokter']) ?></td>
                            <td><?= htmlspecialchars($a['Status_Pendaftaran']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set nilai filter dari URL jika ada
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('hari')) {
            document.getElementById('hari').value = urlParams.get('hari');
        }
        if (urlParams.has('tempat')) {
            document.getElementById('tempat').value = urlParams.get('tempat');
        }
        if (urlParams.has('dokter')) {
            document.getElementById('dokter').value = urlParams.get('dokter');
        }
    });
</script>

<?php
// Query untuk mengambil data antrian
$query = "
    SELECT 
        p.ID_Pendaftaran,
        p.nm_pasien,
        jr.Hari,
        jr.Jam_Mulai,
        jr.Jam_Selesai,
        tp.Nama_Tempat,
        d.Nama_Dokter,
        p.Status_Pendaftaran
    FROM 
        pendaftaran p
    JOIN 
        jadwal_rutin jr ON p.ID_Jadwal = jr.ID_Jadwal_Rutin
    JOIN 
        tempat_praktek tp ON p.ID_Tempat_Praktek = tp.ID_Tempat_Praktek
    JOIN 
        dokter d ON p.ID_Dokter = d.ID_Dokter
    WHERE 1=1
";

// Tambahkan filter jika ada
$params = [];

if (!empty($_GET['hari'])) {
    $query .= " AND jr.Hari = :hari";
    $params[':hari'] = $_GET['hari'];
}

if (!empty($_GET['tempat'])) {
    $query .= " AND p.ID_Tempat_Praktek = :tempat";
    $params[':tempat'] = $_GET['tempat'];
}

if (!empty($_GET['dokter'])) {
    $query .= " AND p.ID_Dokter = :dokter";
    $params[':dokter'] = $_GET['dokter'];
}

$query .= " ORDER BY jr.Hari ASC, jr.Jam_Mulai ASC";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$antrian = $stmt->fetchAll(PDO::FETCH_ASSOC);
