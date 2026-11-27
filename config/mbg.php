<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Opsi Kebijakan yang Dapat Diubah via Konfigurasi (Menunggu Keputusan PM)
    |--------------------------------------------------------------------------
    */
    'admin_lihat_operasional' => env('MBG_ADMIN_LIHAT_OPERASIONAL', true),

    'pagu' => [
        'kelola_roles' => ['administrator_sistem', 'akuntan'],
    ],

    'fitur_opsional' => [
        'kunci_akun' => true,
        // Kebijakan sandi standar (panjang min, huruf besar/kecil/angka, wajib ganti sandi sementara 24 jam).
        'kebijakan_sandi' => true,
        // Opsional di luar laporan: masa berlaku sandi berkala untuk sandi reguler (dinonaktifkan default).
        'kedaluwarsa_sandi_berkala' => false,
        'backup_manual' => true,
        'unduh_backup' => true,
        // Opsional di luar laporan: retensi arsip 30 hari & pembersihan otomatis (dinonaktifkan default).
        'retensi_arsip' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Path Utilitas Database (Backup & Restore)
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),
        'mysql_path' => env('MYSQL_PATH', 'mysql'),
        'retention_days' => 30,
        'schedule_time' => '02:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Matriks Hak Akses 7 Peran (Sumber Tunggal UI & Middleware)
    |--------------------------------------------------------------------------
    */
    'role_matrix_columns' => [
        'Pengguna & Data Master',
        'Perencanaan Menu',
        'Persediaan & Pemorsian',
        'Keamanan Pangan & APD',
        'Distribusi',
        'Keluhan',
        'Dashboard & Laporan',
    ],

    'role_matrix' => [
        'administrator_sistem' => [
            'label' => 'Administrator Sistem',
            'akses' => [
                'Pengguna & Data Master' => 'Kelola penuh',
                'Perencanaan Menu' => 'Lihat saja',
                'Persediaan & Pemorsian' => 'Lihat saja',
                'Keamanan Pangan & APD' => 'Lihat saja',
                'Distribusi' => 'Lihat saja',
                'Keluhan' => 'Lihat saja',
                'Dashboard & Laporan' => 'Lihat saja',
            ],
        ],
        'ahli_gizi' => [
            'label' => 'Ahli Gizi',
            'akses' => [
                'Pengguna & Data Master' => 'Lihat saja',
                'Perencanaan Menu' => 'Kelola penuh',
                'Persediaan & Pemorsian' => 'Kelola penuh',
                'Keamanan Pangan & APD' => 'Kelola penuh',
                'Distribusi' => 'Lihat saja',
                'Keluhan' => 'Lihat saja',
                'Dashboard & Laporan' => 'Lihat saja',
            ],
        ],
        'akuntan' => [
            'label' => 'Akuntan',
            'akses' => [
                'Pengguna & Data Master' => 'Kelola terbatas (Parameter PAGU)',
                'Perencanaan Menu' => 'Verifikasi',
                'Persediaan & Pemorsian' => 'Tidak ada akses',
                'Keamanan Pangan & APD' => 'Tidak ada akses',
                'Distribusi' => 'Tidak ada akses',
                'Keluhan' => 'Tidak ada akses',
                'Dashboard & Laporan' => 'Lihat saja',
            ],
        ],
        'kepala_dapur' => [
            'label' => 'Kepala Dapur',
            'akses' => [
                'Pengguna & Data Master' => 'Lihat saja',
                'Perencanaan Menu' => 'Setujui',
                'Persediaan & Pemorsian' => 'Lihat saja',
                'Keamanan Pangan & APD' => 'Kelola penuh (Tindak Lanjut APD saja)',
                'Distribusi' => 'Lihat saja',
                'Keluhan' => 'Lihat saja',
                'Dashboard & Laporan' => 'Lihat & ekspor',
            ],
        ],
        'asisten_lapangan' => [
            'label' => 'Asisten Lapangan',
            'akses' => [
                'Pengguna & Data Master' => 'Tidak ada akses',
                'Perencanaan Menu' => 'Tidak ada akses',
                'Persediaan & Pemorsian' => 'Kelola terbatas',
                'Keamanan Pangan & APD' => 'Tidak ada akses',
                'Distribusi' => 'Kelola penuh',
                'Keluhan' => 'Kelola terbatas',
                'Dashboard & Laporan' => 'Lihat & ekspor',
            ],
        ],
        'tim_distribusi' => [
            'label' => 'Tim Distribusi',
            'akses' => [
                'Pengguna & Data Master' => 'Tidak ada akses',
                'Perencanaan Menu' => 'Tidak ada akses',
                'Persediaan & Pemorsian' => 'Tidak ada akses',
                'Keamanan Pangan & APD' => 'Tidak ada akses',
                'Distribusi' => 'Kelola terbatas',
                'Keluhan' => 'Tidak ada akses',
                'Dashboard & Laporan' => 'Tidak ada akses',
            ],
        ],
        'guru_pic' => [
            'label' => 'Guru PIC / Sekolah & Posyandu',
            'akses' => [
                'Pengguna & Data Master' => 'Tidak ada akses',
                'Perencanaan Menu' => 'Lihat terbatas (menu harian lokasinya)',
                'Persediaan & Pemorsian' => 'Tidak ada akses',
                'Keamanan Pangan & APD' => 'Tidak ada akses',
                'Distribusi' => 'Kelola terbatas',
                'Keluhan' => 'Kelola terbatas',
                'Dashboard & Laporan' => 'Tidak ada akses',
            ],
        ],
    ],
];
