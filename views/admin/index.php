<?php
/**
 * @var AdminController $controller
 * @var bool $free_bookings
 * @var bool $qrcodes
 * @var string $help_content
 */
?>
<form action="<?= $controller->store() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Einstellungen') ?></legend>

        <label>
            <input type="checkbox" name="qrcodes" value="1"
                   <? if ($qrcodes) echo 'checked'; ?>>
            <?= _('QR-Codes zu Veranstaltungsseiten anzeigen') ?>
        </label>

        <label>
            <input type="checkbox" name="free_bookings" value="1"
                   <? if ($free_bookings) echo 'checked'; ?>>
            <?= _('Auch freie Raumbuchungen anzeigen') ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('API-Authorisierung') ?></legend>

        <label>
            <?= _('Nutzername') ?>
            <input type="text" name="username"
                   value="<?= htmlReady($auth['username'] ?? '') ?>"
                   placeholder="api@raumaushang">
        </label>

        <label>
            <?= _('Passwort') ?>
            <input type="text" name="password"
                   value="<?= htmlReady($auth['password'] ?? '') ?>"
                   placeholder="raumaushang">
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Text für die Hilfeseite') ?></legend>

        <label>
            <?= _('HTML-Inhalt') ?>
            <textarea name="help_content"><?= htmlReady($help_content) ?></textarea>

            <p>
                <?= _('Spezielle Angaben zu Fragen zum Beamer können in <code>{{beamer}}...{{/beamer}}</code> verschaltet werden.') ?><br>
                <?= _('Auszeichnungen für Telefonnummern bzw. eMails können mit den CSS-Klassen <code>phone</code> bzw. <code>mail</code> ausgezeichnet werden.') ?>
                <?= _('Diese Elemente müssen in einem Element mit der CSS-Klasse <code>contact</code> verschachtelt sein.') ?>
            </p>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\ResetButton::create(_('Zurücksetzen')) ?>
    </footer>
</form>
