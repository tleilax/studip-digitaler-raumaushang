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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
    <code></code>
    <?= $content_for_layout ?>
<? foreach ($scripts as $script): ?>
    <script src="<?= Assets::javascript_path($script) ?>"></script>
<? endforeach; ?>
<? foreach ((array)@$plugin_scripts as $script): ?>
  <? if (Studip\ENV === 'production'): ?>
    <script src="<?= $script ?>"></script>
  <? else: ?>
    <script src="<?= URLHelper::getURL($script, ['r' => time()]) ?>"></script>
  <? endif; ?>
<? endforeach; ?>
    <div id="loading-overlay">
        <?= Assets::img('ajax-indicator-black.svg') ?>
        <?= _('Lade') ?> &hellip;
    </div>
    <div id="course-overlay"></div>
<? if (Studip\ENV === 'development'): ?>
    <progress value="100" max="100"></progress>
<? endif; ?>
</body>
</html>
