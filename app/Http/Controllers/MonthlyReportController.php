<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Report;
use App\Models\Site;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $companyId = $request->integer('company_id');

        if ($user->role === 'ecologist') {
            $companyId = $user->company_id;
        }

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
            ->whereBetween('report_month', [
                $month . '-01',
                $month . '-31',
            ])
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
                    'ai_result' => $report?->ai_result ?? '—',
                    'report_id' => $report?->id,
                ];
            }
        }

        $total = count($rows);
        $uploaded = collect($rows)->where('uploaded', true)->count();
        $missing = $total - $uploaded;

        return Inertia::render('MonthlyReport/Index', [
            'filters' => [
                'month' => $month,
                'company_id' => $companyId,
            ],
            'companies' => $companies,
            'rows' => $rows,
            'summary' => [
                'total' => $total,
                'uploaded' => $uploaded,
                'missing' => $missing,
            ],
            'userRole' => $user->role,
        ]);
    }
}