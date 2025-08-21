<?php
class Response
{
  public static function send($data, $status = 200, $message = 'Success')
  {
    http_response_code($status);

    $response = [
      'status' => $status,
      'message' => $message,
      'data' => $data,
      'timestamp' => date('c')
    ];

    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function error($message, $status = 500, $details = null)
  {
    http_response_code($status);

    $response = [
      'status' => $status,
      'message' => $message,
      'details' => $details,
      'timestamp' => date('c')
    ];

    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function notFound($resource = 'Resource')
  {
    self::error("$resource not found", 404);
  }

  public static function badRequest($message = 'Bad request')
  {
    self::error($message, 400);
  }

  public static function unauthorized($message = 'Unauthorized')
  {
    self::error($message, 401);
  }

  public static function forbidden($message = 'Forbidden')
  {
    self::error($message, 403);
  }
}
?>