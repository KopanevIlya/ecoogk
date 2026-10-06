<?php

namespace App\Console\Commands;

use App\Exports\MonthlyReportExport;
use App\Mail\MonthlyReportMail;
use App\Models\Company;
use App\Services\MonthlyReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class SendMonthlyReportsCommand extends Command
{
    protected $signature = 'reports:send-monthly {month?}';
    protected $description = 'Send monthly reports to company emails';

    public function handle(MonthlyReportService $service): int
    {
        $month = $this->argument('month') ?: now()->subMonth()->format('Y-m');

        $companies = Company::query()
            ->where('active', true)
            ->whereNotNull('report_email')
            ->orderBy('name')
            ->get();

        foreach ($companies as $company) {
            $data = $service->build($month, $company->id);
            $exportRows = $service->makeExportRows($data['rows']);

            $fileName = 'monthly-report-' . $month . '-company-' . $company->id . '.xlsx';
            $filePath = storage_path('app/' . $fileName);

            Excel::store(new MonthlyReportExport($exportRows), $fileName);

            Mail::to($company->report_email)->send(
                new MonthlyReportMail($company->name, $month, $filePath)
            );

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->info('Sent: ' . $company->name . ' -> ' . $company->report_email);
        }

        return self::SUCCESS;
    }
}