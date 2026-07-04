<?php
/**
 * tests/test_home.php
 *
 * Test runner sederhana tanpa dependency eksternal (tidak butuh PHPUnit).
 * Menjalankan:
 *   1. Unit test untuk helper formatRupiah() di koneksi.php
 *   2. Smoke test: memastikan halaman utama (index.php) bisa diakses
 *      lewat PHP built-in server dan mengandung nama situs "BatikAyu".
 *
 * Cara pakai:
 *   php -S 127.0.0.1:8000 -t . &
 *   php tests/test_home.php
 *
 * Environment variables yang dipakai:
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT  -> koneksi database (lihat koneksi.php)
 *   APP_URL -> base URL server PHP built-in, default http://127.0.0.1:8000
 */

$failures = 0;
$passed   = 0;

function check(string $label, bool $condition): void
{
    global $failures, $passed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failures++;
    }
}

echo "== Unit test: formatRupiah() ==\n";

require_once __DIR__ . '/../koneksi.php';

check('formatRupiah(15000) === "Rp15.000"', formatRupiah(15000) === 'Rp15.000');
check('formatRupiah(0) === "Rp0"', formatRupiah(0) === 'Rp0');
check('formatRupiah(1234567) === "Rp1.234.567"', formatRupiah(1234567) === 'Rp1.234.567');

echo "\n== Smoke test: halaman utama dapat diakses ==\n";

$baseUrl = rtrim(getenv('APP_URL') ?: 'http://127.0.0.1:8000', '/');
$homeUrl = $baseUrl . '/index.php';

$context = stream_context_create([
    'http' => ['ignore_errors' => true, 'timeout' => 5],
]);

$body = false;
$http_response_header = [];

for ($i = 0; $i < 10 && $body === false; $i++) {
    $body = @file_get_contents($homeUrl, false, $context);
    if ($body === false) {
        sleep(1);
    }
}

$statusLine = $http_response_header[0] ?? '';

check("GET {$homeUrl} mendapat respons", $body !== false);
check('Halaman utama merespons HTTP 200', strpos($statusLine, '200') !== false);
check('Halaman utama memuat nama situs "BatikAyu"', $body !== false && strpos($body, 'BatikAyu') !== false);

echo "\n== Ringkasan: {$passed} passed, {$failures} failed ==\n";

exit($failures > 0 ? 1 : 0);