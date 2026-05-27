<?php

declare(strict_types=1);

function notify_order(array $order): void
{
    $message = build_wa_message($order);
    $context = $order['order_code'] ?? $order['public_id'] ?? 'unknown';
    send_telegram($message, $context);
    send_discord($message, $context);
}

function send_telegram(string $message, string $context = 'unknown'): void
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
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Telegram webhook failed for order ' . $context . ': ' . curl_error($ch));
    }
    curl_close($ch);
}

function send_discord(string $message, string $context = 'unknown'): void
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
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Discord webhook failed for order ' . $context . ': ' . curl_error($ch));
    }
    curl_close($ch);
}
