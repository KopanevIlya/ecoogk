<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Models\Company;
use App\Models\Report;
use App\Models\Site;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        [$data, $companies, $month, $companyId, $user] = $this->buildReportData($request);

        return Inertia::render('MonthlyReport/Index', [
            'filters' => [
                'month' => $month,
                'company_id' => $companyId,
            ],
            'companies' => $companies,
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'userRole' => $user->role,
        ]);
    }

    public function export(Request $request)
    {
        [$data, $companies, $month, $companyId] = $this->buildReportData($request);

        $rows = [
            [
                'Компания',
                'Участок',
                'Зона',
                'Месяц',
                'Статус загрузки',
                'Количество фото',
                'Дата загрузки',
                'Пользователь',
                'AI статус',
                'Замечания',
                'Рекомендации',
                'Полный AI результат',
            ],
        ];

        foreach ($data['rows'] as $row) {
            $rows[] = [
                $row['company'],
                $row['site'],
                $row['zone'],
                $row['month'],
                $row['status_label'],
                $row['photos_count'],
                $row['created_at'],
                $row['created_by'],
                $this->aiStatusLabel($row['ai_status']),
                $row['issues'],
                $row['recommendations'],
                $row['ai_result'],
            ];
        }

        $companySuffix = 'all';

        if ($companyId) {
            $company = $companies->firstWhere('id', $companyId);
            $companySuffix = $company?->code ?: $company?->name ?: 'company';
        }

        $filename = 'monthly-report-' . $month . '-' . $companySuffix . '.xlsx';

        return Excel::download(new MonthlyReportExport($rows), $filename);
    }

    protected function buildReportData(Request $request): array
    {
        $user = $request->user();

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $companyId = $request->integer('company_id');

        if ($user->role === 'ecologist') {
            $companyId = $user->company_id;
        }

        $from = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $to = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $companies = Company::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $sitesQuery = Site::query()
            ->with('company:id,name,code')
            ->where('active', true);

        if ($companyId) {
            $sitesQuery->where('company_id', $companyId);
        }

        $sites = $sitesQuery
            ->orderBy('company_id')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'company_id', 'active']);

        $zones = Zone::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'active']);

        $reports = Report::query()
            ->with([
                'user:id,name',
                'photos:id,report_id',
                'site.company:id,name,code',
                'zone:id,name,code',
            ])
            ->whereBetween('report_month', [$from, $to])
            ->when($companyId, function ($query) use ($companyId) {
                $query->whereHas('site', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $reportMap = [];

        foreach ($reports as $report) {
            $key = $report->site_id . '_' . $report->zone_id;

            if (! isset($reportMap[$key])) {
                $reportMap[$key] = $report;
            }
        }

        $rows = [];

        foreach ($sites as $site) {
            foreach ($zones as $zone) {
                $key = $site->id . '_' . $zone->id;
                $report = $reportMap[$key] ?? null;

                $aiResult = $report?->ai_result ?? '—';
                $parsed = $this->extractAiSections($aiResult);

                $rows[] = [
                    'company' => $site->company?->name ?? '—',
                    'company_code' => $site->company?->code ?? '—',
                    'site' => $site->name,
                    'site_code' => $site->code,
                    'zone' => $zone->name,
                    'zone_code' => $zone->code,
                    'month' => $month,
                    'uploaded' => (bool) $report,
                    'status_label' => $report ? 'Загружено' : 'Не загружено',
                    'photos_count' => $report ? $report->photos->count() : 0,
                    'created_at' => $report?->created_at?->format('d.m.Y H:i') ?? '—',
                    'created_by' => $report?->user?->name ?? '—',
                    'ai_status' => $report?->ai_status ?? '—',
                    'ai_result' => $aiResult,
                    'issues' => $parsed['issues'],
                    'recommendations' => $parsed['recommendations'],
                    'report_id' => $report?->id,
                ];
            }
        }

        $total = count($rows);
        $uploaded = collect($rows)->where('uploaded', true)->count();
        $missing = $total - $uploaded;

        return [
            [
                'rows' => $rows,
                'summary' => [
                    'total' => $total,
                    'uploaded' => $uploaded,
                    'missing' => $missing,
                ],
            ],
            $companies,
            $month,
            $companyId,
            $user,
        ];
    }

    protected function extractAiSections(?string $text): array
    {
        if (! $text || $text === '—') {
            return [
                'issues' => '—',
                'recommendations' => '—',
            ];
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);

        $issues = $this->extractSection($normalized, ['Замечания:', 'Недостатки:'], ['Рекомендации:']);
        $recommendations = $this->extractSection($normalized, ['Рекомендации:'], []);

        return [
            'issues' => $issues ?: '—',
            'recommendations' => $recommendations ?: '—',
        ];
    }

    protected function extractSection(string $text, array $starts, array $ends): ?string
    {
        $startPos = null;
        $startLabel = null;

        foreach ($starts as $label) {
            $pos = mb_stripos($text, $label);

            if ($pos !== false && ($startPos === null || $pos < $startPos)) {
                $startPos = $pos;
                $startLabel = $label;
            }
        }

        if ($startPos === null || $startLabel === null) {
            return null;
        }

        $contentStart = $startPos + mb_strlen($startLabel);
        $content = mb_substr($text, $contentStart);

        $endPos = null;

        foreach ($ends as $label) {
            $pos = mb_stripos($content, $label);

            if ($pos !== false && ($endPos === null || $pos < $endPos)) {
                $endPos = $pos;
            }
        }

        if ($endPos !== null) {
            $content = mb_substr($content, 0, $endPos);
        }

        return trim($content) ?: null;
    }

    protected function aiStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'В очереди',
            'processing' => 'Анализируется',
            'done' => 'Готово',
            'failed' => 'Ошибка',
            'disabled' => 'Отключен',
            default => $status ?: '—',
        };
    }
}