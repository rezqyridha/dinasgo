<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/dinasgo/config/constants.php';
?>
<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= isset($pageTitle) ? "$pageTitle | " . APP_NAME : APP_NAME ?></title>
    <link rel="icon" href="<?= ASSETS_URL ?>/images/brand-logos/PUPR.png" type="image/x-icon">

    <!-- CSS Libraries -->
    <link href="<?= ASSETS_URL ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/styles.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/custom.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/icons.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/libs/node-waves/waves.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/libs/simplebar/simplebar.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/@simonwep/pickr/themes/nano.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/jsvectormap/css/jsvectormap.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/libs/swiper/swiper-bundle.min.css">

    <!-- Optional JS preload -->
    <script src="<?= ASSETS_URL ?>/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
</head>