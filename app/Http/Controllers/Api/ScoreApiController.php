<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ScoreApiController extends Controller
{
    public function show($uid)
{
    $response = Http::timeout(5)->get("https://api.mihomo.me/sr_info_parsed/{$uid}?lang=jp");

    if ($response->successful()) {
        $data = $response->json();

        // プレイヤー or キャラが空ならエラー返す
        if (empty($data['player']) || empty($data['characters'])) {
            return response()->json(
                ['error' => 'データが不完全です'],
                422,
                [],
                JSON_UNESCAPED_UNICODE
            );
        }

        return response()->json(
            [
                'player' => $data['player'],
                'characters' => $data['characters'],
            ],
            200,
            [],
            JSON_UNESCAPED_UNICODE
        );
    }

    return response()->json(
        ['error' => 'API取得失敗'],
        500,
        [],
        JSON_UNESCAPED_UNICODE
    );
}
}
