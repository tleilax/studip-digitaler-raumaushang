<h1>
    <?= sprintf(_('Gebäude "%s" - Raum "%s" - Belegung'),
                htmlReady($room->parent->name),
                htmlReady($room->name)) ?>

    <a href="<?= $controller->url_for('schedules/building/' . $room->parent->id) ?>" class="back-link">
        <?= _('Zurück zur Raumübersicht') ?>
    </a>
</h1>

<?= $this->render_partial('schedule.php', compact('schedule')) ?>
