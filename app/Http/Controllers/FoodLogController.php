<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use App\Services\ClaudeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FoodLogController extends Controller
{
    public function __construct(private ClaudeService $claude) {}

    // ── COMPRESS IMAGE ────────────────────────────────────────────────────────

    private function compressImage(string $filePath, string $mimeType): string
    {
        // Buat image resource dari file
        $image = match(true) {
            str_contains($mimeType, 'png')  => imagecreatefrompng($filePath),
            str_contains($mimeType, 'webp') => imagecreatefromwebp($filePath),
            default                         => imagecreatefromjpeg($filePath),
        };

        if (!$image) {
            // Fallback: langsung base64 tanpa compress
            return base64_encode(file_get_contents($filePath));
        }

        $origW = imagesx($image);
        $origH = imagesy($image);

        // Resize maksimal 800px di sisi terpanjang
        $maxSize = 800;
        if ($origW > $maxSize || $origH > $maxSize) {
            if ($origW > $origH) {
                $newW = $maxSize;
                $newH = (int) round($origH * ($maxSize / $origW));
            } else {
                $newH = $maxSize;
                $newW = (int) round($origW * ($maxSize / $origH));
            }
            $resized = imagecreatetruecolor($newW, $newH);

            // Preserve transparency untuk PNG
            if (str_contains($mimeType, 'png')) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($image);
            $image = $resized;
        }

        // Output sebagai JPEG kualitas 75%
        ob_start();
        imagejpeg($image, null, 75);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return base64_encode($imageData);
    }

    // ── ANALYZE IMAGE (AJAX) ──────────────────────────────────────────────────

    public function analyze(Request $request)
    {
        $request->validate([
            'image'     => ['required', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'food_hint' => ['nullable', 'string', 'max:100'],
        ]);

        $file      = $request->file('image');
        $filePath  = $file->getRealPath();
        $mimeType  = $file->getMimeType();
        $hint      = (string) $request->input('food_hint', '');

        // Compress & resize sebelum kirim ke AI
        try {
            $base64    = $this->compressImage($filePath, $mimeType);
            $mediaType = 'image/jpeg'; // selalu JPEG setelah compress
        } catch (\Throwable $e) {
            Log::warning('Compress failed, using original', ['error' => $e->getMessage()]);
            $base64    = base64_encode(file_get_contents($filePath));
            $mediaType = $mimeType;
        }

        $result = $this->claude->analyzeFoodImage($base64, $mediaType, $hint);

        // Simpan gambar original ke temp
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempFilename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $file->move($tempDir, $tempFilename);
        $tempPath = 'temp/' . $tempFilename;

        return response()->json([
            'success'   => true,
            'analysis'  => $result,
            'temp_path' => $tempPath,
        ]);
    }

    // ── STORE LOG ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'food_name'        => ['required', 'string', 'max:255'],
            'food_description' => ['nullable', 'string'],
            'calories'         => ['required', 'integer', 'min:0'],
            'protein'          => ['nullable', 'numeric', 'min:0'],
            'carbs'            => ['nullable', 'numeric', 'min:0'],
            'fat'              => ['nullable', 'numeric', 'min:0'],
            'meal_type'        => ['required', 'in:breakfast,lunch,dinner,snack'],
            'ai_analysis'      => ['nullable', 'string'],
            'temp_path'        => ['nullable', 'string'],
            'logged_date'      => ['nullable', 'date'],
        ]);

        $imageUrl = null;

        // Upload gambar dari temp local ke Supabase Storage
        if (!empty($validated['temp_path'])) {
            $tempPath = storage_path('app/' . $validated['temp_path']);

            if (file_exists($tempPath)) {
                $supabaseUrl  = env('SUPABASE_URL');
                $supabaseKey  = env('SUPABASE_SERVICE_KEY');
                $bucket       = 'food-images';
                $extension    = pathinfo($tempPath, PATHINFO_EXTENSION);
                $filename     = Auth::id() . '/' . Str::uuid() . '.' . $extension;
                $fileContents = file_get_contents($tempPath);
                $mimeType     = mime_content_type($tempPath);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $supabaseKey,
                    'Content-Type'  => $mimeType,
                    'x-upsert'      => 'true',
                ])->withBody($fileContents, $mimeType)
                  ->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$filename}");

                if ($response->successful()) {
                    $imageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$filename}";
                    Log::info('Image uploaded to Supabase', ['url' => $imageUrl]);
                } else {
                    Log::error('Supabase upload failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }

                @unlink($tempPath);
            }
        }

        Auth::user()->foodLogs()->create([
            'food_name'        => $validated['food_name'],
            'food_description' => $validated['food_description'] ?? null,
            'calories'         => $validated['calories'],
            'protein'          => $validated['protein'] ?? null,
            'carbs'            => $validated['carbs'] ?? null,
            'fat'              => $validated['fat'] ?? null,
            'meal_type'        => $validated['meal_type'],
            'ai_analysis'      => $validated['ai_analysis'] ?? null,
            'image_path'       => $imageUrl,
            'logged_date'      => $validated['logged_date'] ?? today(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Makanan berhasil dicatat!');
    }

    // ── DELETE LOG ────────────────────────────────────────────────────────────

    public function destroy(FoodLog $foodLog)
    {
        if ($foodLog->user_id !== Auth::id()) {
            abort(403);
        }

        if ($foodLog->image_path) {
            $supabaseUrl = env('SUPABASE_URL');
            $supabaseKey = env('SUPABASE_SERVICE_KEY');
            $bucket      = 'food-images';

            $path = str_replace("{$supabaseUrl}/storage/v1/object/public/{$bucket}/", '', $foodLog->image_path);

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $supabaseKey,
            ])->delete("{$supabaseUrl}/storage/v1/object/{$bucket}/{$path}");
        }

        $foodLog->delete();

        return back()->with('success', 'Log makanan dihapus.');
    }

    // ── HISTORY ───────────────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $logs = Auth::user()->foodLogs()
            ->whereDate('logged_date', $date)
            ->latest()
            ->get();

        $totalCalories = $logs->sum('calories');

        return view('food.history', compact('logs', 'date', 'totalCalories'));
    }
}