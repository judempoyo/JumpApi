<?php
class Response
{
    private static $corsAllowedOrigins = [];
    private static $rateLimitInfo = [];
    private static $cachedResponses = [];

    public static function initialize()
    {
        self::$corsAllowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    public static function send($data, $status = 200, $message = 'Success', $headers = [])
    {
        self::setCorsHeaders();
        self::setCacheHeaders();
        self::setSecurityHeaders();
        
        http_response_code($status);

        $response = [
            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'message' => $message,
            'data' => self::sanitizeOutput($data),
            'timestamp' => date('c'),
            'version' => defined('API_VERSION') ? API_VERSION : 'v1'
        ];

        if (!empty(self::$rateLimitInfo)) {
            $response['rate_limit'] = self::$rateLimitInfo;
        }

        foreach ($headers as $header => $value) {
            header("$header: $value");
        }

        header('Content-Type: application/json; charset=utf-8');
        
        $jsonResponse = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            header('Content-Encoding: gzip');
            echo gzencode($jsonResponse, 9);
        } else {
            echo $jsonResponse;
        }
        
        exit;
    }

    public static function error($message, $status = 500, $details = null, $errorCode = null)
    {
        self::setCorsHeaders();
        self::setSecurityHeaders();
        
        http_response_code($status);

        $errorResponse = [
            'success' => false,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('c'),
            'version' => defined('API_VERSION') ? API_VERSION : 'v1'
        ];

        if ($errorCode !== null) {
            $errorResponse['error_code'] = $errorCode;
        }

        if ($details !== null && (defined('ENVIRONMENT') && ENVIRONMENT === 'development')) {
            $errorResponse['details'] = self::sanitizeErrorDetails($details);
        }

        if (!empty(self::$rateLimitInfo)) {
            $errorResponse['rate_limit'] = self::$rateLimitInfo;
        }

        header('Content-Type: application/json; charset=utf-8');
        
        $jsonResponse = json_encode($errorResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo $jsonResponse;
        
        exit;
    }

    public static function notFound($resource = 'Resource', $errorCode = 'NOT_FOUND')
    {
        self::error("$resource not found", 404, null, $errorCode);
    }

    public static function badRequest($message = 'Bad request', $details = null, $errorCode = 'BAD_REQUEST')
    {
        self::error($message, 400, $details, $errorCode);
    }

    public static function unauthorized($message = 'Unauthorized', $errorCode = 'UNAUTHORIZED')
    {
        self::error($message, 401, null, $errorCode);
    }

    public static function forbidden($message = 'Forbidden', $errorCode = 'FORBIDDEN')
    {
        self::error($message, 403, null, $errorCode);
    }

    public static function tooManyRequests($retryAfter = 60, $errorCode = 'RATE_LIMITED')
    {
        header('Retry-After: ' . $retryAfter);
        self::error('Too many requests', 429, ['retry_after' => $retryAfter], $errorCode);
    }

    public static function setRateLimitInfo($remaining, $limit, $reset)
    {
        self::$rateLimitInfo = [
            'remaining' => $remaining,
            'limit' => $limit,
            'reset' => $reset
        ];
        
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $reset);
    }

    public static function paginated($data, $pagination, $status = 200, $message = 'Success')
    {
        $response = [
            'data' => $data,
            'pagination' => $pagination
        ];
        
        self::send($response, $status, $message);
    }

    private static function setCorsHeaders()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', self::$corsAllowedOrigins) || in_array($origin, self::$corsAllowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept, charset, boundary, Content-Length, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 3600');
    }

    private static function setCacheHeaders()
    {
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');
    }

    private static function setSecurityHeaders()
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            header('Content-Security-Policy: default-src \'self\'');
        }
    }

    private static function sanitizeOutput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeOutput'], $data);
        } elseif (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        } elseif (is_object($data)) {
            $array = (array)$data;
            $sanitized = [];
            foreach ($array as $key => $value) {
                $sanitized[$key] = self::sanitizeOutput($value);
            }
            return (object)$sanitized;
        }
        return $data;
    }

    private static function sanitizeErrorDetails($details)
    {
        if (is_array($details)) {
            $sanitized = [];
            foreach ($details as $key => $value) {
                if ($key === 'trace') {
                    if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
                        continue;
                    }
                    $sanitized[$key] = array_slice($value, 0, 5);
                } else {
                    $sanitized[$key] = self::sanitizeOutput($value);
                }
            }
            return $sanitized;
        }
        return self::sanitizeOutput($details);
    }

    public static function cacheResponse($key, $data, $ttl = 300)
    {
        self::$cachedResponses[$key] = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
    }

    public static function getCachedResponse($key)
    {
        if (isset(self::$cachedResponses[$key]) && self::$cachedResponses[$key]['expires'] > time()) {
            return self::$cachedResponses[$key]['data'];
        }
        return null;
    }
}

// Initialiser la classe
Response::initialize();
?>