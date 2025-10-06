<?php

// app/Imports/CacheImport.php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // <--- PASTIKAN ADA INI

class CacheImport implements ToCollection, WithHeadingRow // <--- PASTIKAN IMPLEMENTASI INI
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // ... (biarkan kosong, data akan diakses di controller)
    }
}
