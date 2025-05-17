<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SavedPlayer;
use App\Services\RelicScoreService;

class ScoreApiController extends Controller
{
    public function show($uid)
    {
        try {
            // API呼び出し
            $response = Http::timeout(5)->get("https://api.mihomo.me/sr_info_parsed/{$uid}?lang=jp");

            if ($response->successful()) {
                $data = $response->json();

                // プレイヤー or キャラが空ならエラー返す
                if (empty($data['player']) || empty($data['characters'])) {
                    throw new \Exception('データが不完全です');
                }

                $player = $data['player'];
                $characters = $data['characters'];

                $service = new RelicScoreService();
                foreach ($characters as $i => $char) {
                    $total = 0;
                    foreach ($char['relics'] ?? [] as $j => $relic) {
                        $score = $service->calculateRelicScore($relic, []);
                        $characters[$i]['relics'][$j]['score'] = $score;
                        $total += $score['total'] ?? 0;
                    }
                    $characters[$i]['sumScore'] = round($total, 1);
                }

                // DB保存
                SavedPlayer::updateOrCreate(
                    ['uid' => $uid],
                    ['player_data' => json_encode(['player' => $player, 'characters' => $characters], JSON_UNESCAPED_UNICODE)]
                );

                return response()->json([
                    'source' => 'api',
                    'player' => $player,
                    'characters' => $characters,
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            throw new \Exception('APIエラー');
        } catch (\Throwable $e) {
            Log::warning("API失敗: {$uid} - {$e->getMessage()}");

            $saved = SavedPlayer::where('uid', $uid)->first();
            if ($saved) {
                $data = json_decode($saved->player_data, true);
                return response()->json([
                    'source' => 'saved',
                    'player' => $data['player'] ?? null,
                    'characters' => $data['characters'] ?? [],
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json([
                'source' => 'none',
                'error' => '情報が取得できません',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
