<?php
require_once 'config.php';

$discharge_date = isset($_GET['discharge_date']) ? $_GET['discharge_date'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    $query = "SELECT ran.no_rawat as no_rawat, pasien.nm_pasien as nama,
            bgl.nm_bangsal as bgsl, bgl.kd_bangsal as kdbgs, pasien.alamat as alamat, reg.p_jawab as pj,
            kel.nm_kel as kel, kec.nm_kec as kec, kab.nm_kab as kab, prop.nm_prop as prov
            FROM kamar_inap as ran
            INNER JOIN reg_periksa as reg ON ran.no_rawat = reg.no_rawat
            INNER JOIN pasien as pasien ON pasien.no_rkm_medis = reg.no_rkm_medis
            INNER JOIN kamar as kmr ON ran.kd_kamar = kmr.kd_kamar
            INNER JOIN bangsal as bgl ON bgl.kd_bangsal = kmr.kd_bangsal
            INNER JOIN kelurahan as kel ON pasien.kd_kel = kel.kd_kel
            INNER JOIN kecamatan as kec ON pasien.kd_kec = kec.kd_kec
            INNER JOIN kabupaten as kab ON pasien.kd_kab = kab.kd_kab
            INNER JOIN propinsi as prop ON pasien.kd_prop = prop.kd_prop
            WHERE tgl_keluar = :discharge_date AND stts_pulang = '-'";
    
    if ($search) {
        $query .= " AND (pasien.nm_pasien LIKE :search OR ran.no_rawat LIKE :search OR bgl.nm_bangsal LIKE :search)";
    }
    
    $query .= " ORDER BY bgl.nm_bangsal";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':discharge_date', $discharge_date, PDO::PARAM_STR);
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Patient List</h2>
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <form class="d-flex" method="GET">
                    <input type="date" name="discharge_date" value="<?php echo htmlspecialchars($discharge_date); ?>" class="form-control me-2">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." class="form-control me-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No Rawat</th>
                        <th>Nama Pasien</th>
                        <th>Bangsal</th>
                        <th>Kode Bangsal</th>
                        <th>Alamat</th>
                        <th>Penanggung Jawab</th>
                        <th>Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Kabupaten</th>
                        <th>Provinsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($patients) > 0): ?>
                        <?php foreach($patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['no_rawat']); ?></td>
                                <td><?php echo htmlspecialchars($patient['nama']); ?></td>
                                <td><?php echo htmlspecialchars($patient['bgsl']); ?></td>
                                <td><?php echo htmlspecialchars($patient['kdbgs']); ?></td>
                                <td><?php echo htmlspecialchars($patient['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($patient['pj']); ?></td>
                                <td><?php echo htmlspecialchars($patient['kel']); ?></td>
                                <td><?php echo htmlspecialchars($patient['kec']); ?></td>
                                <td><?php echo htmlspecialchars($patient['kab']); ?></td>
                                <td><?php echo htmlspecialchars($patient['prov']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No patients found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>