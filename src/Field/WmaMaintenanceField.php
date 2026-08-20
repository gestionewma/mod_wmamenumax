<?php

/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
 * @author      Giuseppe Bosco <giusebos@libero.it>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.webmakeragency.it
 * @version     1.0.0
 * @date        11/08/2026
 * @file        src/Field/WmaMaintenanceField.php
 */

namespace Wma\Module\WmaMenumax\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Maintenance buttons (clear cache / rebuild thumbnails) calling com_ajax.
 *
 * @since  1.0.0
 */
class WmaMaintenanceField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $type = 'WmaMaintenance';

    /**
     * Load the admin script and render the buttons.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function getInput(): string
    {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            $wa = $app->getDocument()->getWebAssetManager();
            $wa->getRegistry()->addExtensionRegistryFile('mod_wmamenumax');

            if ($wa->assetExists('script', 'mod_wmamenumax.admin')) {
                $wa->useScript('mod_wmamenumax.admin');
            } else {
                $app->getDocument()->addScript(Uri::root() . 'media/mod_wmamenumax/admin-wmamenumax.js');
            }
        }

        $moduleId = (int) $this->form->getValue('id');
        $root     = rtrim(Uri::root(), '/') . '/';
        $desc     = htmlspecialchars(Text::_((string) $this->description), ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div class="wma-mm-maintenance"
             data-url-root="{$root}"
             data-module-id="{$moduleId}">
            <p class="wma-mm-maintenance-desc">{$desc}</p>
            <div class="wma-mm-maintenance-actions">
                <button type="button" class="btn btn-info" data-wma-action="clean">
                    <span class="icon-trash" aria-hidden="true"></span>
                    {$this->translate('MOD_WMAMENUMAX_MAINTENANCE_CLEAN')}
                </button>
                <button type="button" class="btn btn-warning" data-wma-action="rebuild">
                    <span class="icon-refresh" aria-hidden="true"></span>
                    {$this->translate('MOD_WMAMENUMAX_MAINTENANCE_REBUILD')}
                </button>
            </div>
            <div class="wma-mm-maintenance-result" role="status"></div>
        </div>
        HTML;
    }

    /**
     * Translate a language key.
     *
     * @param   string  $key  The language key.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function translate(string $key): string
    {
        $text = Text::_($key);

        return $text === $key ? htmlspecialchars($key, ENT_QUOTES, 'UTF-8') : htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Hide the label.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function getLabel(): string
    {
        return '';
    }
}
