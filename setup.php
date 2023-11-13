<?php

require __DIR__ . '/autoload.php';

use eru123\orm\ORM;

$tbl_uploads = <<<SQL
CREATE TABLE IF NOT EXISTS `uploads` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `name` varchar(255) DEFAULT NULL,
    `size` bigint(20) DEFAULT NULL,
    `type` varchar(255) DEFAULT NULL,
    `path` varchar(255) DEFAULT NULL,
    `hash` varchar(255) DEFAULT NULL,
    `private` tinyint(1) DEFAULT 0,
    `temporary` tinyint(1) DEFAULT 0,
    `batch_id` varchar(255) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `uploads_user_id_index` (`user_id`)
);
SQL;

$tbl_admin = <<<SQL
CREATE TABLE IF NOT EXISTS `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user` varchar(255) DEFAULT NULL,
    `hash` varchar(255) DEFAULT NULL,
    `secret` varchar(255) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `admin_user_unique` (`user`)
);

$tbl_users = <<<SQL
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) DEFAULT NULL,
    `key` varchar(255) DEFAULT NULL,
    `token` varchar(255) DEFAULT NULL,
    `name` varchar(255) DEFAULT NULL,
    `description` varchar(255) DEFAULT NULL,
    `rules` JSON DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_admin_id_key_unique` (`admin_id`, `key`),
    INDEX `users_key_index` (`key`),
    INDEX `users_token_unique` (`token`)
);
SQL;

$default_admin_password = password_hash(venv('DEFAULT_ADMIN_PASSWORD', 'admin'), PASSWORD_BCRYPT);
$tbl_default_admin = <<<SQL
INSERT INTO `admin` (`user`, `hash`)
VALUES ('admin', '$default_admin_password');
SQL;

try {
    $queries = [
        $tbl_uploads,
        $tbl_admin,
        $tbl_default_admin,
        $tbl_users,
    ];

    foreach ($queries as $query) {
        $stmt = ORM::raw($query)->exec();
        if ($stmt->errorCode() !== '00000') {
            throw new Exception('Query failed: ' . $stmt->errorInfo()[2]);
        }
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
