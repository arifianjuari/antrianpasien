<?php
// Pastikan variabel yang diperlukan tersedia
if (!isset($status_ginekologi)) {
    echo "Error: Data status ginekologi tidak tersedia.";
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Status Ginekologi</h3>
                    <div class="card-tools">
                        <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $status_ginekologi['no_rkm_medis'] ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="index.php?module=rekam_medis&action=update_status_ginekologi" method="post">
                        <input type="hidden" name="id_status_ginekologi" value="<?= $status_ginekologi['id_status_ginekologi'] ?>">
                        <input type="hidden" name="no_rkm_medis" value="<?= $status_ginekologi['no_rkm_medis'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Parturien</label>
                            <input type="number" name="parturien" class="form-control" value="<?= $status_ginekologi['Parturien'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Abortus</label>
                            <input type="number" name="abortus" class="form-control" value="<?= $status_ginekologi['Abortus'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hari Pertama Haid Terakhir</label>
                            <input type="date" name="hpht" class="form-control" value="<?= $status_ginekologi['Hari_pertama_haid_terakhir'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kontrasepsi Terakhir</label>
                            <select name="kontrasepsi" class="form-control">
                                <option value="Tidak Ada" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                                <option value="Pil KB" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Pil KB' ? 'selected' : '' ?>>Pil KB</option>
                                <option value="Suntik KB" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Suntik KB' ? 'selected' : '' ?>>Suntik KB</option>
                                <option value="Spiral/IUD" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Spiral/IUD' ? 'selected' : '' ?>>Spiral/IUD</option>
                                <option value="Implant" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Implant' ? 'selected' : '' ?>>Implant</option>
                                <option value="MOW" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'MOW' ? 'selected' : '' ?>>MOW</option>
                                <option value="MOP" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'MOP' ? 'selected' : '' ?>>MOP</option>
                                <option value="Kondom" <?= $status_ginekologi['Kontrasepsi_terakhir'] == 'Kondom' ? 'selected' : '' ?>>Kondom</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lama Menikah (Tahun)</label>
                            <input type="number" name="lama_menikah" class="form-control" value="<?= $status_ginekologi['lama_menikah_th'] ?>" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $status_ginekologi['no_rkm_medis'] ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>