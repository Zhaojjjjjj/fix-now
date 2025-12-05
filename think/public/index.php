<?php
// [DEBUG] 如果看到这行字，说明 Web 服务器和 PHP 基本正常
// echo "Step 1: Start <br>"; 

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// 强制开启错误提示
ini_set('display_errors', 1);
error_reporting(E_ALL);

// echo "Step 2: Config Loaded <br>";

require __DIR__ . '/../vendor/autoload.php';

// echo "Step 3: Autoload OK <br>";

// 执行HTTP应用并响应
try {
    $http = (new App())->http;
    $response = $http->run();
    $response->send();
    $http->end($response);
} catch (\Throwable $e) {
    echo "<h1>Fatal Error Caught in index.php</h1>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
