<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

readonly class SubmissionsExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    /**
     * @param Collection $rows
     * @param int        $totalRsvps
     */
    public function __construct(
        private readonly Collection $rows,
        private readonly int $totalRsvps
    )
    {
        //
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'ID',
            'Registered At',
            'Dealer',
            'Event ID',
            'Full Name',
            'Email',
            'Phone',
            'Guests',
            'Wants Appointment',
            'Event Date',
            'Vehicle Purchased',
            'Notes',
        ];
    }

    /**
     * @return mixed
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $dataCount = $this->rows->count();
                $totalRow = $dataCount + 2;

                $sheet->setCellValue("A{$totalRow}", 'Total RSVPs:');
                $sheet->setCellValue("B{$totalRow}", $this->totalRsvps);
            },
        ];
    }
}
