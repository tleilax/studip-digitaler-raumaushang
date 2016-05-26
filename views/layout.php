<?php
    $scripts = [
        'vendor/modernizr.js',
        'jquery/jquery-1.8.2.js',
    ];
?>
<!doctype html>
<html>
<head>
    <title>Raumaushang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="current-timestamp" content="<?= strtotime('monday this week 0:00:00') ?>">
    <meta name="course-url-template" content="<?= $controller->absolute_uri('dispatch.php/course/details?sem_id=#{course_id}&cancel_login=1', [], true) ?>">
<? foreach ((array)@$plugin_styles as $style): ?>
  <? if (Studip\ENV === 'production'): ?>
    <link href="<?= $style ?>" rel="stylesheet" type="text/css">
  <? else: ?>
    <link href="<?= URLHelper::getURL($style, ['r' => time()]) ?>" rel="stylesheet" type="text/css">
  <? endif; ?>
<? endforeach; ?>
    <script>
    var Raumaushang = {
        api: {
            auth: <?= json_encode($config['auth']) ?>,
            url: <?= json_encode(URLHelper::getURL('plugins.php/restipplugin/api', [], true)) ?>
        }
    };
    </script>
</head>
<body>
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
        <?= Icon::create('80/black/info-circle')->render(Icon::SVG) ?>
    </button>
    <div id="help-overlay"><?= $this->render_partial('help-overlay.php') ?></div>

    <div id="clock"><?= date('H:i:s') ?></div>

<? if ($debug): ?>
    <small id="debug-time"><?= date('d.m.Y H:i:s') ?></small>
<? endif; ?>

<? foreach ($scripts as $script): ?>
    <script src="<?= Assets::javascript_path($script) ?>"></script>
<? endforeach; ?>
<? foreach ((array)@$plugin_scripts as $script): ?>
  <? if ($debug): ?>
    <script src="<?= URLHelper::getURL($script, ['r' => time()]) ?>"></script>
  <? else: ?>
    <script src="<?= $script ?>"></script>
  <? endif; ?>
<? endforeach; ?>
</body>
</html>
