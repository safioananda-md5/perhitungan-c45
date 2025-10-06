<?php

namespace App\Http\Controllers;

use Exception;
use App\Imports\CacheImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;

class ExcelController extends Controller
{
    // --- Method 2: Memproses Unggah dan Menyimpan ke Cache ---
    public function importToCache(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $file = $request->file('file');

            // 2. Membaca File Excel ke dalam Collection
            // toCollection mengembalikan array dari Collection (untuk setiap sheet)
            $sheets = Excel::toCollection(new CacheImport, $file);

            // Ambil sheet pertama saja
            $dataCollection = $sheets->first();

            // 3. Menyimpan Data ke Cache
            // Kita gunakan kunci 'excel_data' dan simpan selama 60 menit (60 * 60 detik)
            $cacheKey = 'excel_data';
            $ttlSeconds = 3600; // Time To Live: 1 jam

            Cache::put($cacheKey, $dataCollection);
            return redirect()->back()->with('success', 'Data Excel berhasil diimpor dan disimpan di cache!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses data Excel: ' . $e->getMessage());
        }
    }

    // --- Method 3: Menampilkan Data dari Cache ---
    public function showCachedData()
    {
        $cacheKey = 'excel_data';

        // Mengambil data dari cache. Jika tidak ada, kembalikan Collection kosong.
        $dataCollection = Cache::get($cacheKey, collect([]));

        // Ambil header/kunci kolom dari baris pertama (jika data ada)
        $headers = $dataCollection->isNotEmpty() ? array_keys($dataCollection->first()->toArray()) : [];

        return view('excel.cached_data_table', compact('dataCollection', 'headers'));
    }
}
