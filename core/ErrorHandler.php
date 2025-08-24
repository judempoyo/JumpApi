<?php
class ErrorHandler
{
    private static $errorCodes = [
        E_ERROR => 'FATAL_ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE_ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT_ERROR',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];

    public static function handleException(Throwable $exception)
    {
        $errorId = uniqid('err_', true);
        $errorCode = self::getExceptionErrorCode($exception);
        
        $error = [
            'error_id' => $errorId,
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'error_code' => $errorCode,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => date('c')
        ];

        // Ajouter la trace stack seulement en développement
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $error['trace'] = array_slice($exception->getTrace(), 0, 10);
        }

        // Log détaillé
        self::logError($error, $exception);

        // Envoyer la réponse appropriée
        if ($exception instanceof PDOException) {
            self::handleDatabaseError($error, $errorId);
        } elseif ($exception instanceof SessionException) {
            Response::unauthorized('Session invalid', 'INVALID_SESSION');
        } elseif ($exception instanceof AuthException) {
            Response::unauthorized('Authentication failed', 'AUTH_FAILED');
        } else {
            self::handleGenericError($error, $errorId);
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        // Ne pas gérer les erreurs si elles sont supprimées par @
        if (error_reporting() === 0) {
            return false;
        }

        $errorCode = self::$errorCodes[$errno] ?? 'UNKNOWN_ERROR';
        
        $error = [
            'error_id' => uniqid('err_', true),
            'message' => $errstr,
            'code' => $errno,
            'error_code' => $errorCode,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('c')
        ];

        self::logError($error);

        // Convertir en exception pour une gestion uniforme
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorId = uniqid('fatal_', true);
            
            $errorData = [
                'error_id' => $errorId,
                'message' => $error['message'],
                'code' => $error['type'],
                'error_code' => 'FATAL_SHUTDOWN',
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('c')
            ];

            self::logError($errorData);
            
            Response::error(
                'Internal Server Error', 
                500, 
                (defined('ENVIRONMENT') && ENVIRONMENT === 'development') ? $errorData : null,
                'FATAL_ERROR'
            );
        }
    }

    private static function handleDatabaseError($error, $errorId)
    {
        $userMessage = 'Database error occurred';
        $logMessage = "Database Error [{$errorId}]: {$error['message']}";

        error_log($logMessage);

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            Response::error($userMessage, 500, $error, 'DATABASE_ERROR');
        } else {
            Response::error($userMessage, 500, ['error_id' => $errorId], 'DATABASE_ERROR');
        }
    }

    private static function handleGenericError($error, $errorId)
    {
        $userMessage = 'Internal Server Error';
        
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            Response::error($userMessage, 500, $error, $error['error_code']);
        } else {
            Response::error($userMessage, 500, ['error_id' => $errorId], $error['error_code']);
        }
    }

    private static function logError($error, $exception = null)
    {
        $logData = [
            'error_id' => $error['error_id'],
            'message' => $error['message'],
            'code' => $error['code'],
            'error_code' => $error['error_code'],
            'file' => $error['file'],
            'line' => $error['line'],
            'timestamp' => $error['timestamp'],
            'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        if ($exception instanceof PDOException) {
            $logData['sql_state'] = $exception->errorInfo[0] ?? '';
            $logData['driver_code'] = $exception->errorInfo[1] ?? '';
        }

        error_log('ERROR: ' . json_encode($logData));

        // Log dans un fichier dédié
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/errors-' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private static function getExceptionErrorCode($exception)
    {
        if ($exception instanceof PDOException) {
            return 'DATABASE_ERROR';
        } elseif ($exception instanceof InvalidArgumentException) {
            return 'INVALID_ARGUMENT';
        } elseif ($exception instanceof RuntimeException) {
            return 'RUNTIME_ERROR';
        } elseif ($exception instanceof LogicException) {
            return 'LOGIC_ERROR';
        } elseif ($exception instanceof SessionException) {
            return 'SESSION_ERROR';
        } elseif ($exception instanceof AuthException) {
            return 'AUTH_ERROR';
        }
        
        return 'UNKNOWN_ERROR';
    }
}

// Exceptions personnalisées
class SessionException extends Exception {}
class AuthException extends Exception {}

// Configuration des handlers
set_exception_handler(['ErrorHandler', 'handleException']);
set_error_handler(['ErrorHandler', 'handleError']);
register_shutdown_function(['ErrorHandler', 'handleShutdown']);

// Configuration des niveaux d'erreur
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
}

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
?>