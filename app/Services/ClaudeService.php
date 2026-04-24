<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $apiKey = '';
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    // Model vision yang tersedia di Groq (gratis)
    private array $models = [
        'meta-llama/llama-4-scout-17b-16e-instruct',
        'meta-llama/llama-4-maverick-17b-128e-instruct',
    ];

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
    }

    /**
     * @param string $base64Image  Base64-encoded image data
     * @param string $mediaType    MIME type (image/jpeg, image/png, etc.)
     * @param string $foodHint     Optional hint from user about what the food is
     */
    public function analyzeFoodImage(string $base64Image, string $mediaType, string $foodHint = ''): array
    {
        if (empty($this->apiKey)) {
            return $this->fallbackResult('API key belum dikonfigurasi.');
        }

        // Bangun hint line — AI tetap harus cross-check visual, tidak boleh buta ikut hint
        $hintLine = '';
        if (!empty(trim($foodHint))) {
            $hintLine = "\n\nINFO DARI PENGGUNA: Pengguna menyebut makanan ini sebagai \"{$foodHint}\". "
                . "Gunakan ini sebagai petunjuk tambahan, BUKAN kebenaran mutlak. "
                . "Tetap analisis gambar secara visual. "
                . "Jika gambar COCOK atau MENDEKATI hint tersebut, gunakan nama dan data nutrisi dari hint. "
                . "Jika gambar JELAS BERBEDA dari hint (misalnya hint bilang 'nasi goreng' tapi gambar jelas bukan nasi), "
                . "abaikan hint dan analisis berdasarkan apa yang benar-benar terlihat di gambar. "
                . "Tulis hasil yang jujur sesuai visual.";
        }

        $prompt = 'Kamu adalah ahli gizi profesional. Analisis gambar makanan ini dan berikan estimasi nilai nutrisinya.'
            . $hintLine
            . ' Jawab HANYA dalam format JSON berikut, tanpa teks tambahan apapun:
{
  "food_name": "nama makanan dalam Bahasa Indonesia",
  "description": "deskripsi singkat makanan",
  "calories": <angka integer, estimasi kalori>,
  "protein": <gram protein, angka desimal>,
  "carbs": <gram karbohidrat, angka desimal>,
  "fat": <gram lemak, angka desimal>,
  "confidence": "high/medium/low",
  "notes": "catatan tambahan seperti porsi yang diasumsikan atau variasi yang mungkin"
}
Jika gambar bukan makanan, tetap jawab dalam JSON dengan calories: 0 dan food_name: "Bukan makanan".';

        $lastError = 'Semua model AI tidak tersedia saat ini.';

        foreach ($this->models as $model) {
            try {
                Log::info('Trying Groq model: ' . $model);

                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type'  => 'application/json',
                    ])
                    ->post($this->apiUrl, [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'image_url',
                                        'image_url' => [
                                            'url' => 'data:' . $mediaType . ';base64,' . $base64Image,
                                        ],
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $prompt,
                                    ],
                                ],
                            ],
                        ],
                        'temperature' => 0.1,
                        'max_tokens'  => 1024,
                    ]);

                if (in_array($response->status(), [429, 503, 404])) {
                    $lastError = 'Model ' . $model . ' tidak tersedia, mencoba model lain...';
                    Log::warning('Groq model unavailable, trying next', [
                        'model'  => $model,
                        'status' => $response->status(),
                    ]);
                    continue;
                }

                if ($response->failed()) {
                    $lastError = 'Gagal terhubung ke AI. Status: ' . $response->status();
                    Log::error('Groq API error', [
                        'model'  => $model,
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    continue;
                }

                $content = $response->json('choices.0.message.content', '');
                $content = preg_replace('/```json\s*|\s*```/', '', trim($content));

                $data = json_decode($content, true);

                if (!$data || !isset($data['calories'])) {
                    Log::warning('Groq returned invalid JSON', ['model' => $model, 'raw' => $content]);
                    $lastError = 'Respons AI tidak valid.';
                    continue;
                }

                Log::info('Successfully analyzed with Groq model: ' . $model);
                return $data;

            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::error('ClaudeService exception', ['model' => $model, 'message' => $e->getMessage()]);
                continue;
            }
        }

        return $this->fallbackResult($lastError);
    }

    private function fallbackResult(string $reason): array
    {
        return [
            'food_name'   => 'Tidak dikenali',
            'description' => $reason,
            'calories'    => 0,
            'protein'     => 0,
            'carbs'       => 0,
            'fat'         => 0,
            'confidence'  => 'low',
            'notes'       => 'Analisis gagal. Silakan isi manual.',
        ];
    }
}