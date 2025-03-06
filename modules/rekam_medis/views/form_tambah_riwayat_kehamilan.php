<?php
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan parameter no_rkm_medis ada
if (!isset($_GET['no_rkm_medis'])) {
    $_SESSION['error_message'] = "Nomor rekam medis tidak ditemukan";
    header("Location: index.php?module=rekam_medis&action=data_pasien");
    exit;
}

$no_rkm_medis = $_GET['no_rkm_medis'];
$page_title = "Tambah Riwayat Kehamilan";

// Siapkan konten untuk layout
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?= $page_title ?></h5>
                </div>
                <div class="card-body">
                    <form action="index.php?module=rekam_medis&action=simpan_riwayat_kehamilan" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="no_rkm_medis" value="<?= $no_rkm_medis ?>">

                        <div class="mb-3">
                            <label for="no_urut_kehamilan" class="form-label required-field">Urutan Kehamilan</label>
                            <input type="number" class="form-control" id="no_urut_kehamilan" name="no_urut_kehamilan" required min="1">
                            <div class="invalid-feedback">
                                Silakan masukkan urutan kehamilan.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status_kehamilan" class="form-label required-field">Status Kehamilan</label>
                            <select class="form-select" id="status_kehamilan" name="status_kehamilan" required>
                                <option value="" selected disabled>Pilih Status Kehamilan</option>
                                <option value="Sedang Hamil">Sedang Hamil</option>
                                <option value="Lahir Hidup">Lahir Hidup</option>
                                <option value="Lahir Mati">Lahir Mati</option>
                                <option value="Abortus">Abortus</option>
                                <option value="Ektopik">Ektopik</option>
                            </select>
                            <div class="invalid-feedback">
                                Silakan pilih status kehamilan.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="komplikasi_kehamilan" class="form-label">Komplikasi Kehamilan</label>
                            <textarea class="form-control" id="komplikasi_kehamilan" name="komplikasi_kehamilan" rows="3" placeholder="Masukkan komplikasi kehamilan jika ada"></textarea>
                        </div>

                        <!-- Fields for all status except "Sedang Hamil" -->
                        <div id="persalinanFields" style="display: none;">
                            <div class="mb-3">
                                <label for="tanggal_persalinan" class="form-label">Tanggal Persalinan</label>
                                <input type="date" class="form-control" id="tanggal_persalinan" name="tanggal_persalinan">
                            </div>

                            <div class="mb-3">
                                <label for="jenis_persalinan" class="form-label">Jenis Persalinan</label>
                                <select class="form-select" id="jenis_persalinan" name="jenis_persalinan">
                                    <option value="" selected disabled>Pilih Jenis Persalinan</option>
                                    <option value="Spontan">Spontan</option>
                                    <option value="Sectio Caesaria">Sectio Caesaria</option>
                                    <option value="Vakum">Vakum</option>
                                    <option value="Forceps">Forceps</option>
                                    <option value="Tidak Relevan">Tidak Relevan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="tempat_persalinan" class="form-label">Tempat Persalinan</label>
                                <select class="form-select" id="tempat_persalinan" name="tempat_persalinan">
                                    <option value="" selected disabled>Pilih Tempat Persalinan</option>
                                    <option value="Rumah Sakit">Rumah Sakit</option>
                                    <option value="Puskesmas">Puskesmas</option>
                                    <option value="Klinik">Klinik</option>
                                    <option value="Bidan">Bidan</option>
                                    <option value="Rumah">Rumah</option>
                                    <option value="Lainnya">Lainnya</option>
                                    <option value="Tidak Relevan">Tidak Relevan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="penolong_persalinan" class="form-label">Penolong Persalinan</label>
                                <select class="form-select" id="penolong_persalinan" name="penolong_persalinan">
                                    <option value="" selected disabled>Pilih Penolong Persalinan</option>
                                    <option value="Dokter SpOG">Dokter SpOG</option>
                                    <option value="Dokter Umum">Dokter Umum</option>
                                    <option value="Bidan">Bidan</option>
                                    <option value="Dukun">Dukun</option>
                                    <option value="Lainnya">Lainnya</option>
                                    <option value="Tidak Relevan">Tidak Relevan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="komplikasi_persalinan" class="form-label">Komplikasi Persalinan</label>
                                <textarea class="form-control" id="komplikasi_persalinan" name="komplikasi_persalinan" rows="3" placeholder="Masukkan komplikasi persalinan jika ada"></textarea>
                            </div>
                        </div>

                        <!-- Fields for "Lahir Hidup" and "Lahir Mati" -->
                        <div id="bayiFields" style="display: none;">
                            <div class="mb-3">
                                <label for="jenis_kelamin_anak" class="form-label">Jenis Kelamin Anak</label>
                                <select class="form-select" id="jenis_kelamin_anak" name="jenis_kelamin_anak">
                                    <option value="" selected disabled>Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                    <option value="Tidak Diketahui">Tidak Diketahui</option>
                                    <option value="Tidak Relevan">Tidak Relevan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="berat_badan_lahir" class="form-label">Berat Badan Lahir (kg)</label>
                                <input type="number" class="form-control" id="berat_badan_lahir" name="berat_badan_lahir" step="0.01" min="0" max="99.99" placeholder="Masukkan berat badan lahir dalam kilogram">
                            </div>

                            <div class="mb-3">
                                <label for="kondisi_lahir" class="form-label">Kondisi Lahir</label>
                                <select class="form-select" id="kondisi_lahir" name="kondisi_lahir">
                                    <option value="" selected disabled>Pilih Kondisi Lahir</option>
                                    <option value="Sehat">Sehat</option>
                                    <option value="Cacat">Cacat</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Meninggal">Meninggal</option>
                                    <option value="Tidak Relevan">Tidak Relevan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Masukkan catatan tambahan jika ada"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php?module=rekam_medis&action=detail_pasien&no_rkm_medis=<?= $no_rkm_medis ?>" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Tambahkan CSS khusus
$additional_css = "
    .required-field::after {
        content: ' *';
        color: red;
    }
";

// Tambahkan JavaScript khusus
$additional_js = "
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Toggle fields based on status kehamilan
    document.getElementById('status_kehamilan').addEventListener('change', function() {
        var status = this.value;
        var persalinanFields = document.getElementById('persalinanFields');
        var bayiFields = document.getElementById('bayiFields');
        
        if (status === 'Sedang Hamil') {
            persalinanFields.style.display = 'none';
            bayiFields.style.display = 'none';
        } else {
            persalinanFields.style.display = 'block';
            
            if (status === 'Lahir Hidup' || status === 'Lahir Mati') {
                bayiFields.style.display = 'block';
            } else {
                bayiFields.style.display = 'none';
            }
        }
    });
";

// Include layout
require_once __DIR__ . '/../../../template/layout.php';
?>