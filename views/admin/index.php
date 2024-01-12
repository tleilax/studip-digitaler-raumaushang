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
            <span class="required"><?= _('Nutzername') ?></span>
            <input type="text" required name="username" value="<?= htmlReady($auth['username'] ?? '') ?>">
        </label>

        <label>
            <span class="required"><?= _('Passwort') ?></span>
            <input type="text" required name="password" value="<?= htmlReady($auth['password'] ?? '') ?>">
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _('Text für die Hilfeseite') ?></legend>

        <label>
            <?= _('HTML-Inhalt') ?>
            <textarea name="help_content"><?= htmlReady($help_content) ?></textarea>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\ResetButton::create(_('Zurücksetzen')) ?>
    </footer>
</form>
