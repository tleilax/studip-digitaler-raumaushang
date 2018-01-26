<?php
$post = ['r' => time()];
?>
<!doctype html>
<html>
<head>
    <base href="<?= htmlReady($GLOBALS['ABSOLUTE_URI_STUDIP']) ?>">
    <title>Raumaushang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="current-timestamp" content="<?= strtotime('monday this week 0:00:00') ?>">
<? foreach ((array)@$plugin_styles as $style): ?>
    <link href="<?= is_object($style) ? $style->getDownloadLink() : URLHelper::getLink($plugin_base . $style, $post) ?>" rel="stylesheet" type="text/css">
<? endforeach; ?>
    <script>
    var Raumaushang = {
        api: {
            auth: <?= json_encode($config['auth']) ?>,
            url: <?= json_encode(URLHelper::getURL('api.php/', [], true)) ?>
        },
        current_id: <?= json_encode($building->id) ?>,
        maxPages: <?= json_encode($max) ?>,
        version: <?= json_encode($plugin_version) ?>,
        now: <?= json_encode(date('c')) ?>,
        timezone: <?= json_encode(date('e')) ?>
    };
    </script>
</head>
<body id="debug">
    <?= $content_for_layout ?>

    <script src="<?= Assets::javascript_path('jquery/jquery-3.1.0.js') ?>"></script>
    <script src="<?= Assets::javascript_path('vendor/lodash.underscore-3.10.1.js') ?>"></script>

<? foreach ((array)@$plugin_scripts as $script): ?>
    <script src="<?= URLHelper::getLink($plugin_base . $script, $post) ?>"></script>
<? endforeach; ?>
</body>
</html>
