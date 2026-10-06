<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class MonthlyReportExport implements FromArray
{
    public function __construct(protected array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }
}