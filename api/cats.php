<?php


session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');


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
    'mime_types' => 'jpg,png,gif',
    'format' => 'json',
    'order' => 'RANDOM'
]);

$request_url = 'https://api.thecatapi.com/v1/images/search?' . $query;
$request_headers = [
    'Accept: application/json',
];


$api_key = 'live_0ep0Hqwg6sEzfvt9jMWw3rymwETfMawjZRF3TCkO9qiPdYwD4DOq7N7Q5AQicQGi';
$request_headers[] = 'x-api-key: ' . $api_key;


try {
    $curl = curl_init($request_url);

    if ($curl === false) {
        throw new RuntimeException('Unable to initialize the Cat API request.');
    }

    curl_setopt_array(
        $curl,
        [
            CURLOPT_RETURNTRANSFER => true, // Return the response as a string
            CURLOPT_HTTPHEADER => $request_headers, // Set request headers
            CURLOPT_CONNECTTIMEOUT => 5, // 5 seconds to connect
            CURLOPT_TIMEOUT => 15,// 15 seconds for the entire request
        ]
    );

    $response_body = curl_exec($curl); // Execute the request and get the response body
    $response_status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE); // Get the HTTP status code
    $curl_error = curl_error($curl); // Get any cURL error message

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
        $response_body, // Decode the JSON response
        true, // Decode as associative array
        512, // Maximum depth
        JSON_THROW_ON_ERROR // Throw exception on JSON errors
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


        $cat = [ // Create a new cat array with validated and sanitized data
            'id' => substr((string) ($upstream_cat['id'] ?? ''), 0, 100),
            'url' => $image_url,
            'width' => max(0, (int) ($upstream_cat['width'] ?? 0)), // Validate and sanitize width
            'height' => max(0, (int) ($upstream_cat['height'] ?? 0)) // Validate and sanitize height
        ];

        $breed = $upstream_cat['breeds'][0] ?? null;  // Get the first breed if available

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
