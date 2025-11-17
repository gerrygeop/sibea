<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class NimImport implements ToCollection
{
    public array $nims = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Ambil header di baris pertama
        $headers = $rows->first()->map(fn($h) => strtolower(trim($h)));

        // Cari index kolom nim
        $nimIndex = $headers->search('nim');

        if ($nimIndex === false) {
            throw new \Exception('Kolom "nim" tidak ditemukan');
        }

        $rows->shift(); // skip header

        // Ambil semua nim di bawah header
        foreach ($rows as $row) {
            $nim = trim($row[$nimIndex] ?? '');

            if (!empty($nim)) {
                $this->nims[] = $nim;
            }
        }
    }
}
