<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Raumaushang.class.php
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.1
 */
class Raumaushang extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();

        if (is_object($GLOBALS['user']) && $GLOBALS['user']->perms === 'root') {
            $navigation = new Navigation(_('Raumaushang'), $this->url_for('schedules/index'));
            $navigation->setImage('icons/white/timetable.svg');
            $navigation->setActiveImage('icons/black/timetable.svg');
            Navigation::addItem('/resources/raumaushang', $navigation);
        }
    }

    public function perform($unconsumed_path)
    {
        if (Navigation::hasItem('/resources/raumaushang')) {
            Navigation::activateItem('/resources/raumaushang');
        }

        $this->addLESS('assets/style.less');

        URLHelper::removeLinkParam('cid');

        parent::perform($unconsumed_path);
    }

    protected function url_for($to, $params = array())
    {
        return PluginEngine::getURL($this, $params, $to);
    }

    public function addJS($asset)
    {
        PageLayout::addScript($this->getPluginURL() . '/' . ltrim($asset, '/'));
    }

    // This is still ugly, since it copies almost all of the core functionality
    public function addLESS($filename)
    {
        if (substr($filename, -5) !== '.less') {
            $url = $this->getPluginURL() . '/' . $filename;
            PageLayout::addStylesheet($url);
            return;
        }

        // Create absolute path to less file
        $less_file = $GLOBALS['ABSOLUTE_PATH_STUDIP']
                   . $this->getPluginPath() . '/'
                   . $filename;

        // Fail if file does not exist
        if (!file_exists($less_file)) {
            throw new Exception('Could not locate LESS file "' . $filename . '"');
        }

        // Get plugin version from metadata
        $metadata = $this->getMetadata();
        $plugin_version = $metadata['version'];

        // Get plugin id (or parent plugin id if any)
        $plugin_id = $this->plugin_info['depends'] ?: $this->getPluginId();

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createCSSFile($less_file, array(
            'plugin_id'      => $this->plugin_info['depends'] ?: $this->getPluginId(),
            'plugin_version' => $metadata['version'],
        ));

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $variables['plugin-path'] = $this->getPluginURL();
            $variables['plugin-url']  = $this->getPluginURL();

            $less = file_get_contents($less_file);

            if (strpos($less, '@import') !== false) {
                // Import things here!!
                $path  = dirname($less_file);
                $lines = array_map('trim', explode("\n", $less));
                $less = '';
                foreach ($lines as $line) {
                    if (preg_match('/^@import "(.*)";/', $line, $match)) {
                        $include_file = $path . '/' . $match[1];
                        $line = trim(file_get_contents($include_file));
                    }
                    $less .= $line . "\n";
                }
            }

            $css  = Assets\Compiler::compileLESS($less, $variables);
            $asset->setContent($css);
        }

        // Include asset in page by reference or directly
        $download_uri = $asset->getDownloadLink();
        if ($download_uri === false) {
            PageLayout::addStyle($asset->getContent(), $link_attr);
        } else {
            $link_attr['rel']  = 'stylesheet';
            $link_attr['href'] = $download_uri;
            $link_attr['type'] = 'text/css';
            PageLayout::addHeadElement('link', $link_attr);
        }

        return $asset;
    }
}
