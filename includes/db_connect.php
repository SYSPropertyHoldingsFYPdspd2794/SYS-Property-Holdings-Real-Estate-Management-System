<?php
// 安全锁 1：开启严格的 MySQLi 错误报告模式，用异常 (Exception) 代替致命崩溃
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 判断当前运行环境：如果是本地电脑
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        $host = "localhost";
        $user = "root";
        $password = "";
        $dbname = "sys_property_db";
    } 
    // 如果不是本地（那就是跑在 InfinityFree 线上了）
    else {
        $host = "sqlXXX.epizy.com"; // 【必须修改】替换成 InfinityFree 的 MySQL Hostname
        $user = "if0_41857411";
        $password = "你的FTP隐藏密码"; // 【必须修改】填入你的线上密码
        $dbname = "if0_41857411_sys_property_db";
    }

    // 统一建立连接
    $conn = new mysqli($host, $user, $password, $dbname);
    
    // 安全锁 2：强制设定字符集，防止你的中文字符存入数据库变成乱码 "???"
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // 安全锁 3：优雅的崩溃处理（不暴露系统隐私）
    if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
        // 本地测试时，显示详细错误方便你们团队除错 (Debug)
        die("🚨 数据库连接失败 (仅本地可见): " . $e->getMessage());
    } else {
        // 线上运行时，如果数据库宕机，只显示给导师和用户友好的提示，绝不暴露后台信息
        die("🚧 SYS Property Holdings 系统维护中，无法连接到数据中心，请稍后再试。");
    }
}
?>