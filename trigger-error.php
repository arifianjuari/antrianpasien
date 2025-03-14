<?php
// File ini sengaja dibuat untuk memicu error 500 untuk pengujian PWA

// Memicu error fatal
function triggerError()
{
    // Memicu error dengan memanggil fungsi yang tidak ada
    non_existent_function();
}

// Jalankan fungsi untuk memicu error
triggerError();
