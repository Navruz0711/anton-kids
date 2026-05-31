<?php
/**
 * send.php — Форма заявки «Антон Детям»
 * Отправляет данные на balanov_anton@bk.ru
 * Разместить рядом с index.html на хостинге Timeweb
 */

// ── Настройки ──────────────────────────────────────────
define('TO_EMAIL',   'balanov_anton@bk.ru');
define('FROM_EMAIL', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'anton-detyam.ru'));
define('FROM_NAME',  'Сайт Антон Детям');
// ───────────────────────────────────────────────────────

header('Content-Type: application/json; charset=utf-8');

// Разрешаем только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Получаем и очищаем данные ──
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$source = clean($_POST['source'] ?? 'Не указан');
$name   = clean($_POST['name']   ?? '');
$phone  = clean($_POST['phone']  ?? '');
$email  = clean($_POST['email']  ?? '');
$org    = clean($_POST['org']    ?? '');

// ── Базовая валидация ──
if ($name === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Имя и телефон обязательны']);
    exit;
}

// ── Формируем письмо ──
$subject = '📩 Новая заявка с сайта: ' . $source;

$body  = "=== НОВАЯ ЗАЯВКА С САЙТА «АНТОН ДЕТЯМ» ===\n\n";
$body .= "Источник (кнопка): {$source}\n";
$body .= "─────────────────────────────────────────\n";
$body .= "Имя:          {$name}\n";
$body .= "Телефон:      {$phone}\n";
if ($email !== '') $body .= "E-mail:       {$email}\n";
if ($org   !== '') $body .= "Организация:  {$org}\n";
$body .= "─────────────────────────────────────────\n";
$body .= "Дата/время:   " . date('d.m.Y H:i:s (T)') . "\n";
$body .= "IP клиента:   " . ($_SERVER['REMOTE_ADDR'] ?? '—') . "\n";
$body .= "User-Agent:   " . ($_SERVER['HTTP_USER_AGENT'] ?? '—') . "\n";

// ── Заголовки письма ──
$headers  = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
$headers .= "Reply-To: " . ($email !== '' ? $email : FROM_EMAIL) . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// ── Отправка ──
$sent = mail(TO_EMAIL, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Ошибка отправки письма. Проверьте настройки почты на хостинге.']);
}