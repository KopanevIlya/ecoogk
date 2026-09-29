<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Http;

class AiVisionService
{
    public function analyzeReport(Report $report): array
    {
        $report->load(['site', 'zone', 'photos', 'user']);

        $prompt = $this->buildPrompt($report);

        $content = [
            [
                'type' => 'text',
                'text' => $prompt,
            ],
        ];

        foreach ($report->photos as $photo) {
            $fullPath = storage_path('app/public/' . $photo->path);

            if (! file_exists($fullPath)) {
                continue;
            }

            $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
            $base64 = base64_encode(file_get_contents($fullPath));

            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$base64}",
                ],
            ];
        }

        $response = Http::withToken(config('services.ai.api_key'))
            ->acceptJson()
            ->timeout(180)
            ->post(rtrim(config('services.ai.base_url'), '/') . '/chat/completions', [
                'model' => config('services.ai.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'temperature' => 0.2,
                'max_tokens' => 1200,
            ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'prompt' => $prompt,
                'raw' => $response->body(),
                'error' => 'AI request failed: ' . $response->status(),
            ];
        }

        $json = $response->json();

        $text = data_get($json, 'choices.0.message.content');

        if (is_array($text)) {
            $text = collect($text)
                ->pluck('text')
                ->filter()
                ->implode("\n");
        }

        return [
            'success' => true,
            'prompt' => $prompt,
            'raw' => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'text' => $text ?: 'Пустой ответ от AI',
        ];
    }

    protected function buildPrompt(Report $report): string
    {
        $site = $report->site?->name ?? 'Неизвестный участок';
        $zone = $report->zone?->name ?? 'Неизвестная зона';
        $date = $report->report_month ? \Carbon\Carbon::parse($report->report_month)->format('Y-m-d') : 'Не указана';
        $comment = $report->comment ?: 'Нет комментария';

        return <<<PROMPT
Ты экологический инспектор. Проанализируй фотографии отчета по обращению с отходами.

Контекст:
- Участок: {$site}
- Зона: {$zone}
- Дата отчета: {$date}
- Комментарий: {$comment}

Нужно:
1. Кратко описать, что видно на фото.
2. Выявить возможные нарушения или недостатки.
3. Оценить экологические и производственные риски.
4. Дать рекомендации.
5. Если по фото недостаточно данных — честно укажи это.

Ответ верни строго в формате:

Краткое описание:
...

Недостатки:
- ...
- ...

Риски:
- ...
- ...

Рекомендации:
- ...
- ...

Итог:
...
PROMPT;
    }
}