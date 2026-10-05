<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Http;

class AiVisionService
{
    public function analyzeReport(Report $report): array
    {
        $report->load(['site.company', 'zone', 'photos', 'user']);

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
                'max_tokens' => 1000,
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
        $company = $report->site?->company?->name ?? 'Неизвестная компания';
        $site = $report->site?->name ?? 'Неизвестный участок';
        $zone = $report->zone?->name ?? 'Неизвестная зона';
        $date = $report->report_month
            ? \Carbon\Carbon::parse($report->report_month)->format('Y-m-d')
            : 'Не указана';
        $comment = $report->comment ?: 'Нет комментария';

        return <<<PROMPT
Ты сотрудник, проводящий экологическую проверку производственного участка. Проанализируй фотографии мест сбора, хранения и обращения с отходами, а также хранения масел и ГСМ на удаленном участке компании.

Оцени только то, что реально видно на фотографиях. Не выдумывай факты и не делай выводы, если данных недостаточно.

Контекст:
- Компания: {$company}
- Участок: {$site}
- Зона: {$zone}
- Дата отчета: {$date}
- Комментарий: {$comment}

Задача:
1. Кратко опиши, что изображено на фото.
2. Укажи выявленные недостатки, нарушения или замечания по обращению с отходами, местам накопления отходов, раздельному сбору, маркировке, таре, площадкам хранения, а также хранению масел и ГСМ.
3. Дай краткие практические рекомендации по устранению замечаний.
4. Если по фото невозможно сделать достоверный вывод, прямо укажи это.

Требования к ответу:
- Пиши кратко, официально и по делу.
- Не добавляй разделы "Риски" и "Итог".
- Не дублируй одни и те же мысли разными словами.
- Если замечаний не видно, так и напиши.
- Если фото не позволяют сделать точный вывод, так и напиши.

Ответ верни строго в формате:

Описание:
...

Замечания:
- ...
- ...

Рекомендации:
- ...
- ...
PROMPT;
    }
}