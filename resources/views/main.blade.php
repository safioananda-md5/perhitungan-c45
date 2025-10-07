@extends('layout')

@section('content')
    <h5>Input Data Excel</h5>

    @if (session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('excel.import.cache') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-6">
                <label for="file" class="form-label">Masukkan file excel<span style="color: red">*</span></label>
                <input class="form-control w-100" type="file" name="file" id="file" required>
            </div>
            <div class="col-6 d-flex align-items-end gap-3">
                <button type="submit" class="btn btn-success">Import</button>
                <a href="{{ route('clear.cache') }}" class="btn btn-danger">
                    Hapus Data
                </a>
            </div>
        </div>
    </form>
    <hr class="border border-3 border-dark">
    Jumlah Header: {{ $countheaders }}
    @if ($dataCollection->isEmpty())
        <p>Cache data kosong. Silakan unggah file Excel terlebih dahulu.</p>
    @else
        <p>Data berhasil dimuat dari cache. Total baris: {{ $dataCollection->count() }}</p>
        <table class="table table-hover">
            <thead>
                <tr>
                    {{-- Loop untuk menampilkan semua header kolom --}}
                    @foreach ($headers as $index => $header)
                        <th>{{ $index . ' ' . Str::title(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Loop untuk menampilkan data per baris --}}
                @foreach ($dataCollection as $row)
                    <tr>
                        {{-- Loop untuk menampilkan data per kolom di setiap baris --}}
                        @foreach ($headers as $header)
                            <td>{{ $row[$header] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (isset($headers))
        <p>Total Puas = {{ $countPuas1 }}</p>
        <p>Total Tidak Puas = {{ $countTidakPuas1 }}</p>
        <p>Total Kepuasan = {{ $countkepuasan1 }}</p>
        <p>Entropy S =
            @php
                if ($countkepuasan1 > 0) {
                    $p_puas = $countPuas1 / $countkepuasan1;
                    $p_tidakpuas = $countTidakPuas1 / $countkepuasan1;

                    // hindari log(0)
                    $entropyS = 0;
                    if ($p_puas > 0) {
                        $entropyS -= $p_puas * log($p_puas, 2);
                    }
                    if ($p_tidakpuas > 0) {
                        $entropyS -= $p_tidakpuas * log($p_tidakpuas, 2);
                    }
                } else {
                    $entropyS = 0;
                }
            @endphp
            {{ fmod($entropyS, 1) == 0 ? $entropyS : number_format($entropyS, 3) }}
        </p>

        <hr class="border border-3 border-dark mt-5">
        <h5>Tabel Perhitungan</h5>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th rowspan="2">Node</th>
                    <th rowspan="2">Kriteria</th>
                    <th rowspan="2">Value</th>
                    <th>Jumlah Kasus</th>
                    <th>Puas</th>
                    <th>Tidak Puas</th>
                    <th rowspan="2">Entropy</th>
                    <th rowspan="2">Information Gain</th>
                </tr>
                <tr>
                    <th>{{ $countkepuasan1 }}</th>
                    <th>{{ $countPuas1 }}</th>
                    <th>{{ $countTidakPuas1 }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($headers as $header)
                    @if (!in_array($header, ['usia', 'kepuasan']))
                        <tr>
                            <td rowspan="{{ $uniqueValues[$header]['count'] }}"></td>
                            <td rowspan="{{ $uniqueValues[$header]['count'] }}">{{ $header }}</td>
                            <td>{{ $uniqueValues[$header]['values'][0] }}</td>
                            <td>
                                @php
                                    $countdata = 0;
                                    foreach ($dataCollection as $row) {
                                        if ($row[$header] === $uniqueValues[$header]['values'][0]) {
                                            $countdata++;
                                        }
                                    }
                                @endphp
                                {{ $countdata }}
                            </td>
                            <td>
                                @php
                                    $countpuas = 0;
                                    foreach ($dataCollection as $row) {
                                        if (
                                            $row[$header] === $uniqueValues[$header]['values'][0] &&
                                            $row['kepuasan'] === 'Puas'
                                        ) {
                                            $countpuas++;
                                        }
                                    }
                                @endphp
                                {{ $countpuas }}
                            </td>
                            <td>
                                @php
                                    $counttidakpuas = 0;
                                    foreach ($dataCollection as $row) {
                                        if (
                                            $row[$header] === $uniqueValues[$header]['values'][0] &&
                                            $row['kepuasan'] === 'Tidak Puas'
                                        ) {
                                            $counttidakpuas++;
                                        }
                                    }
                                @endphp
                                {{ $counttidakpuas }}
                            </td>
                            <td>
                                @php
                                    if ($countdata > 0) {
                                        $p_puas = $countpuas / $countdata;
                                        $p_tidakpuas = $counttidakpuas / $countdata;

                                        // hindari log(0)
                                        $entropy = 0;
                                        if ($p_puas > 0) {
                                            $entropy -= $p_puas * log($p_puas, 2);
                                        }
                                        if ($p_tidakpuas > 0) {
                                            $entropy -= $p_tidakpuas * log($p_tidakpuas, 2);
                                        }
                                    } else {
                                        $entropy = 0;
                                    }
                                @endphp
                                {{ fmod($entropy, 1) == 0 ? $entropy : number_format($entropy, 3) }}
                            </td>
                            <td rowspan="{{ $uniqueValues[$header]['count'] }}">
                                @php
                                    $gain = 0;
                                    $gain = $entropyS;
                                    $rowMath = 0;
                                    foreach ($uniqueValues[$header]['values'] as $item) {
                                        $countdata = 0;
                                        foreach ($dataCollection as $row) {
                                            if ($row[$header] === $item) {
                                                $countdata++;
                                            }
                                        }

                                        $countpuas = 0;
                                        foreach ($dataCollection as $row) {
                                            if ($row[$header] === $item && $row['kepuasan'] === 'Puas') {
                                                $countpuas++;
                                            }
                                        }

                                        $counttidakpuas = 0;
                                        foreach ($dataCollection as $row) {
                                            if ($row[$header] === $item && $row['kepuasan'] === 'Tidak Puas') {
                                                $counttidakpuas++;
                                            }
                                        }

                                        if ($countdata > 0) {
                                            $p_puas = $countpuas / $countdata;
                                            $p_tidakpuas = $counttidakpuas / $countdata;

                                            // hindari log(0)
                                            $entropy = 0;
                                            if ($p_puas > 0) {
                                                $entropy -= $p_puas * log($p_puas, 2);
                                            }
                                            if ($p_tidakpuas > 0) {
                                                $entropy -= $p_tidakpuas * log($p_tidakpuas, 2);
                                            }
                                        } else {
                                            $entropy = 0;
                                        }

                                        $rowMath += ($countdata / $countkepuasan1) * $entropy;
                                    }

                                    $gain = $entropyS - $rowMath;
                                @endphp
                                {{ fmod($gain, 1) == 0 ? $gain : number_format($gain, 3) }}
                            </td>
                        </tr>
                        @foreach ($uniqueValues[$header]['values'] as $item)
                            @if ($loop->first)
                            @else
                                <tr>
                                    <td>{{ $item }}</td>
                                    <td>
                                        @php
                                            $countdata = 0;
                                            foreach ($dataCollection as $row) {
                                                if ($row[$header] === $item) {
                                                    $countdata++;
                                                }
                                            }
                                        @endphp
                                        {{ $countdata }}
                                    </td>
                                    <td>
                                        @php
                                            $countpuas = 0;
                                            foreach ($dataCollection as $row) {
                                                if ($row[$header] === $item && $row['kepuasan'] === 'Puas') {
                                                    $countpuas++;
                                                }
                                            }
                                        @endphp
                                        {{ $countpuas }}
                                    </td>
                                    <td>
                                        @php
                                            $counttidakpuas = 0;
                                            foreach ($dataCollection as $row) {
                                                if ($row[$header] === $item && $row['kepuasan'] === 'Tidak Puas') {
                                                    $counttidakpuas++;
                                                }
                                            }
                                        @endphp
                                        {{ $counttidakpuas }}
                                    </td>
                                    <td>
                                        @php
                                            if ($countdata > 0) {
                                                $p_puas = $countpuas / $countdata;
                                                $p_tidakpuas = $counttidakpuas / $countdata;

                                                // hindari log(0)
                                                $entropy = 0;
                                                if ($p_puas > 0) {
                                                    $entropy -= $p_puas * log($p_puas, 2);
                                                }
                                                if ($p_tidakpuas > 0) {
                                                    $entropy -= $p_tidakpuas * log($p_tidakpuas, 2);
                                                }
                                            } else {
                                                $entropy = 0;
                                            }
                                        @endphp
                                        {{ fmod($entropy, 1) == 0 ? $entropy : number_format($entropy, 3) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
