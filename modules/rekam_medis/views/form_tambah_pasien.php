<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah Pasien Baru</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="index.php?module=rekam_medis&action=simpan_pasien" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama_pasien" class="form-label">Nama Pasien <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" required>
                    </div>
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
                    </div>
                    <div class="col-md-6">
                        <label for="no_ktp" class="form-label">NIK</label>
                        <input type="text" class="form-control" id="no_ktp" name="no_ktp" maxlength="16">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="kd_kab" class="form-label">Kabupaten/Kota</label>
                        <select class="form-select" id="kd_kab" name="kd_kab">
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                            <?php foreach ($kabupaten as $kab): ?>
                                <option value="<?= $kab['kd_kab'] ?>"><?= $kab['nm_kab'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="kd_kec" class="form-label">Kecamatan</label>
                        <select class="form-select" id="kd_kec" name="kd_kec">
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach ($kecamatan as $kec): ?>
                                <option value="<?= $kec['kd_kec'] ?>"><?= $kec['nm_kec'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="kd_kel" class="form-label">Kelurahan/Desa</label>
                        <select class="form-select" id="kd_kel" name="kd_kel">
                            <option value="">-- Pilih Kelurahan/Desa --</option>
                            <?php foreach ($kelurahan as $kel): ?>
                                <option value="<?= $kel['kd_kel'] ?>"><?= $kel['nm_kel'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="no_tlp" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" id="no_tlp" name="no_tlp">
                    </div>
                    <div class="col-md-6">
                        <label for="pekerjaan" class="form-label">Pekerjaan</label>
                        <input type="text" class="form-control" id="pekerjaan" name="pekerjaan">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nm_ibu" class="form-label">Nama Ibu</label>
                        <input type="text" class="form-control" id="nm_ibu" name="nm_ibu">
                    </div>
                    <div class="col-md-6">
                        <label for="namakeluarga" class="form-label">Nama Keluarga</label>
                        <input type="text" class="form-control" id="namakeluarga" name="namakeluarga">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="kd_pj" class="form-label">Cara Bayar</label>
                    <select class="form-select" id="kd_pj" name="kd_pj">
                        <option value="">-- Pilih Cara Bayar --</option>
                        <?php foreach ($cara_bayar as $cb): ?>
                            <option value="<?= $cb['kd_pj'] ?>"><?= $cb['nm_pj'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="index.php?module=rekam_medis&action=data_pasien" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>