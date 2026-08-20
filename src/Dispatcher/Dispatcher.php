<?php

/**
 * @package     Wma.Module.WmaMenumax
 * @subpackage  mod_wmamenumax
 *
 * @author      Team Developer by WMA Web Maker Agency <giusebos@libero.it>
 * @copyright   (C) 2026 WMA Web Maker Agency. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.wma.ovh
 * @version     1.0.11
 * @date        11/08/2026
 * @file        src/Dispatcher/Dispatcher.php
 */

namespace Wma\Module\WmaMenumax\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Wma\Module\WmaMenumax\Site\Helper\WmamenumaxHelper;

class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Add the module assets and prepare the layout data.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $wa = $this->app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('mod_wmamenumax');
        $wa->useStyle('mod_wmamenumax.style');
        $wa->useScript('mod_wmamenumax.script');

        $helper = new WmamenumaxHelper();

        $menu          = $helper->getMenuItems($data['params'], $this->app);
        $data['menu']  = $helper->distribute($data['params'], $menu);

        $data['colTitles'] = $helper->getColumnTitles(
            $data['params'],
            max(1, (int) $data['params']->get('columns', 3))
        );

        $data['jsonLd']   = $helper->buildJsonLd($data['menu']);
        $data['panelPct'] = (int) $data['params']->get('panel_pct', 100);
        $data['titleTag'] = $data['params']->get('title_tag', 'h3');
        $data['seoJsonLd'] = (bool) $data['params']->get('seo_jsonld', true);

        $data['colors'] = [
            'title'   => (string) $data['params']->get('title_color', '#046aca'),
            'border'  => (string) $data['params']->get('mega_border_color', '#046aca'),
            'outline' => (string) $data['params']->get('mega_outline_color', '#e9ecef'),
            'megaBg'  => (string) $data['params']->get('mega_bg_color', '#ffffff'),
            'topBg'   => (string) $data['params']->get('top_bg_color', '#ffffff'),
            'top'     => (string) $data['params']->get('top_color', '#212529'),
            'topHover' => (string) $data['params']->get('top_hover_color', '#046aca'),
            'topCurrent' => (string) $data['params']->get('top_current_color', '#046aca'),
            'mobileBg' => (string) $data['params']->get('mobile_bg_color', '#ffffff'),
            'child'   => (string) $data['params']->get('child_color', '#212529'),
            'childHoverBg' => (string) $data['params']->get('child_hover_bg_color', '#e9ecef'),
        ];

        $data['opacities'] = [
            'title'   => (int) $data['params']->get('title_opacity', 100),
            'border'  => (int) $data['params']->get('mega_border_opacity', 100),
            'outline' => (int) $data['params']->get('mega_outline_opacity', 100),
            'megaBg'  => (int) $data['params']->get('mega_bg_opacity', 100),
            'topBg'   => (int) $data['params']->get('top_bg_opacity', 100),
            'top'     => (int) $data['params']->get('top_color_opacity', 100),
            'topHover' => (int) $data['params']->get('top_hover_color_opacity', 100),
            'topCurrent' => (int) $data['params']->get('top_current_color_opacity', 100),
            'mobileBg' => (int) $data['params']->get('mobile_bg_opacity', 100),
            'child'   => (int) $data['params']->get('child_color_opacity', 100),
            'childHoverBg' => (int) $data['params']->get('child_hover_bg_opacity', 100),
        ];

        $data['colorsMobile'] = [
            'title'   => (string) $data['params']->get('mobile_title_color', '#046aca'),
            'megaBg'  => (string) $data['params']->get('mobile_mega_bg_color', '#ffffff'),
            'topBg'   => (string) $data['params']->get('mobile_top_bg_color', '#ffffff'),
            'top'     => (string) $data['params']->get('mobile_top_color', '#212529'),
            'topHover' => (string) $data['params']->get('mobile_top_hover_color', '#046aca'),
            'topCurrent' => (string) $data['params']->get('mobile_top_current_color', '#046aca'),
            'mobileBg' => (string) $data['params']->get('mobile_bg_color', '#ffffff'),
            'child'   => (string) $data['params']->get('mobile_child_color', '#212529'),
            'childHoverBg' => (string) $data['params']->get('mobile_child_hover_bg_color', '#e9ecef'),
        ];

        $data['opacitiesMobile'] = [
            'title'   => (int) $data['params']->get('mobile_title_opacity', 100),
            'megaBg'  => (int) $data['params']->get('mobile_mega_bg_opacity', 100),
            'topBg'   => (int) $data['params']->get('mobile_top_bg_opacity', 100),
            'top'     => (int) $data['params']->get('mobile_top_color_opacity', 100),
            'topHover' => (int) $data['params']->get('mobile_top_hover_color_opacity', 100),
            'topCurrent' => (int) $data['params']->get('mobile_top_current_color_opacity', 100),
            'mobileBg' => (int) $data['params']->get('mobile_bg_opacity', 100),
            'child'   => (int) $data['params']->get('mobile_child_color_opacity', 100),
            'childHoverBg' => (int) $data['params']->get('mobile_child_hover_bg_opacity', 100),
        ];

        return $data;
    }
}
