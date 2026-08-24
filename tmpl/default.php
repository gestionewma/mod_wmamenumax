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
 * @file        tmpl/default.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$moduleId = (int) ($module->id ?? 0);
$navId    = 'wma-mm-' . $moduleId;

$titleTag = in_array($titleTag ?? 'h3', ['h2', 'h3', 'p', 'div'], true) ? $titleTag : 'h3';
$panelPct = max(1, min(100, (int) $panelPct));
$suffix   = trim((string) $params->get('moduleclass_sfx', ''));

$colors = $colors ?? [
    'title'   => '#046aca',
    'border'  => '#046aca',
    'outline' => '#e9ecef',
    'megaBg'  => '#ffffff',
    'topBg'   => '#ffffff',
    'top'     => '#212529',
    'topHover' => '#046aca',
    'topCurrent' => '#046aca',
    'mobileBg' => '#ffffff',
    'child'   => '#212529',
    'childHoverBg' => '#e9ecef',
];
$opacities = $opacities ?? [
    'title'   => 100,
    'border'  => 100,
    'outline' => 100,
    'megaBg'  => 100,
    'topBg'   => 100,
    'top'     => 100,
    'topHover' => 100,
    'topCurrent' => 100,
    'mobileBg' => 100,
    'child'   => 100,
    'childHoverBg' => 100,
];
$colorsMobile = $colorsMobile ?? [
    'title'   => '#046aca',
    'megaBg'  => '#ffffff',
    'topBg'   => '#ffffff',
    'top'     => '#212529',
    'topHover' => '#046aca',
    'topCurrent' => '#046aca',
    'mobileBg' => '#ffffff',
    'child'   => '#212529',
    'childHoverBg' => '#e9ecef',
];
$opacitiesMobile = $opacitiesMobile ?? [
    'title'   => 100,
    'megaBg'  => 100,
    'topBg'   => 100,
    'top'     => 100,
    'topHover' => 100,
    'topCurrent' => 100,
    'mobileBg' => 100,
    'child'   => 100,
    'childHoverBg' => 100,
];

$hexToRgba = static function (string $hex, int $opacity): string {
    $hex = ltrim(trim($hex), '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return 'transparent';
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $a = max(0, min(100, $opacity)) / 100;

    return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $a . ')';
};

$colorVar = static function (string $color, int $opacity) use ($hexToRgba): string {
    return $opacity >= 100 ? $color : $hexToRgba($color, $opacity);
};

$inlineCss = '--wma-mm-coltitle:' . $colorVar($colors['title'], $opacities['title'])
    . ';--wma-mm-mega-border:' . $colorVar($colors['border'], $opacities['border'])
    . ';--wma-mm-mega-outline:' . $colorVar($colors['outline'], $opacities['outline'])
    . ';--wma-mm-mega-bg:' . $colorVar($colors['megaBg'], $opacities['megaBg'])
    . ';--wma-mm-item-bg:' . $colorVar($colors['topBg'], $opacities['topBg'])
    . ';--wma-mm-link:' . $colorVar($colors['top'], $opacities['top'])
    . ';--wma-mm-link-hover:' . $colorVar($colors['topHover'], $opacities['topHover'])
    . ';--wma-mm-link-current:' . $colorVar($colors['topCurrent'], $opacities['topCurrent'])
    . ';--wma-mm-mobile-bg:' . $colorVar($colorsMobile['mobileBg'], $opacitiesMobile['mobileBg'])
    . ';--wma-mm-child-color:' . $colorVar($colors['child'], $opacities['child'])
    . ';--wma-mm-child-hover-bg:' . $colorVar($colors['childHoverBg'], $opacities['childHoverBg'])
    . ';--wma-mm-mobile-coltitle:' . $colorVar($colorsMobile['title'], $opacitiesMobile['title'])
    . ';--wma-mm-mobile-mega-bg:' . $colorVar($colorsMobile['megaBg'], $opacitiesMobile['megaBg'])
    . ';--wma-mm-mobile-item-bg:' . $colorVar($colorsMobile['topBg'], $opacitiesMobile['topBg'])
    . ';--wma-mm-mobile-link:' . $colorVar($colorsMobile['top'], $opacitiesMobile['top'])
    . ';--wma-mm-mobile-link-hover:' . $colorVar($colorsMobile['topHover'], $opacitiesMobile['topHover'])
    . ';--wma-mm-mobile-link-current:' . $colorVar($colorsMobile['topCurrent'], $opacitiesMobile['topCurrent'])
    . ';--wma-mm-mobile-child-color:' . $colorVar($colorsMobile['child'], $opacitiesMobile['child'])
    . ';--wma-mm-mobile-child-hover-bg:' . $colorVar($colorsMobile['childHoverBg'], $opacitiesMobile['childHoverBg']) . ';';

if (empty($menu)) {
    return;
}

$suffixClass = $suffix !== '' ? ' ' . htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') : '';
?>

<nav class="wma-mm<?php echo $suffixClass; ?>"
     id="<?php echo $navId; ?>"
     style="<?php echo htmlspecialchars($inlineCss, ENT_QUOTES, 'UTF-8'); ?>"
     data-panel-pct="<?php echo $panelPct; ?>"
     aria-label="<?php echo Text::_('MOD_WMAMENUMAX_MENU_LABEL'); ?>">

    <?php if ($seoJsonLd && $jsonLd !== '') : ?>
        <script type="application/ld+json"><?php echo $jsonLd; ?></script>
    <?php endif; ?>

    <button class="wma-mm-toggle" type="button" aria-expanded="false" aria-controls="<?php echo $navId; ?>-body" aria-label="<?php echo Text::_('MOD_WMAMENUMAX_MENU_LABEL'); ?>">
        <span class="wma-mm-toggle-icon" aria-hidden="true"></span>
    </button>

    <ul class="wma-mm-bar" id="<?php echo $navId; ?>-body">
        <?php foreach ($menu as $topItem) : ?>
            <li class="wma-mm-item<?php echo $topItem['hasChildren'] ? ' has-mega' : ''; ?><?php echo $topItem['current'] ? ' current' : ''; ?><?php echo $topItem['active'] ? ' active' : ''; ?>">
                <?php if ($topItem['flink'] === '') : ?>
                    <span class="wma-mm-link is-title"><?php echo htmlspecialchars($topItem['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php else : ?>
                    <a class="wma-mm-link" href="<?php echo htmlspecialchars($topItem['flink'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($topItem['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>

                <?php if ($topItem['hasChildren'] && !empty($topItem['columns'])) : ?>
                    <div class="wma-mm-mega">
                        <div class="wma-mm-cols" data-cols="<?php echo count($topItem['columns']); ?>">
                            <?php foreach ($topItem['columns'] as $colIndex => $colItems) : ?>
                                <div class="wma-mm-col">
                                    <?php
                                    $colNum = $colIndex + 1;
                                    $title  = $colTitles[$colNum]['title'] ?? '';
                                    $show   = $colTitles[$colNum]['show'] ?? true;
                                    ?>

                                    <?php if ($title !== '' && $show) : ?>
                                        <<?php echo $titleTag; ?> class="wma-mm-coltitle"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></<?php echo $titleTag; ?>>
                                    <?php endif; ?>

                                    <ul class="wma-mm-list">
                                        <?php foreach ($colItems as $child) : ?>
                                            <li class="wma-mm-list-item<?php echo $child['current'] ? ' current' : ''; ?>">
                                                <?php if ($child['flink'] === '') : ?>
                                                    <span class="wma-mm-child is-title">
                                                        <span class="wma-mm-child-title"><?php echo htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </span>
                                                <?php else : ?>
                                                    <a class="wma-mm-child"
                                                       href="<?php echo htmlspecialchars($child['flink'], ENT_QUOTES, 'UTF-8'); ?>"
                                                       <?php if ($child['thumb'] !== '') : ?>
                                                       data-thumb="<?php echo htmlspecialchars($child['thumb'], ENT_QUOTES, 'UTF-8'); ?>"
                                                       <?php endif; ?>
                                                       <?php if ($child['hover'] !== '') : ?>
                                                       data-hover="<?php echo htmlspecialchars($child['hover'], ENT_QUOTES, 'UTF-8'); ?>"
                                                       <?php endif; ?>>
                                                        <?php if ($child['thumb'] !== '') : ?>
                                                            <img class="wma-mm-thumb" src="<?php echo htmlspecialchars($child['thumb'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy">
                                                        <?php endif; ?>
                                                        <span class="wma-mm-child-title"><?php echo htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php if ($child['hasChildren']) : ?>
                                                            <span class="wma-mm-chevron" aria-hidden="true">›</span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="wma-mm-panel">
                            <?php if ($topItem['panelDefault'] !== '') : ?>
                                <img class="wma-mm-panel-img"
                                     src="<?php echo htmlspecialchars($topItem['panelDefault'], ENT_QUOTES, 'UTF-8'); ?>"
                                     data-default="<?php echo htmlspecialchars($topItem['panelDefault'], ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
