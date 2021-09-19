<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDashboardPluginCreator\Form\Factory;

use Laminas\ServiceManager\ServiceManager;
use MelisCore\Form\Factory\MelisSelectFactory;

/**
 * This class sets the value options for the Existing Module dropdown
 *
 */
class MelisDashboardPluginCreatorModuleSelectFactory extends MelisSelectFactory
{
    protected function loadValueOptions(ServiceManager $serviceManager)
    {
        $moduleService = $serviceManager->get('ModulesService');
        $moduleLoadFile = $moduleService->getUserModules();

        $valueoptions = [];
        if($moduleLoadFile){
            foreach($moduleLoadFile as $key => $val) {
                //exclude the MelisModuleConfig and MelisSites
                if($val != 'MelisModuleConfig' && $val != 'MelisSites'){
                    $valueoptions[$val] = $val;
                }            
            }
        }
        
        return $valueoptions;
    }
}