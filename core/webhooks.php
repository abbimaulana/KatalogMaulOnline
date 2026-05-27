<?php

declare(strict_types=1);

function notify_order(array $order): void
{
    $message = build_wa_message($order);
    send_telegram($message);
    send_discord($message);
}

function send_telegram(string $message): void
{
    $token = config('bots.telegram_token');
    $chatId = config('bots.telegram_chat_id');

    if (!$token || !$chatId) {
        return;
    }

    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $payload = http_build_query([
        'chat_id' => $chatId,
        'text' => $message,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function send_discord(string $message): void
{
    $webhook = config('bots.discord_webhook_url');
    if (!$webhook) {
        return;
    }

    $payload = json_encode(['content' => $message], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($webhook);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}
