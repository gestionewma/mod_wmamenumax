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
 * @date        20/08/2026
 * @file        services/provider.php
 */

defined('_JEXEC') or die;

\JLoader::registerNamespace('Wma\\Module\\WmaMenumax\\Site', dirname(__DIR__) . '/src');

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module as ModuleServiceProvider;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new ModuleDispatcherFactory('\\Wma\\Module\\WmaMenumax')
        );
        $container->registerServiceProvider(
            new HelperFactory('\\Wma\\Module\\WmaMenumax\\Site\\Helper')
        );
        $container->registerServiceProvider(new ModuleServiceProvider());
    }
};
