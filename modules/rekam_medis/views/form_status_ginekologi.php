<?php
// Pastikan variabel yang diperlukan tersedia
if (!isset($no_rkm_medis)) {
    echo "Error: Nomor rekam medis tidak tersedia.";
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Status Ginekologi</h3>
                    <div class="card-tools">
                        <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $no_rkm_medis ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="index.php?module=rekam_medis&action=simpan_status_ginekologi" method="post">
                        <input type="hidden" name="no_rkm_medis" value="<?= $no_rkm_medis ?>">

                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parturien</label>
                            <input type="number" name="parturien" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Abortus</label>
                            <input type="number" name="abortus" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hari Pertama Haid Terakhir</label>
                            <input type="date" name="hpht" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kontrasepsi Terakhir</label>
                            <select name="kontrasepsi" class="form-control">
                                <option value="Tidak Ada">Tidak Ada</option>
                                <option value="Pil KB">Pil KB</option>
                                <option value="Suntik KB">Suntik KB</option>
                                <option value="Spiral/IUD">Spiral/IUD</option>
                                <option value="Implant">Implant</option>
                                <option value="MOW">MOW</option>
                                <option value="MOP">MOP</option>
                                <option value="Kondom">Kondom</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lama Menikah (Tahun)</label>
                            <input type="number" name="lama_menikah" class="form-control" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="index.php?module=rekam_medis&action=detailPasien&no_rkm_medis=<?= $no_rkm_medis ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>