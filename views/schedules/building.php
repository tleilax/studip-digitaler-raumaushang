<?php
/**
 * @var SchedulesController $controller
 * @var Raumaushang\Resources\Objekt $building
 * @var Raumaushang\Resources\Objekt[] $resources
 */
?>
<h1 class="flex-heading">
    <?= sprintf(_('Gebäude "%s" - Raumübersicht'), htmlReady($building->name)) ?>

    <div>
        <a href="<?= $controller->current($building) ?>">
            <?= _('Aktuelle Sicht') ?>
        </a>
    <? if (STUDIP\ENV === 'development'): ?>
        /
        <a href="<?= $controller->debug('current', $building) ?>">
            <?= _('Debug') ?>
        </a>
    <? endif; ?>
    </div>

    <a href="<?= $controller->url_for('schedules/index') ?>" class="back-link">
        <?= _('Zurück zur Gebäudeübersicht') ?>
    </a>
</h1>

<ul class="raumaushang-list">
<? foreach ($resources as $resource): ?>
    <li>
        <a href="<?= $controller->url_for('schedules/room/' . $resource->id) ?>">
            <?= htmlReady($resource->name) ?>
            (<?= htmlReady($resource->category->name) ?>)
        <? if ($resource->description): ?>
            <small><?= htmlReady($resource->description) ?></small>
        <? endif; ?>
        </a>
    </li>
<? endforeach; ?>
</ul>
