
<?php
require_once __DIR__ . '/vendor/autoload.php';
require 'Config.php';

// PHPOption - 一時的にエラー内容を画面に表示
// ini_set ( 'display_errors', 1 );

// configファイルのパス設定
Config::set_config_directory(__DIR__ . '/config');
echo  Config::get('app.url');

// POST
$input = file_get_contents ( 'php://input' );
$json = json_decode ( $input );
$event = $json->events [0];

file_put_contents ( "php://stdout", $input );

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient (Config::get('app.CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot ( $httpClient, ['channelSecret' => Config::get('app.CHANNEL_SECRET')] );

if ('user' == $event->source->type) {
    if ("postback" == $event->type) {
        if ("1" == $event->postback->data) {
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( "参加で受付ました。" );
            $response = $bot->replyMessage ( $event->replyToken, $textMessageBuilder );
        } else if ("2" == $event->postback->data) {
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( "不参加で受付ました。" );
            $response = $bot->replyMessage ( $event->replyToken, $textMessageBuilder );
        }
        return;
    } else if ("message" == $event->type) { // 一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
                                            
        // テキストメッセージにはオウムで返す
        if ("text" == $event->message->type) {
            $inputText = $event->message->text;
            file_put_contents ( "php://stdout", "\n" . $inputText . "\n" );
            if ('参加可否入力' == $inputText) {
                
                $response = $bot->replyMessage ( $event->replyToken, new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder ( '参加しますか？', new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder ( '参加しますか？', [ 
                                new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder ( "はい", "1" ),
                                new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder ( 'いいえ', '2' ) 
                ] ) ) );
                
                if ($response->isSucceeded ()) {
                    file_put_contents ( "php://stdout", "\nisSucceeded\n" );
                } else {
                    file_put_contents ( "php://stdout", "\nisFail\n" );
                }
                return;
            } else if ('ユーザID取得' == $inputText) {
                $replyMsg = $event->source->userId;
                $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( $replyMsg );
                $response = $bot->replyMessage ( $event->replyToken, $textMessageBuilder );
                return;
            } else if ('プロフィール取得' == $inputText) {
                $res = $bot->getProfile ( $event->source->userId );
                if ($res->isSucceeded ()) {
                    $profile = $res->getJSONDecodedBody();
                    $profileDispName = '[表示名]=' . $profile['displayName'];
                    $profilePictUrl = '[画像]=' . $profile['pictureUrl'];
                    $profileStatus = '[ステータス]=' . $profile['statusMessage'];
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("あなたのプロフィール情報です。", $profileDispName, $profilePictUrl, $profileStatus);
                    $response = $bot->replyMessage( $event->replyToken, $textMessageBuilder );
                    return;
                } else {
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( 'unknown' );
                    $response = $bot->replyMessage ( $event->replyToken, $textMessageBuilder );
                    return;
                }
            }
        }
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder ( $input );
        $response = $bot->replyMessage ( $event->replyToken, $textMessageBuilder );
        return;
    }
} else {
}
return;

?>
