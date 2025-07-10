<?php
header('Content-Type: application/json');

// .envの読み込み（簡易）
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false) {
            putenv(trim($line));
        }
    }
}

$apiKey = getenv('GEMINI_API_KEY');
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $text = $postData['text'] ?? null;
    $targetLang = $postData['target'] ?? 'en';

    if (!$text) {
        echo json_encode(['error' => 'No input text provided.']);
        exit;
    }

    // 言語コード → 言語名マッピング
    $langMap = [
        'en' => '英語',
        'zh' => '中国語（簡体字）',
        'zh-TW' => '中国語（台湾）',
        'yue' => '広東語',
        'tl' => 'タガログ語',
        'vi' => 'ベトナム語',
        'pt' => 'ポルトガル語',
        'es' => 'スペイン語',
        'ne' => 'ネパール語',
        'my' => 'ミャンマー語',
        'ko' => '韓国語',
        'mn' => 'モンゴル語',
        'uz' => 'ウズベク語',
        'th' => 'タイ語',
        'id' => 'インドネシア語',
        'fr' => 'フランス語',
        'hi' => 'ヒンディー語',
        'bn' => 'ベンガル語',
        'ur' => 'ウルドゥー語',
        'ar' => 'アラビア語',
        'fa' => 'ペルシャ語',
        'ms' => 'マレー語',
        'ru' => 'ロシア語',
        'uk' => 'ウクライナ語',
        'tr' => 'トルコ語',
        'de' => 'ドイツ語',
        'ro' => 'ルーマニア語',
        'pl' => 'ポーランド語',
        'it' => 'イタリア語',
        'sv' => 'スウェーデン語',
        'si' => 'シンハラ語',
        'km' => 'クメール語',
        'ta' => 'タミル語',
        'tg' => 'タジク語',
        'ceb' => 'セブアノ語',
        'ps' => 'パシュトー語',
        'el' => 'ギリシャ語',
        'hu' => 'ハンガリー語',
        'bo' => 'チベット語',
        'ky' => 'キルギス語',
        'be' => 'ベラルーシ語'
    ];
    $targetLangName = $langMap[$targetLang] ?? $targetLang;


    // プロンプトを構成
    $prompt = <<<EOT
以下の日本語の文を「{$targetLangName}」に翻訳してください。
- 短くやさしい子どもにも伝わる表現で翻訳してください
- 出力は翻訳文のみとし、説明などは不要です

元の文: {$text}
EOT;

    $data = [
        'contents' => [[
            'parts' => [['text' => $prompt]]
        ]]
    ];

    $ch = curl_init($apiUrl . '?key=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Gemini API Error: HTTP {$httpCode} " . $response);
        echo json_encode(['translatedText' => "エラーが発生しました。"]);
        exit;
    }

    $result = json_decode($response, true);
    $translated = $result['candidates'][0]['content']['parts'][0]['text'] ?? "翻訳結果が取得できませんでした。";

    echo json_encode(['translatedText' => $translated]);
    exit;
}

echo json_encode(['error' => 'Invalid request method.']);
exit;
