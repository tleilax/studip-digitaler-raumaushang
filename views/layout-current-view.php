<?php
$asset_postfix = $debug ? '?r=' . time() : '';
?>
<!doctype html>
<html>
<head>
    <base href="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>">
    <title>Raumaushang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="current-timestamp" content="<?= strtotime('monday this week 0:00:00') ?>">
    
    <!-- meta http-equiv="refresh" content="30" -->
    
<? foreach ((array)@$plugin_styles as $style): ?>
    <link href="<?= $plugin_base . $style . $asset_postfix ?>" rel="stylesheet" type="text/css">
<? endforeach; ?>
    <script>
    var Raumaushang = {
        api: {
            auth: <?= json_encode($config['auth']) ?>,
            url: <?= json_encode(URLHelper::getURL('plugins.php/restipplugin/api/', [], true)) ?>
        },
        current_id: <?= json_encode($building->id) ?>,
        maxPages: <?= json_encode($max) ?>
    };
    </script>
</head>
<body id="debug">
    <?= $content_for_layout ?>

    <script src="<?= Assets::javascript_path('jquery/jquery-1.8.2.js') ?>"></script>

    <script src="<?= $plugin_base ?>/assets/base64.js<?= $asset_postfix ?>"></script>
    <script src="<?= $plugin_base ?>/assets/date.format.js<?= $asset_postfix ?>"></script>
    <script src="<?= $plugin_base ?>/assets/countdown.js<?= $asset_postfix ?>"></script>

<? foreach ((array)@$plugin_scripts as $script): ?>
    <script src="<?= $plugin_base . $script . $asset_postfix ?>"></script>
<? endforeach; ?>
</body>
</html>
