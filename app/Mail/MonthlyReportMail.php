<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $companyName,
        public string $month,
        public string $filePath
    ) {
    }

    public function build()
    {
        return $this->subject('Ежемесячный экологический отчет за ' . $this->month)
            ->view('emails.monthly-report')
            ->attach($this->filePath, [
                'as' => 'monthly-report-' . $this->month . '.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}