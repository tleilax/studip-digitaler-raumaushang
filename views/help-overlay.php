<div>
    <h2><?= sprintf(_('Rauminformationen %s'), htmlReady($room->name)) ?></h2>
    <hr>
    <dl>
    <? foreach ($properties as $key => $value): ?>
        <dt><?= htmlReady($key) ?></dt>
        <dd><?= htmlReady($value) ?></dd>
    <? endforeach; ?>
    </dl>
    
    <? if (!empty($properties['Beamer'])): ?>
        <hr>
        <p>
            Bei Problemen mit dem Beamer wenden Sie sich bitte an:
        </p>
        <ul class="contact">
            <li>IT-Support</li>
            <li>
                <?= Icon::create('phone', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
                04441 15 <strong>432</strong>
            </li>
            <li>
                <?= Icon::create('mail', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
                it-support@uni-vechta.de
            </li>
        </ul>
    <? endif; ?>

    <hr>
    <p>
        Bei Raumleerständen oder bei einem Raumtausch bitte eine Meldung an das
    </p>
    <ul class="contact">
        <li>Raumbüro</li>
        <li>
            <?= Icon::create('phone', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            04441 15 <strong>200</strong>
        </li>
        <li>
            <?= Icon::create('mail', 'info')->asImg(36, ['style' => 'vertical-align: text-top']) ?>
            info.raumplanung@uni-vechta.de
        </li>
    </ul>
</div>
