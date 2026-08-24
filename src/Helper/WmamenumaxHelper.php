<?php

/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
* @author      Team Developer by WMA Web Maker Agency <wmaextension@gmail.com>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.wma.ovh
 * @version     1.0.11
 * @date        20/08/2026
 * @file        src/Helper/WmamenumaxHelper.php
 */

namespace Wma\Module\WmaMenumax\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Core logic for the WMA Megamenu module.
 *
 * @since  1.0.0
 */
class WmamenumaxHelper
{
    /**
     * Database driver.
     *
     * @var    DatabaseInterface|null
     * @since  1.0.0
     */
    private ?DatabaseInterface $db = null;

    /**
     * Article images cache [id => ['intro' => string, 'fulltext' => string]].
     *
     * @var    array
     * @since  1.0.0
     */
    private array $articleImages = [];

    /**
     * Category images cache [id => string].
     *
     * @var    array
     * @since  1.0.0
     */
    private array $categoryImages = [];

    /**
     * Thumbnails cache [source|size|hover => ['thumb' => url, 'hover' => url]].
     *
     * @var    array
     * @since  1.0.0
     */
    private array $thumbs = [];

    /**
     * Thumb helper instance.
     *
     * @var    ThumbHelper|null
     * @since  1.0.0
     */
    private ?ThumbHelper $thumbHelper = null;

    /**
     * Get the menu tree: top level items with their children (level 2).
     *
     * @param   Registry                   $params  The module parameters.
     * @param   CMSApplicationInterface    $app     The application.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function getMenuItems(Registry $params, CMSApplicationInterface $app): array
    {
        $menu  = $app->getMenu();
        $start = (int) $params->get('startLevel', 1);
        $end   = (int) $params->get('endLevel', 0);
        $items = $menu->getItems('menutype', $params->get('menutype')) ?: [];

        if (!$items) {
            return [];
        }

        $active    = $menu->getActive();
        $path      = $active ? $active->tree : [];
        $inputVars = $app->getInput()->getArray();
        $levels    = $app->getIdentity()->getAuthorisedViewLevels();

        $thumbSize = max(1, (int) $params->get('thumb_size', 100));
        $hoverSize = max(1, (int) $params->get('hover_size', 400));
        $fallback  = (string) $params->get('fallback_image', '');

        $flat = [];

        foreach ($items as $item) {
            if (!in_array($item->access, $levels)) {
                continue;
            }

            if (($start && $item->level < $start) || ($end && $item->level > $end)) {
                continue;
            }

            $itemParams = $item->getParams();

            if ((int) $itemParams->get('menu_show', 1) === 0) {
                continue;
            }

            $item->active = in_array($item->id, $path);

            if ($active && $item->id === $active->id) {
                $item->current = true;
            } elseif (!empty($item->query)) {
                $item->current = true;

                foreach ($item->query as $key => $value) {
                    if (!isset($inputVars[$key]) || $inputVars[$key] != $value) {
                        $item->current = false;
                        break;
                    }
                }
            } else {
                $item->current = false;
            }

            $item->flink      = $this->getFlink($item, $itemParams, $app);
            $item->menu_image = (string) $itemParams->get('menu_image', '');

            $flat[$item->id] = $item;
        }

        if (!$flat) {
            return [];
        }

        // Parent -> children ids map
        $childrenMap = [];

        foreach ($flat as $item) {
            $childrenMap[$item->parent_id][] = $item->id;
        }

        $top = [];

        // First pass: build top level nodes
        foreach ($flat as $item) {
            if ($item->level != $start) {
                continue;
            }

            $node = [
                'id'           => $item->id,
                'title'        => $item->title,
                'flink'        => $item->flink,
                'current'      => $item->current,
                'active'       => $item->active,
                'type'         => $item->type,
                'hasChildren'  => false,
                'children'     => [],
                'panelDefault' => $this->getPanelDefaultImage($item, $app, $fallback),
            ];

            $top[$item->id] = $node;
        }

        // Second pass: attach level 2 children
        foreach ($flat as $item) {
            if ($item->level != $start + 1 || !isset($top[$item->parent_id])) {
                continue;
            }

            $child = [
                'id'          => $item->id,
                'title'       => $item->title,
                'flink'       => $item->flink,
                'current'     => $item->current,
                'active'      => $item->active,
                'type'        => $item->type,
                'hasChildren' => !empty($childrenMap[$item->id]),
                'thumb'       => '',
                'hover'       => '',
            ];

            $source = $this->getChildSourceImage($item, $app, $fallback);

            if ($source !== '') {
                $thumbs = $this->getThumbs($source, $thumbSize, $hoverSize);
                $child['thumb'] = $thumbs['thumb'];
                $child['hover'] = $thumbs['hover'];
            }

            $top[$item->parent_id]['children'][] = $child;
            $top[$item->parent_id]['hasChildren'] = true;
        }

        return array_values($top);
    }

    /**
     * Distribute children of every top item into columns.
     *
     * @param   Registry  $params  The module parameters.
     * @param   array     $menu    The menu tree from getMenuItems().
     *
     * @return  array
     *
     * @since   1.0.0
     */
    public function distribute(Registry $params, array $menu): array
    {
        $distribution = $params->get('distribution', 'auto');
        $maxColumns   = max(1, (int) $params->get('columns', 3));

        $counts = [];

        if ($distribution === 'manual') {
            for ($i = 1; $i <= $maxColumns; $i++) {
                $counts[] = max(0, (int) $params->get('col' . $i . '_count', 0));
            }
        }

        foreach ($menu as &$topItem) {
            $children = $topItem['children'] ?? [];

            if (!$children) {
                $topItem['columns'] = [];
                continue;
            }

            $topItem['columns'] = $distribution === 'manual'
                ? $this->manualChunk($children, $counts)
                : $this->autoChunk($children, $maxColumns);
        }

        unset($topItem);

        return $menu;
    }

    /**
     * Balanced distribution of items across a max number of columns.
     *
     * @param   array  $items       The items to distribute.
     * @param   int    $maxColumns  Maximum number of columns.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    private function autoChunk(array $items, int $maxColumns): array
    {
        $total = count($items);
        $cols  = min($total, $maxColumns);
        $chunks = [];

        for ($i = 0; $i < $cols; $i++) {
            $chunks[] = [];
        }

        $base      = intdiv($total, $cols);
        $remainder = $total % $cols;
        $index     = 0;

        for ($i = 0; $i < $cols; $i++) {
            $size = $base + ($i < $remainder ? 1 : 0);

            for ($j = 0; $j < $size; $j++) {
                $chunks[$i][] = $items[$index++];
            }
        }

        return $chunks;
    }

    /**
     * Manual distribution using the per-column counts.
     *
     * @param   array  $items   The items to distribute.
     * @param   array  $counts  Items per column.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    private function manualChunk(array $items, array $counts): array
    {
        $chunks = [];
        $index  = 0;
        $total  = count($items);

        foreach ($counts as $count) {
            $chunk = [];

            for ($i = 0; $i < $count && $index < $total; $i++) {
                $chunk[] = $items[$index++];
            }

            $chunks[] = $chunk;
        }

        // Any leftover item goes to the last column
        while ($index < $total) {
            $chunks[count($chunks) - 1][] = $items[$index++];
        }

        return $chunks;
    }

    /**
     * Resolve the column titles from the coltitles subform.
     *
     * @param   Registry  $params         The module parameters.
     * @param   int       $columnsCount   The configured max columns.
     *
     * @return  array  [columnIndex(1-based) => ['title' => string, 'show' => bool]]
     *
     * @since   1.0.0
     */
    public function getColumnTitles(Registry $params, int $columnsCount): array
    {
        $map    = [];
        $groups = $params->get('coltitles', []);

        if (empty($groups)) {
            return $map;
        }

        foreach ((array) $groups as $group) {
            if (!is_object($group)) {
                continue;
            }

            $raw = isset($group->set) && is_object($group->set) ? $group->set : $group;

            $title = trim((string) ($raw->title ?? ''));

            if ($title === '') {
                continue;
            }

            $show  = (bool) ($raw->show ?? true);
            $start = max(1, (int) ($raw->start_col ?? 1));
            $span  = max(1, (int) ($raw->span ?? 1));

            for ($i = $start; $i < $start + $span && $i <= $columnsCount; $i++) {
                $map[$i] = ['title' => $title, 'show' => $show];
            }
        }

        return $map;
    }

    /**
     * Build the JSON-LD SiteNavigationElement payload.
     *
     * @param   array  $menu  The menu tree.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function buildJsonLd(array $menu): string
    {
        $names = [];
        $urls  = [];

        foreach ($menu as $topItem) {
            if ($topItem['flink'] !== '') {
                $names[] = $topItem['title'];
                $urls[]  = $topItem['flink'];
            }

            foreach ($topItem['children'] ?? [] as $child) {
                if ($child['flink'] !== '') {
                    $names[] = $child['title'];
                    $urls[]  = $child['flink'];
                }
            }
        }

        if (!$names) {
            return '';
        }

        $json = json_encode(
            [
                '@context' => 'https://schema.org',
                '@type'    => 'SiteNavigationElement',
                'name'     => $names,
                'url'      => $urls,
            ],
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        );

        return $json !== false ? $json : '';
    }

    /**
     * Resolve the link URL of a menu item (mirrors mod_menu).
     *
     * @param   object                    $item        The menu item.
     * @param   Registry                  $itemParams  The item params.
     * @param   CMSApplicationInterface   $app         The application.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function getFlink(object $item, Registry $itemParams, CMSApplicationInterface $app): string
    {
        switch ($item->type) {
            case 'separator':
            case 'heading':
                return '';

            case 'url':
                if (str_starts_with($item->link, 'index.php?') && !str_contains($item->link, 'Itemid=')) {
                    $flink = $item->link . '&Itemid=' . $item->id;
                } else {
                    $flink = $item->link;
                }
                break;

            case 'alias':
                $flink = 'index.php?Itemid=' . (int) $itemParams->get('aliasoptions');

                if (Multilanguage::isEnabled()) {
                    $target = $app->getMenu()->getItem((int) $itemParams->get('aliasoptions'));

                    if ($target && $target->language && $target->language !== '*') {
                        $flink .= '&lang=' . $target->language;
                    }
                }
                break;

            default:
                $flink = 'index.php?Itemid=' . $item->id;
                break;
        }

        if ((str_contains($flink, 'index.php?')) && strcasecmp(substr($flink, 0, 4), 'http')) {
            return Route::_($flink, true, $itemParams->get('secure'));
        }

        return Route::_($flink);
    }

    /**
     * Parse the query of an item link, resolving aliases to their target.
     *
     * @param   object                    $item  The menu item.
     * @param   CMSApplicationInterface   $app   The application.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    private function getItemQuery(object $item, CMSApplicationInterface $app): array
    {
        $link = $item->link;

        if ($item->type === 'alias') {
            $target = $app->getMenu()->getItem((int) $item->getParams()->get('aliasoptions'));

            if ($target) {
                $link = $target->link;
            }
        }

        if (!is_string($link) || strpos($link, '?') === false) {
            return [];
        }

        parse_str(parse_url($link, PHP_URL_QUERY) ?: '', $query);

        return is_array($query) ? $query : [];
    }

    /**
     * Source image for a level-2 child.
     *
     * Priority: article intro -> article fulltext -> category image -> per-item menu_image -> global fallback.
     *
     * @param   object                    $item     The child menu item.
     * @param   CMSApplicationInterface   $app      The application.
     * @param   string                    $fallback Global fallback image.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function getChildSourceImage(object $item, CMSApplicationInterface $app, string $fallback): string
    {
        $query  = $this->getItemQuery($item, $app);
        $source = '';

        if (!empty($query['view'])) {
            if ($query['view'] === 'article' && !empty($query['id'])) {
                $images = $this->getArticleImages((int) $query['id']);
                $source = $images['intro'] ?: $images['fulltext'];
            } elseif ($query['view'] === 'category' && !empty($query['id'])) {
                $source = $this->getCategoryImage((int) $query['id']);
            }
        }

        if ($source === '' && $item->menu_image !== '') {
            $source = $this->cleanImagePath((string) $item->menu_image);
        }

        if ($source === '') {
            $source = $this->cleanImagePath($fallback);
        }

        return $source;
    }

    /**
     * Default image for the right panel of a top item.
     *
     * Priority: category image -> per-item menu_image -> global fallback.
     *
     * @param   object                    $item     The top menu item.
     * @param   CMSApplicationInterface   $app      The application.
     * @param   string                    $fallback Global fallback image.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function getPanelDefaultImage(object $item, CMSApplicationInterface $app, string $fallback): string
    {
        $query  = $this->getItemQuery($item, $app);
        $source = '';

        if (!empty($query['view']) && $query['view'] === 'category' && !empty($query['id'])) {
            $source = $this->getCategoryImage((int) $query['id']);
        }

        if ($source === '' && $item->menu_image !== '') {
            $source = $this->cleanImagePath((string) $item->menu_image);
        }

        if ($source === '') {
            $source = $this->cleanImagePath($fallback);
        }

        return $this->rawUrl($source);
    }

    /**
     * Build the two thumbnails (small + hover) for a source image.
     *
     * @param   string  $source     Relative path or URL of the source image.
     * @param   int     $thumbSize  Small square size in px.
     * @param   int     $hoverSize  Large square size in px.
     *
     * @return  array  ['thumb' => string, 'hover' => string]
     *
     * @since   1.0.0
     */
    private function getThumbs(string $source, int $thumbSize, int $hoverSize): array
    {
        $key = $source . '|' . $thumbSize . '|' . $hoverSize;

        if (!isset($this->thumbs[$key])) {
            $this->thumbs[$key] = $this->getThumbHelper()->getThumbs($source, $thumbSize, $hoverSize);
        }

        return $this->thumbs[$key];
    }

    /**
     * Fetch the intro/fulltext images of an article.
     *
     * @param   int  $id  Article id.
     *
     * @return  array  ['intro' => string, 'fulltext' => string]
     *
     * @since   1.0.0
     */
    private function getArticleImages(int $id): array
    {
        if (!array_key_exists($id, $this->articleImages)) {
            $images = ['intro' => '', 'fulltext' => ''];

            try {
                $db = $this->getDb();

                $query = $db->getQuery(true)
                    ->select($db->quoteName('images'))
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . ' = ' . (int) $id);

                $db->setQuery($query);
                $json = $db->loadResult();

                if ($json) {
                    $data = json_decode((string) $json, true);

                    if (is_array($data)) {
                        $images = [
                            'intro'    => $this->cleanImagePath((string) ($data['image_intro'] ?? '')),
                            'fulltext' => $this->cleanImagePath((string) ($data['image_fulltext'] ?? '')),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Keep empty images on failure.
            }

            $this->articleImages[$id] = $images;
        }

        return $this->articleImages[$id];
    }

    /**
     * Fetch the image of a category.
     *
     * @param   int  $id  Category id.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function getCategoryImage(int $id): string
    {
        if (!array_key_exists($id, $this->categoryImages)) {
            $image = '';

            try {
                $db = $this->getDb();

                $query = $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('id') . ' = ' . (int) $id);

                $db->setQuery($query);
                $json = $db->loadResult();

                if ($json) {
                    $data = json_decode((string) $json, true);

                    if (is_array($data) && !empty($data['image'])) {
                        $image = $this->cleanImagePath((string) $data['image']);
                    }
                }
            } catch (\Throwable $e) {
                // Keep empty image on failure.
            }

            $this->categoryImages[$id] = $image;
        }

        return $this->categoryImages[$id];
    }

    /**
     * Get the database driver.
     *
     * @return  DatabaseInterface
     *
     * @since   1.0.0
     */
    private function getDb(): DatabaseInterface
    {
        if (!$this->db) {
            $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        }

        return $this->db;
    }

    /**
     * Get the thumb helper.
     *
     * @return  ThumbHelper
     *
     * @since   1.0.0
     */
    private function getThumbHelper(): ThumbHelper
    {
        if (!$this->thumbHelper) {
            $this->thumbHelper = new ThumbHelper();
        }

        return $this->thumbHelper;
    }

    /**
     * Strip Joomla media fragments (#joomlaImage:...) and normalize slashes.
     *
     * @param   string  $source  The raw image path.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function cleanImagePath(string $source): string
    {
        $source = trim($source);

        if ($source !== '') {
            $hash = strpos($source, '#');

            if ($hash !== false) {
                $source = substr($source, 0, $hash);
            }

            $source = str_replace('\\', '/', $source);
        }

        return $source;
    }

    /**
     * Convert a relative image path to an absolute URL.
     *
     * @param   string  $source  The image path.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    private function rawUrl(string $source): string
    {
        if ($source === '') {
            return '';
        }

        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://') || str_starts_with($source, '//')) {
            return $source;
        }

        return rtrim(Uri::root(true), '/') . '/' . ltrim($source, '/');
    }

    /**
     * Load the module language strings.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    private function loadLanguage(): void
    {
        $lang = Factory::getLanguage();

        $lang->load('mod_wmamenumax', JPATH_BASE)
            || $lang->load('mod_wmamenumax', JPATH_BASE . '/modules/mod_wmamenumax');
    }

    /**
     * Load the params of a mod_wmamenumax instance.
     *
     * @param   int  $id  Module id.
     *
     * @return  Registry
     *
     * @since   1.0.0
     */
    private function getModuleParams(int $id): Registry
    {
        try {
            $db = $this->getDb();

            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('id') . ' = ' . (int) $id)
                ->where($db->quoteName('module') . ' = ' . $db->quote('mod_wmamenumax'));

            $db->setQuery($query);
            $json = $db->loadResult();
        } catch (\Throwable $e) {
            $json = null;
        }

        return new Registry($json);
    }

    /**
     * Check whether the current user can run maintenance actions.
     *
     * @param   int|null  $moduleId  Optional module id for module-level edit checks.
     *
     * @return  bool
     *
     * @since   1.0.12
     */
    private function canRunMaintenance(?int $moduleId = null): bool
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user || $user->guest) {
            return false;
        }

        if ($user->authorise('core.admin')) {
            return true;
        }

        if ($user->authorise('core.manage', 'com_modules')) {
            return true;
        }

        if ($moduleId !== null && $moduleId > 0 && $user->authorise('core.edit', 'com_modules.module.' . $moduleId)) {
            return true;
        }

        return false;
    }

    /**
     * AJAX: delete all generated thumbnails.
     *
     * @return  JsonResponse
     *
     * @since   1.0.0
     */
    public function cleanCacheAjax(): JsonResponse
    {
        $this->loadLanguage();

        if (!Session::checkToken('get')) {
            return new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
        }

        if (!$this->canRunMaintenance()) {
            return new JsonResponse(null, Text::_('JERROR_ALERTNOAUTHOR'), true);
        }

        $deleted = $this->getThumbHelper()->clearCache();

        return new JsonResponse(
            ['deleted' => $deleted],
            Text::sprintf('MOD_WMAMENUMAX_CACHE_CLEANED', $deleted)
        );
    }

    /**
     * AJAX: clear the cache and regenerate all thumbnails for this module.
     *
     * @return  JsonResponse
     *
     * @since   1.0.0
     */
    public function rebuildAjax(): JsonResponse
    {
        $this->loadLanguage();

        if (!Session::checkToken('get')) {
            return new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
        }

        $app      = Factory::getApplication();
        $moduleId = (int) $app->getInput()->getInt('id', 0);

        if (!$this->canRunMaintenance($moduleId)) {
            return new JsonResponse(null, Text::_('JERROR_ALERTNOAUTHOR'), true);
        }

        $params   = $this->getModuleParams($moduleId);

        $this->getThumbHelper()->clearCache();

        $thumbSize = max(1, (int) $params->get('thumb_size', 100));
        $hoverSize = max(1, (int) $params->get('hover_size', 400));
        $fallback  = (string) $params->get('fallback_image', '');

        $menu = $this->getMenuItems($params, $app);

        $count = 0;

        foreach ($menu as $topItem) {
            foreach ($topItem['children'] ?? [] as $child) {
                if ($child['thumb'] !== '' || $child['hover'] !== '') {
                    $count++;
                }
            }
        }

        return new JsonResponse(
            ['items' => $count],
            Text::sprintf('MOD_WMAMENUMAX_REBUILD_DONE', $count)
        );
    }
}
