<?php
    $scripts = [
        'jquery/jquery-1.8.2.js',
    ];
?>
<!doctype html>
<html>
<head>
    <title>Raumaushang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="api-url" content="<?= URLHelper::getURL('plugins.php/restipplugin/api', [], true) ?>">
<? foreach ((array)@$plugin_styles as $style): ?>
  <? if (Studip\ENV === 'production'): ?>
    <link href="<?= $style ?>" rel="stylesheet" type="text/css">
  <? else: ?>
    <link href="<?= URLHelper::getURL($style, ['r' => time()]) ?>" rel="stylesheet" type="text/css">
  <? endif; ?>
<? endforeach; ?>
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
    <script>
    var Raumaushang = {
        auth: <?= json_encode($config['auth']) ?>
    };
    </script>
    <div id="overlay">
        <?= Assets::img('ajax-indicator-black.svg') ?>
        <?= _('Lade') ?> &hellip;
    </div>
</body>
</html>
