<?php

namespace App\Jobs;

use App\Models\AiLog;
use App\Models\Report;
use App\Services\AiVisionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeReportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(public int $reportId)
    {
    }

    public function handle(AiVisionService $service): void
    {
        $report = Report::with(['photos', 'site', 'zone', 'user'])->find($this->reportId);

        if (! $report) {
            return;
        }

        if (! config('services.ai.enabled')) {
            $report->update([
                'ai_status' => 'disabled',
                'ai_result' => 'AI-анализ отключен.',
            ]);

            return;
        }

        $report->update([
            'ai_status' => 'processing',
        ]);

        $log = AiLog::create([
            'report_id' => $report->id,
            'model' => config('services.ai.model'),
            'status' => 'processing',
        ]);

        try {
            $result = $service->analyzeReport($report);

            if (! $result['success']) {
                $report->update([
                    'ai_status' => 'failed',
                    'ai_result' => $result['error'] ?? 'AI analysis failed',
                ]);

                $log->update([
                    'prompt' => $result['prompt'] ?? null,
                    'response' => $result['raw'] ?? null,
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return;
            }

            $report->update([
                'ai_status' => 'done',
                'ai_result' => $result['text'],
            ]);

            $log->update([
                'prompt' => $result['prompt'] ?? null,
                'response' => $result['raw'] ?? null,
                'status' => 'done',
            ]);
        } catch (\Throwable $e) {
            $report->update([
                'ai_status' => 'failed',
                'ai_result' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}