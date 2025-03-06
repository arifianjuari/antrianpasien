<?php

case 'generate_pdf':
    if (isset($_GET['no_rkm_medis'])) {
        $no_rkm_medis = $_GET['no_rkm_medis'];
        
        // Query untuk mendapatkan data pasien
        $query_pasien = "SELECT * FROM pasien WHERE no_rkm_medis = '$no_rkm_medis'";
        $pasien = mysqli_fetch_assoc(mysqli_query($conn, $query_pasien));
        
        // Query untuk status obstetri
        $query_obstetri = "SELECT * FROM status_obstetri WHERE no_rkm_medis = '$no_rkm_medis' ORDER BY created_at DESC";
        $statusObstetri = mysqli_fetch_all(mysqli_query($conn, $query_obstetri), MYSQLI_ASSOC);
        
        // Query untuk riwayat pemeriksaan
        $query_pemeriksaan = "SELECT * FROM pemeriksaan_ralan WHERE no_rkm_medis = '$no_rkm_medis' ORDER BY tgl_registrasi DESC";
        $riwayatPemeriksaan = mysqli_fetch_all(mysqli_query($conn, $query_pemeriksaan), MYSQLI_ASSOC);
        
        // Generate PDF
        require_once('modules/rekam_medis/generate_resume_pdf.php');
    }
    break; 