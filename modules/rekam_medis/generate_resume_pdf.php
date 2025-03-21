<?php
// Pastikan session sudah dimulai
session_start();

// Impor TCPDF
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

// Extend TCPDF
class ResumePDF extends TCPDF
{
    // Header
    public function Header()
    {
        // Set font
        $this->SetFont('helvetica', 'B', 14);

        // Title
        $this->Cell(0, 15, 'RESUME MEDIS PASIEN', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);

        // Subtitle
        $this->SetFont('helvetica', '', 12);
        $this->Cell(0, 15, 'Klinik Kandungan', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
    }

    // Footer
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

// Buat instance PDF
$pdf = new ResumePDF('P', 'mm', 'A4');

// Set informasi dokumen
$pdf->SetCreator('Sistem Rekam Medis');
$pdf->SetAuthor('Klinik Kandungan');
$pdf->SetTitle('Resume Medis - ' . $pasien['nm_pasien']);

// Set margin
$pdf->SetMargins(15, 40, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Tambah halaman
$pdf->AddPage();

// Set font default
$pdf->SetFont('helvetica', '', 10);

// Data Pasien
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'DATA PRIBADI', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Buat tabel informasi pasien
$pdf->Cell(50, 7, 'No. Rekam Medis', 0);
$pdf->Cell(5, 7, ':', 0);
$pdf->Cell(0, 7, $pasien['no_rkm_medis'], 0, 1);

$pdf->Cell(50, 7, 'Nama Lengkap', 0);
$pdf->Cell(5, 7, ':', 0);
$pdf->Cell(0, 7, $pasien['nm_pasien'], 0, 1);

$pdf->Cell(50, 7, 'Tempat, Tgl Lahir', 0);
$pdf->Cell(5, 7, ':', 0);
$pdf->Cell(0, 7, $pasien['tmp_lahir'] . ', ' . date('d-m-Y', strtotime($pasien['tgl_lahir'])), 0, 1);

$pdf->Cell(50, 7, 'Umur', 0);
$pdf->Cell(5, 7, ':', 0);
$pdf->Cell(0, 7, $pasien['umur'] . ' tahun', 0, 1);

$pdf->Cell(50, 7, 'Alamat', 0);
$pdf->Cell(5, 7, ':', 0);
$pdf->MultiCell(0, 7, $pasien['alamat'], 0, 'L');

// Status Obstetri
if (!empty($statusObstetri)) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'STATUS OBSTETRI', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    foreach ($statusObstetri as $so) {
        $pdf->Cell(50, 7, 'G-P-A', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->Cell(0, 7, $so['gravida'] . '-' . $so['paritas'] . '-' . $so['abortus'], 0, 1);

        $pdf->Cell(50, 7, 'HPHT', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->Cell(0, 7, !empty($so['tanggal_hpht']) ? date('d-m-Y', strtotime($so['tanggal_hpht'])) : '-', 0, 1);

        $pdf->Cell(50, 7, 'TP', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->Cell(0, 7, !empty($so['tanggal_tp']) ? date('d-m-Y', strtotime($so['tanggal_tp'])) : '-', 0, 1);

        if (!empty($so['faktor_risiko_umum']) || !empty($so['faktor_risiko_obstetri'])) {
            $pdf->Cell(50, 7, 'Faktor Risiko', 0);
            $pdf->Cell(5, 7, ':', 0);
            $risiko = [];
            if (!empty($so['faktor_risiko_umum'])) $risiko[] = 'Umum: ' . $so['faktor_risiko_umum'];
            if (!empty($so['faktor_risiko_obstetri'])) $risiko[] = 'Obstetri: ' . $so['faktor_risiko_obstetri'];
            $pdf->MultiCell(0, 7, implode("\n", $risiko), 0, 'L');
        }
    }
}

// Riwayat Kunjungan Terakhir
if (!empty($riwayatPemeriksaan)) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'RIWAYAT KUNJUNGAN TERAKHIR', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);

    $lastVisit = $riwayatPemeriksaan[0];

    $pdf->Cell(50, 7, 'Tanggal Kunjungan', 0);
    $pdf->Cell(5, 7, ':', 0);
    $pdf->Cell(0, 7, date('d-m-Y', strtotime($lastVisit['tgl_registrasi'])), 0, 1);

    if (!empty($lastVisit['keluhan_utama'])) {
        $pdf->Cell(50, 7, 'Keluhan Utama', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->MultiCell(0, 7, $lastVisit['keluhan_utama'], 0, 'L');
    }

    if (!empty($lastVisit['diagnosis'])) {
        $pdf->Cell(50, 7, 'Diagnosis', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->MultiCell(0, 7, $lastVisit['diagnosis'], 0, 'L');
    }

    if (!empty($lastVisit['tata'])) {
        $pdf->Cell(50, 7, 'Tatalaksana', 0);
        $pdf->Cell(5, 7, ':', 0);
        $pdf->MultiCell(0, 7, $lastVisit['tata'], 0, 'L');
    }
}

// Tanda tangan
$pdf->Ln(20);
$pdf->Cell(120);
$pdf->Cell(0, 5, 'Dokter Pemeriksa,', 0, 1, 'L');
$pdf->Ln(15);
$pdf->Cell(120);
$pdf->Cell(0, 5, 'dr. ............................', 0, 1, 'L');

// Output PDF
$pdf->Output('resume_medis_' . $pasien['no_rkm_medis'] . '.pdf', 'I');
