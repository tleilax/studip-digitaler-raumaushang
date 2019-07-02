<div>
    <h2>
        <?= _('Hinweise zur Videoaufzeichnung') ?><br>
        <?= htmlReady($room->name) ?>
    </h2>
    <ul class="contact">
        <li>
            <?= _('Die Ausrichtung der Kamera erfolgt (soweit technisch möglich) ausschließlich auf das Lehrpult.') ?>
            <?= _('Der Ton wird von der Mikrofonanlage und eine etwaige Präsentation vom Beamer des Lehrraums aufgezeichnet.') ?>
        </li>
        <li>
            <?= _('Bei Fragen und Problemen wenden Sie sich bitte an:') ?><br>
            <?= Icon::create('mail', Icon::ROLE_INFO)->asImg(36) ?>
            servicedesk@uni-oldenburg.de
        </li>
    </ul>
</div>
