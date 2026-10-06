<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MonthlyReportExport implements FromArray, ShouldAutoSize, WithEvents
{
    public function __construct(protected array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $headerRange = 'A1:L1';
                $fullRange = 'A1:' . $highestColumn . $highestRow;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter($headerRange);

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle($fullRange)->getAlignment()->setVertical('top');
                $sheet->getStyle($fullRange)->getAlignment()->setWrapText(true);

                $sheet->getRowDimension(1)->setRowHeight(28);

                foreach (range(2, $highestRow) as $row) {
                    $status = trim((string) $sheet->getCell('E' . $row)->getValue());
                    $aiStatus = trim((string) $sheet->getCell('I' . $row)->getValue());

                    if ($status === 'Не загружено') {
                        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => 'solid',
                                'color' => ['rgb' => 'FDE9E7'],
                            ],
                        ]);
                    }

                    if ($status === 'Загружено') {
                        $sheet->getStyle('E' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '008000'],
                            ],
                        ]);
                    }

                    if ($status === 'Не загружено') {
                        $sheet->getStyle('E' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'C00000'],
                            ],
                        ]);
                    }

                    if ($aiStatus === 'Ошибка') {
                        $sheet->getStyle('I' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'C00000'],
                            ],
                        ]);
                    }

                    if ($aiStatus === 'Готово') {
                        $sheet->getStyle('I' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '008000'],
                            ],
                        ]);
                    }

                    if ($aiStatus === 'Анализируется' || $aiStatus === 'В очереди') {
                        $sheet->getStyle('I' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '9E7D0A'],
                            ],
                        ]);
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(24);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(22);
                $sheet->getColumnDimension('I')->setWidth(18);
                $sheet->getColumnDimension('J')->setWidth(40);
                $sheet->getColumnDimension('K')->setWidth(40);
                $sheet->getColumnDimension('L')->setWidth(60);
            },
        ];
    }
}