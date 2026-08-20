<?php

/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
 * @author      Giuseppe Bosco <giusebos@libero.it>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.webmakeragency.it
 * @version     1.0.10
 * @date        11/08/2026
 * @file        src/Field/WmaInfoField.php
 */

namespace Wma\Module\WmaMenumax\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class WmaInfoField extends FormField
{
    protected $type = 'WmaInfo';

    public function getInput(): string
    {
        $version      = '1.0.10';
        $creationDate = '11/08/2026';
        $author       = 'WMA Web Maker Agency';
        $email        = 'giusebos@libero.it';
        $authorUrl    = 'https://www.webmakeragency.it';

        try {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('mod_wmamenumax'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('module'));
            $db->setQuery($query);
            $row = $db->loadObject();

            if ($row && !empty($row->manifest_cache)) {
                $manifest     = json_decode($row->manifest_cache);
                $version      = $manifest->version      ?? $version;
                $creationDate = $manifest->creationDate ?? $creationDate;
                $author       = $manifest->author       ?? $author;
                $email        = $manifest->authorEmail  ?? $email;
                $authorUrl    = $manifest->authorUrl    ?? $authorUrl;
            }
        } catch (\Exception $e) {
            // fallback to defaults
        }

        $year = date('Y');

        $version      = htmlspecialchars($version,      ENT_QUOTES, 'UTF-8');
        $creationDate = htmlspecialchars($creationDate, ENT_QUOTES, 'UTF-8');
        $author       = htmlspecialchars($author,       ENT_QUOTES, 'UTF-8');
        $email        = htmlspecialchars($email,        ENT_QUOTES, 'UTF-8');
        $authorUrl    = htmlspecialchars($authorUrl,    ENT_QUOTES, 'UTF-8');
        $moduleTitle  = htmlspecialchars(Text::_('MOD_WMAMENUMAX'), ENT_QUOTES, 'UTF-8');

        $logoPath = JPATH_ROOT . '/media/mod_wmamenumax/images/logo-wma.png';
        $logoUrl  = Uri::root() . 'media/mod_wmamenumax/images/logo-wma.png';
        $logoHtml = file_exists($logoPath)
            ? '<img src="' . $logoUrl . '" alt="WMA Web Maker Agency" style="max-width:200px;height:auto;" onerror="this.style.display=\'none\';">'
            : '<div style="width:64px;height:64px;display:flex;align-items:center;justify-content:center;background:#046aca;color:#fff;font-size:1.4rem;font-weight:800;border-radius:8px;letter-spacing:.05em;">WMA</div>';

        $emailRow = $email ? <<<HTML
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Email</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;"><a href="mailto:{$email}" style="color:#046aca;">{$email}</a></td>
                    </tr>
        HTML : '';

        $urlRow = $authorUrl ? <<<HTML
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Sito web</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;"><a href="{$authorUrl}" target="_blank" rel="noopener" style="color:#046aca;">{$authorUrl}</a></td>
                    </tr>
        HTML : '';

        return <<<HTML
        <div style="max-width:640px;font-family:inherit;">
            <div style="display:flex;align-items:center;gap:20px;padding:20px 24px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px 8px 0 0;border-bottom:none;">
                {$logoHtml}
                <div>
                    <h2 style="margin:0 0 4px;font-size:1.25rem;color:#212529;">{$moduleTitle}</h2>
                    <p style="margin:0;font-size:.9rem;color:#6c757d;">Versione {$version} &mdash; {$creationDate}</p>
                </div>
            </div>
            <table style="width:100%;border-collapse:collapse;border:1px solid #dee2e6;border-radius:0 0 8px 8px;overflow:hidden;font-size:.9rem;">
                <tbody>
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Modulo</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;"><code>mod_wmamenumax</code></td>
                    </tr>
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Versione</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;"><strong>{$version}</strong></td>
                    </tr>
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Data rilascio</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;">{$creationDate}</td>
                    </tr>
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Autore</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;">{$author}</td>
                    </tr>
                    {$emailRow}
                    {$urlRow}
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Licenza</th>
                        <td style="padding:10px 16px;background:#fff;border-bottom:1px solid #dee2e6;color:#212529;"><span style="display:inline-flex;align-items:center;gap:6px;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;border-radius:4px;padding:2px 8px;font-size:.8rem;font-weight:600;"><span class="icon-certificate" aria-hidden="true"></span> GNU GPL v2+</span></td>
                    </tr>
                    <tr>
                        <th style="width:35%;padding:10px 16px;background:#fff;border-right:1px solid #dee2e6;font-weight:600;color:#495057;text-align:left;">Copyright</th>
                        <td style="padding:10px 16px;background:#fff;color:#212529;">&copy; {$year} WMA Web Maker Agency. All rights reserved.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        HTML;
    }

    public function getLabel(): string
    {
        return '';
    }
}
