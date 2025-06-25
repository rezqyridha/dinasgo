<?php
// =============================
// KONSTANTA UTAMA SISTEM DINASGO
// =============================

// BASE URL sistem (ubah sesuai nama folder jika dipindah)
define('BASE_URL', '/dinasgo');

// URL asset (jika nanti dipisah folder /assets)
define('ASSETS_URL', BASE_URL . '/assets');

// Path absolut sistem (untuk include file lintas folder)
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define('MODULES_PATH', ROOT_PATH . '/modules');
define('LAYOUTS_PATH', ROOT_PATH . '/layouts');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('AUTH_PATH', ROOT_PATH . '/auth');

// Nama sistem
define('APP_NAME', 'Sistem Manajemen DinasGo');

// Set zona waktu default
date_default_timezone_set('Asia/Makassar');
