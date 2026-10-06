<?php

namespace App\Console\Commands;

use App\Exports\MonthlyReportExport;
use App\Mail\MonthlyReportMail;
use App\Models\Company;
use App\Services\MonthlyReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class SendMonthlyReportsCommand extends Command
{
    protected $signature = 'reports:send-monthly {month?} {--company=} {--to=}';
    protected $description = 'Send monthly reports to company emails';

    public function handle(MonthlyReportService $service): int
    {
        $month = $this->argument('month') ?: now()->subMonth()->format('Y-m');
        $companyOption = $this->option('company');
        $toOption = $this->option('to');

        $companies = Company::query()
            ->where('active', true)
            ->when($companyOption, function ($query) use ($companyOption) {
                $query->where('id', $companyOption);
            })
            ->when(! $toOption, function ($query) {
                $query->whereNotNull('report_email');
            })
            ->orderBy('name')
            ->get();

        if ($companies->isEmpty()) {
            $this->warn('No companies found for sending.');
            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            $email = $toOption ?: $company->report_email;

            if (! $email) {
                $this->warn('Skipped ' . $company->name . ': empty email');
                continue;
            }

            try {
                $data = $service->build($month, $company->id);
                $exportRows = $service->makeExportRows($data['rows']);

                $fileName = 'monthly-report-' . $month . '-company-' . $company->id . '.xlsx';
                $filePath = storage_path('app/' . $fileName);

                Excel::store(new MonthlyReportExport($exportRows), $fileName);

                Mail::to($email)->send(
                    new MonthlyReportMail($company->name, $month, $filePath)
                );

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $this->info('Sent: ' . $company->name . ' -> ' . $email);
            } catch (Throwable $e) {
                $this->error('Failed: ' . $company->name . ' -> ' . $email);
                $this->error($e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}