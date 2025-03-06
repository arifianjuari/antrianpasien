-- Buat tabel status_ginekologi jika belum ada
CREATE TABLE IF NOT EXISTS status_ginekologi (
    id_status_ginekologi VARCHAR(36) PRIMARY KEY,
    no_rkm_medis VARCHAR(15) NOT NULL,
    tanggal_pemeriksaan DATE NOT NULL,
    menarche VARCHAR(50),
    siklus_haid VARCHAR(50),
    lama_haid VARCHAR(50),
    jumlah_pembalut INT,
    nyeri_haid VARCHAR(50),
    keputihan VARCHAR(50),
    kontrasepsi VARCHAR(100),
    riwayat_kb TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (no_rkm_medis) REFERENCES pasien(no_rkm_medis) ON DELETE CASCADE
); 