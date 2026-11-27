<?php

return [
    'accepted' => ':attribute harus diterima.',
    'boolean' => ':attribute harus berupa nilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'file' => ':attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah terdaftar.',
    'attributes' => [
        'name' => 'Nama lengkap',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Kata sandi',
        'role' => 'Peran',
        'location_id' => 'Lokasi',
        'recipient_category_id' => 'Kategori penerima',
        'target_portions' => 'Target porsi',
        'max_budget_per_portion' => 'Batas PAGU per porsi',
    ],
];
