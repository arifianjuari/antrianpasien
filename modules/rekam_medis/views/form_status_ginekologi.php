<?php
// Pastikan variabel yang diperlukan tersedia
if (!isset($pasien)) {
    echo "Error: Data pasien tidak tersedia.";
    exit;
}

// Tentukan judul form berdasarkan mode (tambah/edit)
$judul = isset($statusGinekologi) ? 'Edit Status Ginekologi' : 'Tambah Status Ginekologi';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= $judul ?></h3>
                    <div class="card-tools">
                        <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $pasien['no_rkm_medis'] ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?module=rekam_medis&action=<?= isset($statusGinekologi) ? 'update_status_ginekologi' : 'simpan_status_ginekologi' ?>" method="POST">
                        <?php if (isset($statusGinekologi)): ?>
                            <input type="hidden" name="id_status_ginekologi" value="<?= $statusGinekologi['id_status_ginekologi'] ?>">
                        <?php endif; ?>
                        <input type="hidden" name="no_rkm_medis" value="<?= $pasien['no_rkm_medis'] ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="parturien">Parturien (Jumlah Kelahiran)</label>
                                    <input type="number" class="form-control" id="parturien" name="parturien"
                                        value="<?= isset($statusGinekologi) ? $statusGinekologi['Parturien'] : '0' ?>"
                                        min="0">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="abortus">Abortus (Jumlah Keguguran)</label>
                                    <input type="number" class="form-control" id="abortus" name="abortus"
                                        value="<?= isset($statusGinekologi) ? $statusGinekologi['Abortus'] : '0' ?>"
                                        min="0">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="hari_pertama_haid_terakhir">Hari Pertama Haid Terakhir</label>
                                    <input type="date" class="form-control" id="hari_pertama_haid_terakhir" name="hari_pertama_haid_terakhir"
                                        value="<?= isset($statusGinekologi) ? $statusGinekologi['Hari_pertama_haid_terakhir'] : '' ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="kontrasepsi_terakhir">Kontrasepsi Terakhir</label>
                                    <select class="form-control" id="kontrasepsi_terakhir" name="kontrasepsi_terakhir">
                                        <option value="">Pilih</option>
                                        <option value="Tidak Ada" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                                        <option value="Pil KB" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Pil KB' ? 'selected' : '' ?>>Pil KB</option>
                                        <option value="Suntik 1 Bulan" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Suntik 1 Bulan' ? 'selected' : '' ?>>Suntik 1 Bulan</option>
                                        <option value="Suntik 3 Bulan" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Suntik 3 Bulan' ? 'selected' : '' ?>>Suntik 3 Bulan</option>
                                        <option value="IUD/Spiral" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'IUD/Spiral' ? 'selected' : '' ?>>IUD/Spiral</option>
                                        <option value="Implan" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Implan' ? 'selected' : '' ?>>Implan</option>
                                        <option value="Kondom" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Kondom' ? 'selected' : '' ?>>Kondom</option>
                                        <option value="Sterilisasi" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Sterilisasi' ? 'selected' : '' ?>>Sterilisasi</option>
                                        <option value="Kalender" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Kalender' ? 'selected' : '' ?>>Kalender</option>
                                        <option value="Senggama Terputus" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Senggama Terputus' ? 'selected' : '' ?>>Senggama Terputus</option>
                                        <option value="Lainnya" <?= isset($statusGinekologi) && $statusGinekologi['Kontrasepsi_terakhir'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="lama_menikah_th">Lama Menikah (Tahun)</label>
                                    <input type="number" class="form-control" id="lama_menikah_th" name="lama_menikah_th"
                                        value="<?= isset($statusGinekologi) ? $statusGinekologi['lama_menikah_th'] : '0' ?>"
                                        min="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi form sebelum submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            // Validasi bisa ditambahkan sesuai kebutuhan
        });
    });
</script>