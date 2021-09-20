<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace ModuleTpl\Controller\DashboardPlugins;

use MelisCore\Controller\DashboardPlugins\MelisCoreDashboardTemplatingPlugin;
use Laminas\View\Model\ViewModel;

class ModuleTplPluginNamePlugin extends MelisCoreDashboardTemplatingPlugin
{
    public function __construct()
    {
        $this->pluginModule = 'moduletpl';
        parent::__construct();
    }
    
    public function pluginName()
    {        
        /**
         * Check user's accessibility(rights) for this plugin
         * @var \MelisCore\Service\MelisCoreDashboardPluginsRightsService $dashboardPluginsService
         */
        $dashboardPluginsService = $this->getServiceManager()->get('MelisCoreDashboardPluginsService');
        $path = explode('\\', __CLASS__);
        $className = array_pop($path);
        $isAccessible = $dashboardPluginsService->canAccess($className);
         
        $view = new ViewModel();
        $view->setTemplate('moduleTplViewFolderName/dashboard-plugins/plugin-name');               
        $view->toolIsAccessible = $isAccessible;
                
        return $view;
    }   
 
}