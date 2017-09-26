<?php
$post = ['r' => time()];
?>
<!doctype html>
<html>
<head>
    <title>Raumaushang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="current-timestamp" content="<?= date('c', strtotime('monday this week 0:00:00')) ?>">
    <meta name="course-url-template" content="<?= $controller->absolute_link('dispatch.php/course/details?sem_id=#{course_id}&cancel_login=1', [], true) ?>">
<? foreach ((array)@$plugin_styles as $style): ?>
    <link href="<?= is_object($style) ? $style->getDownloadLink() : URLHelper::getLink($plugin_base . $style, $post) ?>" rel="stylesheet" type="text/css">
<? endforeach; ?>
    <script>
    var Raumaushang = {
        api: {
            auth: <?= json_encode($config['auth']) ?>,
            url: <?= json_encode(URLHelper::getURL('plugins.php/restipplugin/api', [], true)) ?>
        },
        version: <?= json_encode($plugin_version) ?>,
        now: <?= json_encode(date('c')) ?>,
        timezone: <?= json_encode(date('e')) ?>
    };
    </script>
</head>
<body data-current-day="<?= date('N') ?>">
<? if ($debug): ?>
    <progress value="100" max="100"></progress>
<? endif; ?>

    <?= $content_for_layout ?>

    <div id="loading-overlay">
        <?= Assets::img('ajax-indicator-black.svg') ?>
        <?= _('Lade') ?> &hellip;
    </div>
    <div id="course-overlay"></div>

    <button id="help-overlay-switch">
        <?= Icon::create('info-circle', 'info')->asImg(80) ?>
    </button>
    <div id="help-overlay"><?= $this->render_partial('help-overlay.php') ?></div>

    <div id="clock"><?= date('H:i') ?></div>

<? if ($debug): ?>
    <small id="debug-time"><?= date('d.m.Y H:i:s') ?></small>
<? endif; ?>

    <script src="<?= Assets::javascript_path('vendor/modernizr-2.8.3.js') ?>"></script>
    <script src="<?= Assets::javascript_path('jquery/jquery-1.11.3.js') ?>"></script>

<? foreach ((array)@$plugin_scripts as $script): ?>
    <script src="<?= URLHelper::getLink($plugin_base . $script, $post) ?>"></script>
<? endforeach; ?>
</body>
</html>
