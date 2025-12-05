<?php
// 这是一个独立的数据库连接测试脚本，不依赖 ThinkPHP 框架
// 用于排查是否是数据库连接导致的问题

// 请根据你的线上 .env 或 database.php 配置修改以下信息
$host = '127.0.0.1';
$db   = 'fix_now'; // 你的数据库名
$user = 'fix_now'; // 你的数据库用户名
$pass = 'password'; // 你的数据库密码
$port = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

echo "<h1>Database Connection Test</h1>";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green'>SUCCESS: Connected to database '$db'</p>";
    
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "<p>MySQL Version: $version</p>";
    
} catch (\PDOException $e) {
    echo "<p style='color:red'>FAILURE: Could not connect to database.</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>JSON Extension: " . (extension_loaded('json') ? 'Loaded' : 'Not Loaded') . "</p>";
echo "<p>PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "</p>";
