<?php

namespace App\Console\Commands;

use App\Exports\MonthlyReportExport;
use App\Mail\MonthlyReportMail;
use App\Models\Company;
use App\Services\MonthlyReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as ExcelFormat;
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
            $emails = $toOption
                ? array_filter(array_map('trim', explode(',', $toOption)))
                : array_filter(array_map('trim', explode(',', (string) $company->report_email)));

            if (empty($emails)) {
                $this->warn('Skipped ' . $company->name . ': empty email');
                continue;
            }

            $filePath = null;

            try {
                $data = $service->build($month, $company->id);
                $exportRows = $service->makeExportRows($data['rows']);

                $fileName = 'monthly-report-' . $month . '-company-' . $company->id . '.xlsx';
                $filePath = storage_path('app/' . $fileName);

                $content = Excel::raw(
                    new MonthlyReportExport($exportRows),
                    ExcelFormat::XLSX
                );

                if ($content === null || $content === '') {
                    throw new \RuntimeException('Excel raw export returned empty content.');
                }

                if (! is_dir(storage_path('app'))) {
                    mkdir(storage_path('app'), 0775, true);
                }

                file_put_contents($filePath, $content);

                if (! file_exists($filePath)) {
                    throw new \RuntimeException('Export file was not created: ' . $filePath);
                }

                Mail::to($emails)->send(
                    new MonthlyReportMail($company->name, $month, $filePath)
                );

                $this->info('Sent: ' . $company->name . ' -> ' . implode(', ', $emails));
            } catch (Throwable $e) {
                $this->error('Failed: ' . $company->name . ' -> ' . implode(', ', $emails));
                $this->error($e->getMessage());
            } finally {
                if ($filePath && file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        return self::SUCCESS;
    }
}