<?php
final class AdminController extends \Raumaushang\Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/resources/raumaushang/admin');

        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }

    public function index_action(): void
    {
        $this->auth = Config::get()->RAUMAUSHANG_AUTH;
        $this->free_bookings = Config::get()->RAUMAUSHANG_SHOW_FREE_BOOKINGS;
        $this->help_content = Config::get()->RAUMAUSHANG_HELP_OVERLAY;
        $this->qrcodes = Config::get()->RAUMAUSHANG_SHOW_QRCODES;
    }

    public function store_action(): void
    {
        CSRFProtection::verifyUnsafeRequest();

        Config::get()->store('RAUMAUSHANG_AUTH', array_filter([
            'username' => trim(Request::get('username')),
            'password' => trim(Request::get('password')),
        ]));
        Config::get()->store(
            'RAUMAUSHANG_SHOW_FREE_BOOKINGS',
            Request::bool('free_bookings')
        );
        Config::get()->store(
            'RAUMAUSHANG_SHOW_QRCODES',
            Request::bool('qrcodes')
        );
        Config::get()->store(
            'RAUMAUSHANG_HELP_OVERLAY',
            trim(Request::get('help_content'))
        );

        PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));

        $this->redirect($this->indexURL());
    }
}
