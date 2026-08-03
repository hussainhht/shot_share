<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

/**
 * Send one safe JSON response and stop execution.
 *
 * @param array<string, mixed> $payload
 */
function send_json_response(int $status, array $payload): void
{
    http_response_code($status);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    send_json_response(405, ['message' => 'Method not allowed.']);
}

if (!isset($_SESSION['user_id'])) {
    send_json_response(401, ['message' => 'Authentication required.']);
}

// Release the session lock before waiting for the upstream image service.
session_write_close();

$limit = 12;

if (isset($_GET['limit'])) {
    $validated_limit = filter_var(
        $_GET['limit'],
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 20
            ]
        ]
    );

    if ($validated_limit === false) {
        send_json_response(
            400,
            ['message' => 'The limit must be between 1 and 20.']
        );
    }

    $limit = $validated_limit;
}

$query = http_build_query([
    'limit' => $limit,
    'size' => 'med',
    'mime_types' => 'jpg,png',
    'format' => 'json',
    'order' => 'RANDOM'
]);

$request_url = 'https://api.thecatapi.com/v1/images/search?' . $query;
$request_headers = [
    'Accept: application/json',
    'User-Agent: Shot-Share/1.0'
];

// The public image search currently works without a key. If a server-side
// CAT_API_KEY is configured, it is added only to the outgoing request.
$api_key = trim((string) getenv('CAT_API_KEY'));

if ($api_key !== '' && !preg_match('/[\r\n]/', $api_key)) {
    $request_headers[] = 'x-api-key: ' . $api_key;
}

try {
    $curl = curl_init($request_url);

    if ($curl === false) {
        throw new RuntimeException('Unable to initialize the Cat API request.');
    }

    curl_setopt_array(
        $curl,
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $request_headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]
    );

    $response_body = curl_exec($curl);
    $response_status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $curl_error = curl_error($curl);

    curl_close($curl);

    if ($response_body === false) {
        throw new RuntimeException(
            $curl_error !== '' ? $curl_error : 'The Cat API request failed.'
        );
    }

    if ($response_status < 200 || $response_status >= 300) {
        throw new RuntimeException(
            'The Cat API returned HTTP ' . $response_status . '.'
        );
    }

    $upstream_cats = json_decode(
        $response_body,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($upstream_cats)) {
        throw new RuntimeException('The Cat API returned an invalid payload.');
    }

    $cats = [];

    foreach ($upstream_cats as $upstream_cat) {
        if (!is_array($upstream_cat)) {
            continue;
        }

        $image_url = filter_var(
            $upstream_cat['url'] ?? null,
            FILTER_VALIDATE_URL
        );

        if ($image_url === false) {
            continue;
        }

        $scheme = strtolower((string) parse_url($image_url, PHP_URL_SCHEME));

        if ($scheme !== 'https') {
            continue;
        }

        $cat = [
            'id' => substr((string) ($upstream_cat['id'] ?? ''), 0, 100),
            'url' => $image_url,
            'width' => max(0, (int) ($upstream_cat['width'] ?? 0)),
            'height' => max(0, (int) ($upstream_cat['height'] ?? 0))
        ];

        $breed = $upstream_cat['breeds'][0] ?? null;

        if (is_array($breed) && !empty($breed['name'])) {
            $cat['breed'] = [
                'name' => substr((string) $breed['name'], 0, 100),
                'origin' => substr((string) ($breed['origin'] ?? ''), 0, 100)
            ];
        }

        $cats[] = $cat;

        if (count($cats) >= $limit) {
            break;
        }
    }

    if ($cats === []) {
        throw new RuntimeException('The Cat API returned no usable images.');
    }

    send_json_response(200, ['cats' => $cats]);

} catch (Throwable $error) {
    error_log('[Shot Share Cats] ' . $error->getMessage());

    send_json_response(
        502,
        ['message' => 'Unable to load cats right now.']
    );
}
