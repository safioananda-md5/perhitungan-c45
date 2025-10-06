<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    public function index()
    {
        $cacheKey = 'excel_data';
        $dataCollection = null;
        $headers = null;
        $uniqueValues = [];

        // Mengambil data dari cache. Jika tidak ada, kembalikan Collection kosong.
        $dataCollection = Cache::get($cacheKey, collect([]));

        if ($dataCollection->isNotEmpty()) {
            // Ambil semua kolom (header)
            $columns = array_keys($dataCollection->first()->toArray());

            foreach ($columns as $column) {
                $uniqueItems = $dataCollection
                    ->pluck($column)
                    ->unique()
                    ->values()
                    ->toArray();

                $uniqueValues[$column] = [
                    'values' => $uniqueItems,
                    'count'  => count($uniqueItems),
                ];
            }

            $headers = $dataCollection->isNotEmpty() ? array_keys($dataCollection->first()->toArray()) : [];
        } else {
            flash()->error('Data tidak ada didalam cache!');
        }

        $countPuas1 = 0;
        $countTidakPuas1 = 0;
        $countkepuasan1 = 0;
        foreach ($dataCollection as $DC) {
            if ($DC['kepuasan'] === 'Puas') {
                $countPuas1++;
            } elseif ($DC['kepuasan']    === 'Tidak Puas') {
                $countTidakPuas1++;
            }
            $countkepuasan1++;
        }

        if ($headers) {
            $countheaders = count($headers);
        } else {
            $countheaders = 0;
        }

        return view('main', compact('dataCollection', 'headers', 'countPuas1', 'countTidakPuas1', 'countkepuasan1', 'countheaders', 'uniqueValues'));
    }
}
