<?php
class ErrorHandler
{
  public static function handleException(Throwable $exception)
  {
    $error = [
      'message' => $exception->getMessage(),
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
      'trace' => $exception->getTrace()
    ];

    error_log("Exception: " . json_encode($error));

    if (getenv('ENVIRONMENT') === 'development') {
      Response::error('Internal Server Error', 500, $error);
    } else {
      Response::error('Internal Server Error', 500);
    }
  }

  public static function handleError($errno, $errstr, $errfile, $errline)
  {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
  }
}

set_exception_handler(['ErrorHandler', 'handleException']);
set_error_handler(['ErrorHandler', 'handleError']);
?>