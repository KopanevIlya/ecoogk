<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Report;
use App\Models\Site;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\Jobs\AnalyzeReportJob;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['site', 'zone', 'photos', 'user'])
            ->latest()
            ->when(Auth::user()->role === 'user', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'site' => $report->site?->name,
                    'zone' => $report->zone?->name,
                    'report_month' => $report->report_month?->format('Y-m-d'),
                    'comment' => $report->comment,
                    'status' => $report->status,
                    'ai_status' => $report->ai_status,
                    'ai_result' => $report->ai_result,
                    'photos_count' => $report->photos->count(),
                    'created_by' => $report->user?->name,
                    'created_at' => $report->created_at?->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
        ]);
    }

    public function create()
    {
        return Inertia::render('Reports/Create', [
            'sites' => Site::where('active', true)->get(['id', 'name']),
            'zones' => Zone::where('active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'site_id' => ['required', 'exists:sites,id'],
        'zone_id' => ['required', 'exists:zones,id'],
        'report_month' => ['required', 'date'],
        'comment' => ['nullable', 'string'],
        'photos' => ['required', 'array', 'min:1'],
        'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ]);

    $report = DB::transaction(function () use ($request) {
        $report = \App\Models\Report::create([
            'user_id' => Auth::id(),
            'site_id' => $request->site_id,
            'zone_id' => $request->zone_id,
            'report_month' => $request->report_month,
            'comment' => $request->comment,
            'status' => 'uploaded',
            'ai_status' => 'pending',
        ]);

        foreach ($request->file('photos') as $file) {
            $path = $file->store('reports', 'public');

            \App\Models\Photo::create([
                'report_id' => $report->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return $report;
    });

    AnalyzeReportJob::dispatch($report->id);

    return redirect()
        ->route('reports.index')
        ->with('success', 'Отчёт загружен и отправлен на AI-анализ.');
}

public function show(Report $report)
{
    $report->load([
        'site',
        'zone',
        'user',
        'photos',
    ]);

    return Inertia::render('Reports/Show', [
        'report' => [
            'id' => $report->id,
            'status' => $report->status,
            'ai_status' => $report->ai_status,
            'ai_result' => $report->ai_result,
            'comment' => $report->comment,
            'report_month' => $report->report_month,
            'created_at' => optional($report->created_at)?->format('Y-m-d H:i'),
            'site' => $report->site ? [
                'id' => $report->site->id,
                'name' => $report->site->name,
            ] : null,
            'zone' => $report->zone ? [
                'id' => $report->zone->id,
                'name' => $report->zone->name,
            ] : null,
            'user' => $report->user ? [
                'id' => $report->user->id,
                'name' => $report->user->name,
            ] : null,
            'photos' => $report->photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'path' => $photo->path,
                    'original_name' => $photo->original_name,
                    'url' => asset('storage/' . $photo->path),
                ];
            })->values(),
        ],
    ]);
}
}