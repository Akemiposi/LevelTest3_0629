<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Google Cloud 認証用のサービスアカウントJSONを指定（Xserver用）
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../tts-credentials.json');

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;

// .env を読み込む
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv(trim($line));
    }
}
// **＊テキスト取得
// JSON形式でPOSTデータを受け取る
$input = json_decode(file_get_contents('php://input'), true);

// ログ出力して中身を確認（デバッグ用）
file_put_contents(__DIR__ . '/../tts_debug.log', "受信データ: " . print_r($input, true) . "\n", FILE_APPEND);

// テキスト抽出
$text = $_POST['text'] ?? '';

if (empty($text)) {
    echo json_encode(['success' => false, 'error' => 'テキストが空です']);
    exit;
}

try {
    // Google Cloud TTSクライアントの作成
    $client = new TextToSpeechClient();

    $input = new SynthesisInput();
    $input->setText($text);

    $voice = new VoiceSelectionParams();
    $voice->setLanguageCode('ja-JP');
    $voice->setName('ja-JP-Wavenet-B'); // 男性音声

    $audioConfig = new AudioConfig();
    $audioConfig->setAudioEncoding(AudioConfig::MP3);

    // 音声生成
    $response = $client->synthesizeSpeech($input, $voice, $audioConfig);

    // 出力ファイル保存
    $file = __DIR__ . '/output.mp3';
    file_put_contents($file, $response->getAudioContent());

    $client->close();

    echo json_encode(['success' => true, 'file' => 'output.mp3']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
