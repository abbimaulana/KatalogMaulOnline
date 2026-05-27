<?php

declare(strict_types=1);

const WHATSAPP_MIN_PHONE_LENGTH = 10;
const WHATSAPP_MAX_PHONE_LENGTH = 15;
const WHATSAPP_NON_DIGIT_PATTERN = '/\D/';

function notify_order(array $order): void
{
    $message = build_wa_message($order);
    $context = $order['order_code'] ?? $order['public_id'] ?? 'unknown';
    send_telegram($message, $context);
    send_discord($message, $context);
    send_whatsapp_meta($message, $context);
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

function send_whatsapp_meta(string $message, string $context = 'unknown'): void
{
    $token = config('whatsapp.cloud_api_access_token');
    $phoneId = config('whatsapp.cloud_api_phone_id');
    $adminNumber = config('whatsapp.admin_number');

    if (!$token || !$phoneId || !$adminNumber) {
        return;
    }

    $sanitizedAdminNumber = preg_replace(WHATSAPP_NON_DIGIT_PATTERN, '', (string) $adminNumber);
    $numberLength = strlen($sanitizedAdminNumber);
    if (
        $sanitizedAdminNumber === ''
        || $numberLength < WHATSAPP_MIN_PHONE_LENGTH
        || $numberLength > WHATSAPP_MAX_PHONE_LENGTH
    ) {
        return;
    }

    $url = 'https://graph.facebook.com/v19.0/' . $phoneId . '/messages';
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'to' => $sanitizedAdminNumber,
        'type' => 'text',
        'text' => ['body' => $message],
    ], JSON_UNESCAPED_UNICODE);
    if ($payload === false) {
        error_log('WhatsApp Cloud API notification failed for order ' . $context . ': json encode failed - ' . json_last_error_msg());
        return;
    }

    $ch = curl_init($url);
    if ($ch === false) {
        error_log('WhatsApp Cloud API notification failed for order ' . $context . ': curl init failed');
        return;
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('WhatsApp Cloud API notification failed for order ' . $context . ': ' . curl_error($ch));
    } else {
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status >= 400) {
            error_log('WhatsApp Cloud API notification failed for order ' . $context . ': HTTP ' . $status . ' ' . $result);
        }
    }
    curl_close($ch);
}
