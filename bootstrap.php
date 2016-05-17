<?php
    require_once 'vendor/trails/trails.php';
    require_once 'app/controllers/studip_controller.php';
    require_once 'app/controllers/plugin_controller.php';

    StudipAutoloader::addAutoloadPath(__DIR__ . '/classes');
    StudipAutoloader::addAutoloadPath(__DIR__ . '/classes', 'Raumaushang');
    StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
    StudipAutoloader::addAutoloadPath(__DIR__ . '/models', 'Raumaushang');

