<?php
/**
 * @var Room $room
 * @var array $properties
 */
?>
<?php
$template = Config::get()->RAUMAUSHANG_HELP_OVERLAY;
if ($template) {
    $has_beamer = !empty($properties['Beamer']);

    $replacement = $has_beamer ? '$1' : '';

    $template = preg_replace('/\{\{beamer}}(.*)\{\{\/beamer}}/xms', $replacement, $template);
}
?>
<div>
    <h2><?= sprintf(_('Rauminformationen %s'), htmlReady($room->name)) ?></h2>
    <hr>
    <dl>
    <? foreach ($properties as $key => $value): ?>
        <dt><?= htmlReady($key) ?></dt>
        <dd><?= htmlReady($value) ?></dd>
    <? endforeach; ?>
    </dl>

    <hr>

<? if ($template): ?>
    <?= $template ?>
<? else: ?>
    <? if (!empty($properties['Beamer'])): ?>
        <p>
            Bei Problemen mit der Medientechnik wenden Sie sich bitte an:
        </p>
        <ul class="contact">
            <li>Hörsaal-/Veranstaltungstechnik</li>
        </ul>
        <ul class="contact">
            <li class="phone">
                0441/798 -<strong>2192</strong>/-<strong>3193</strong>/-<strong>4468</strong>
            </li>
        </ul>
        <ul class="contact">
            <li class="mail">
                bis-veranstaltungstechnik@uol.de
            </li>
        </ul>

        <hr>
    <? endif; ?>
        <p>
            Bei Raumleerständen oder bei einem Raumtausch bitte eine Meldung an das
        </p>
        <ul class="contact">
            <li>Raumbüro</li>
            <li class="phone">
                798 <strong>2483</strong> / <strong>2545</strong> / <strong>4273</strong>
            </li>
            <li class="mail">
                raumbuero@uni-oldenburg.de
            </li>
        </ul>
<?endif; ?>
</div>
