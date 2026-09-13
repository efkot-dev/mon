<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}

class Message
{
    private const ALLOWED = ['info', 'success', 'warning', 'error'];
    private static ?string $currentType = null;
    private static array $texts = [
        'location' => [
            'title' => 'Необхідна локація',
            'text'  => 'Для роботи модуля необхідно додати локацію та відмітити на карті центр локації.'
        ],
        'oblenergo' => [
            'title' => 'Обленерго',
            'text'  => 'Для роботи модуля необхідно додати Обленерго та відмітити на карті відповідно до розташування.'
        ],
        'saved' => [
            'title' => 'Успішно',
            'text'  => 'Дані успішно збережені.'
        ],
        'denied' => [
            'title' => 'Помилка доступу',
            'text'  => 'Недостатньо прав для виконання операції.'
        ],
    ];
    public static function addText(string $key, string $title, string $text): void
    {
        $key = strtolower(trim($key));
        self::$texts[$key] = ['title' => $title, 'text' => $text];
    }
    public static function pmess(): ?string
    {
        if (self::$currentType !== null) return self::$currentType;

        $type = $_GET['pmess'] ?? null;
        if (!is_string($type)) return null;

        $type = strtolower(trim($type));
        if (!in_array($type, self::ALLOWED, true)) return null;

        self::$currentType = $type;
        return $type;
    }
    public static function render(?string $type, string $title, string $text): string
    {
        $type = $type ?? self::pmess();
        if (!$type) return ''; // якщо pmess відсутній, нічого не виводимо
        if (!in_array($type, self::ALLOWED, true)) $type = 'info';

        return '
        <div class="pmon-message ' . $type . '" style="animation: fadeOut 1s ease-in-out 60s forwards;">
            <div class="pmon-message-icon"></div>
            <div class="pmon-message-content">
                <div class="pmon-message-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>
                <div class="pmon-message-text">' . $text . '</div>
            </div>
        </div>';
    }
    public static function show(string $key, ?string $type = null): string
    {
        $key = strtolower(trim($key));
        if (!isset(self::$texts[$key])) return '';
        $type = $type ?? self::pmess();
        if (!$type) return '';
        $msg = self::$texts[$key];
        return self::render($type, $msg['title'], $msg['text']);
    }
    public static function fromRequest(): string
    {
        $type = self::pmess();
		$key  = $_GET['i'] ?? null;
		if (!$type || !$key) return '';
		$key = strtolower(trim($key));
		if (!isset(self::$texts[$key])) return '';
		$msg = self::$texts[$key];
		return self::render($type, $msg['title'], $msg['text']);
    }
    public static function info(string $title, string $text): string
    {
        return self::render('info', $title, $text);
    }
    public static function success(string $title, string $text): string
    {
        return self::render('success', $title, $text);
    }
    public static function warning(string $title, string $text): string
    {
        return self::render('warning', $title, $text);
    }
    public static function error(string $title, string $text): string
    {
        return self::render('error', $title, $text);
    }
}
?>
