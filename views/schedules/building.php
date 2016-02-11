<h1>
    <?= sprintf(_('Gebäude "%s" - Raumübersicht'), htmlReady($building->name)) ?>

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
<? for ($i = 0; $i < 10; $i += 1): ?>
    <li></li>
<? endfor; ?>
</ul>

<h1>
    <?= _('Belegung') ?>
</h1>
<?= $this->render_partial('schedule.php', compact('schedule') + array(
    'show_rooms' => true,
)) ?>