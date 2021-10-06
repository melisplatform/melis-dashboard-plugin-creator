<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDashboardPluginCreator\Service;

use Laminas\Session\Container;
use Laminas\Config\Factory;
use MelisCore\Service\MelisGeneralService;

/**
 * 
 * This service executes the testing of page links
 *
 */
class MelisDashboardPluginCreatorService extends MelisGeneralService
{  
    protected $dashboardPluginCreatorTplDir;   
    protected $dpcSteps;
    protected $pluginName;
    protected $moduleName;
    const EXISTING_MODE = "existing_module";    

    public function __construct()
    {    
        $this->dashboardPluginCreatorTplDir = __DIR__ .'/../../template';

        //session container     
        $container = new Container('dashboardplugincreator');     
        $this->dpcSteps = $container['melis-dashboardplugincreator'];  
    }
     
    /**
     * This will generate the dashboard plugin based on the parameters stored in the current session   
     * @return boolean
     */
    public function generateDashboardPlugin(){           
        //set module name
        if($this->dpcSteps['step_1']['dpc_plugin_destination'] == self::EXISTING_MODE){
            $this->moduleName = $this->dpcSteps['step_1']['dpc_existing_module_name'];
        }else{
            $this->moduleName = $this->generateModuleNameCase($this->dpcSteps['step_1']['dpc_new_module_name']);
            //unset the tools tree section of the newly created module
            $this->emptyConfigToolsTreeSection($_SERVER['DOCUMENT_ROOT'].'/../module/'.$this->moduleName);
        }
        //set plugin name
        $this->pluginName = $this->generateModuleNameCase($this->dpcSteps['step_1']['dpc_plugin_name']);

        //set the directory of the new or existing module
        $moduleDir = $_SERVER['DOCUMENT_ROOT'].'/../module/'.$this->moduleName;
        
        //perform the steps in generating the dashboard plugin
        $isSuccessful = $this->performGeneration($moduleDir);   
          
        if($isSuccessful){    
            //remove temp thumbnail directory of the current session    
            $tempPath = pathinfo($this->getTempThumbnail(), PATHINFO_DIRNAME);            
            $this->removeDir($tempPath);

            return true;
        }else{
            //this will rollback the steps performed when generating the dashboard plugin
            $this->rollbackPluginGeneration($moduleDir);
            return false;            
        }    
    }

    /**
     * This will perform the generation of plugin
     * @param string $moduleDir
     * @return boolean
     */
    protected function performGeneration($moduleDir){
        //create a dashboardplugin config
        $isSuccessful = $this->generateDashboardPluginConfig($moduleDir);
        if(!$isSuccessful){
            return false;
        }

        //update module.config.php
        $isSuccessful = $this->updateModuleConfig($moduleDir);
        if(!$isSuccessful){
            return false;
        }     

        //set the translations
        $isSuccessful = $this->setTranslations($moduleDir);  
        if(!$isSuccessful){
            return false;
        }   

        //generate assets
        $isSuccessful = $this->generateDashboardPluginAssets($moduleDir);
        if(!$isSuccessful){
            return false;
        }

        //create the dashboard plugin controller
        $isSuccessful = $this->generateDashboardPluginController($moduleDir);
        if(!$isSuccessful){
            return false;
        }

        //generate view file
        $isSuccessful = $this->generateDashboardPluginView($moduleDir);
        if(!$isSuccessful){
            return false;
        }

        //update Module.php to add the newly created dashboard plugin config
        $isSuccessful = $this->updateModuleFile($moduleDir);
        if(!$isSuccessful){
            return false;
        }
   
        return true;
    }

    /**
     * This will rollback the steps done in generating the dashboard plugin
     * @param string $moduleDir
     * @return boolean
     */
    protected function rollbackPluginGeneration($moduleDir){

        //remove created files if using existing mode
        if($this->dpcSteps['step_1']['dpc_plugin_destination'] == self::EXISTING_MODE){              
            
            /*delete dashboard plugin config*/
            if(file_exists($moduleDir.'/config/dashboard-plugins/'.$this->pluginName.'Plugin.config.php')){
                unlink($moduleDir.'/config/dashboard-plugins/'.$this->pluginName.'Plugin.config.php');
            }
            
            //update module.config.php to remove the dashboard plugin in template_path and controller_plugin keys 
            $this->updateModuleConfig($moduleDir, false);    

            //remove translation keys
            $this->setTranslations($moduleDir, false);
          
            /*******start remove assets*****/
            if(file_exists($moduleDir.'/public/dashboard-plugin/css/'.$this->pluginName.'.css')){
                unlink($moduleDir.'/public/dashboard-plugin/css/'.$this->pluginName.'.css');
            }
            
            if(file_exists($moduleDir.'/public/dashboard-plugin/js/'.$this->pluginName.'.js')){
                unlink($moduleDir.'/public/dashboard-plugin/js/'.$this->pluginName.'.js');
            }
            
            $fileName = pathinfo($this->dpcSteps['step_2']['plugin_thumbnail'], PATHINFO_FILENAME).'.'.pathinfo($this->dpcSteps['step_2']['plugin_thumbnail'], PATHINFO_EXTENSION);
            if(file_exists($moduleDir.'/public/dashboard-plugin/images/'.$fileName)){
                unlink($moduleDir.'/public/dashboard-plugin/images/'.$fileName);
            }
            /*******end remove assets*****/                                        

            //delete controller
            if(file_exists($moduleDir.'/src/'.$this->moduleName.'/Controller/DashboardPlugins/'.$this->moduleName.$this->pluginName.'Plugin.php')){
                unlink($moduleDir.'/src/'.$this->moduleName.'/Controller/DashboardPlugins/'.$this->moduleName.$this->pluginName.'Plugin.php');
            }
            
            //delete view
            if(file_exists($moduleDir.'/view/'.$this->convertToViewName($this->moduleName).'/dashboard-plugins/'.$this->convertToViewName($this->pluginName).'.phtml')){
                unlink($moduleDir.'/view/'.$this->convertToViewName($this->moduleName).'/dashboard-plugins/'.$this->convertToViewName($this->pluginName).'.phtml');
            }
           
            //update module.php to remove the dashboard config 
            $this->updateModuleFile($moduleDir, false);  

        }else{        
            //remove newly created module if using new module              
            $this->removeDir($moduleDir);
        }
    }


    /**
     * This method generates the dashboard plugin config
     * @param string $moduleDir
     * @return boolean
     */
    protected function generateDashboardPluginConfig($moduleDir)
    {
        $targetDir = $moduleDir.'/config/dashboard-plugins';

        // get the config template
        $dashboardPluginConfigContent = $this->getTemplateContent('/DashboardPlugin.config.php');

        //set the plugin icon
        $dashboardPluginConfigContent = str_replace('PluginIcon',$this->dpcSteps['step_3']['icon_form']['dpc_plugin_icon'], $dashboardPluginConfigContent);

        //set the plugin thumbnail   
        $pluginThumbnail = $this->dpcSteps['step_1']['dpc_plugin_name'].'_pluginThumbnail.'.pathinfo($this->dpcSteps['step_2']['plugin_thumbnail'], PATHINFO_EXTENSION); 
        $dashboardPluginConfigContent = str_replace('PluginThumbnail', $pluginThumbnail, $dashboardPluginConfigContent);

        $res = $this->generateFile($this->pluginName.'Plugin.config.php', $targetDir, $dashboardPluginConfigContent);
        return $res;
    }


    /**
     * This method generates the dashboard plugin controller
     * @param string $moduleDir
     * @return boolean
     */
    protected function generateDashboardPluginController($moduleDir)
    {
        $targetDir = $moduleDir.'/src/'.$this->moduleName.'/Controller/DashboardPlugins';
        $tabIconArr = array();
        $tabIcons = '';

        //get the controller template
        $dashboardPluginControllerContent = $this->getTemplateContent('/DashboardPluginController.php');   

        //update the view folder name  
        $dashboardPluginControllerContent = str_replace('moduleTplViewFolderName', $this->convertToViewName($this->moduleName), $dashboardPluginControllerContent);

        //update the view file name 
        $dashboardPluginControllerContent = str_replace('plugin-name', $this->convertToViewName($this->pluginName), $dashboardPluginControllerContent);
        
        $res = $this->generateFile($this->moduleName.$this->pluginName.'Plugin.php', $targetDir, $dashboardPluginControllerContent);
        return $res;
    }

    /**
     * This method generates the dashboard plugin view file
     * @param string $moduleDir
     * @return boolean
     */
    protected function generateDashboardPluginView($moduleDir)
    {
        $targetDir = $moduleDir.'/view/'.$this->convertToViewName($this->moduleName).'/dashboard-plugins';
        $tabIconArr = array();
        
        //check if tab count is set
        $tabCount = !empty($this->dpcSteps['step_1']['dpc_tab_count'])?$this->dpcSteps['step_1']['dpc_tab_count']:0;
        if($tabCount == 0){
            //get the plugin view template for single tab
            $dashboardPluginViewContent = $this->getTemplateContent('/dashboard-view-single-tab.phtml');
        }else{
            
            //set the containter for the unique id for each tab     
            $pluginConfigPluginId = '<?php echo $this->pluginConfig[\'plugin_id\']?>';            
            $tabHeader = "";          
            $tabContent = "";

            //set the tab header and content dynamically depending upon the number of tabs set
            for($i=1;$i<=$tabCount;$i++){
                $pluginTabId = 'tab-'.$i.'-'.str_replace(" ","-",$this->dpcSteps['step_3']['icon_form']['dpc_plugin_tab_icon_'.$i]).'-'.$pluginConfigPluginId;

                $tabHeader .= '<li class="nav-item '.($i==1?"active":"").'">'."\r\n\t\t\t\t\t".'<a class="glyphicons '.$this->dpcSteps['step_3']['icon_form']['dpc_plugin_tab_icon_'.$i].' nav-link'.($i==1?" active":"").'" href="#'.$pluginTabId.'" data-toggle="tab"><i></i></a>'."\r\n\t\t\t\t"."</li>\r\n\t\t\t\t";

                $tabContent .= '<div class="tab-pane'.($i==1?" active":"").'" id="'.$pluginTabId.'">'."\r\n\t\t\t\t\t\t".'<!-- Plugin Content for the tab here -->'."\r\n\t\t\t\t\t".'</div>'."\r\n\t\t\t\t\t";
            }

            //get the plugin view template for multiple tabs
            $dashboardPluginViewContent = $this->getTemplateContent('/dashboard-view-multiple-tab.phtml');
            $dashboardPluginViewContent = str_replace('#TABHEADER', $tabHeader, $dashboardPluginViewContent);
            $dashboardPluginViewContent = str_replace('#TABCONTENT', $tabContent, $dashboardPluginViewContent);
        }          

        //generate view file
        $res = $this->generateFile($this->convertToViewName($this->pluginName).'.phtml', $targetDir, $dashboardPluginViewContent);

        return $res;
    }


    /**
     * This method generates the css and js files and copy the uploaded plugin thumbnail to the module's asset directory
     * @param string $moduleDir
     * @return boolean
     */
    protected function generateDashboardPluginAssets($moduleDir)
    {       
        $targetDir = $moduleDir.'/public/dashboard-plugin';
        $assetArr = array('css' => $targetDir.'/css', 'js' => $targetDir.'/js','images' => $targetDir.'/images');
        $res = false;

        foreach($assetArr as $asset => $dir){            
            if($asset == 'css'){
                $res = $this->generateFile($this->pluginName.'.'.$asset, $dir, '');
            }else if($asset == 'js'){                    
                $res = $this->generateFile($this->pluginName.'.'.$asset, $dir, $this->getTemplateContent('/blank_plugin.js'));
            }else if($asset == 'images'){
                
                //check if target directory exists
                if(!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }                        

                //copy saved thumbnail to the plugin image directory of the module          
                $tempThumbnail = $this->getTempThumbnail();

                //set the filename
                $fileName = $this->dpcSteps['step_1']['dpc_plugin_name'].'_pluginThumbnail.'.pathinfo($this->dpcSteps['step_2']['plugin_thumbnail'], PATHINFO_EXTENSION); 

                if(copy($tempThumbnail, $dir.'/'.$fileName)){                   
                    $res = true;
                }                    
            }             
        }

        return $res;
    }

    /**
     * This method retrieves the temp thumbnail path and filename     
     * @return string
     */
    public function getTempThumbnail(){
        //session container     
        $container = new Container('dashboardplugincreator');     
        $sessionID = $container->getManager()->getId(); 

        //get the dashboard plugin creator's module directory 
        $melisModule = $this->getServiceManager()->get('MelisAssetManagerModulesService');  
        $names = explode("\\", __NAMESPACE__);                       
        $moduleToolName = $names[0];
        $thumbnailTempPath = $melisModule->getModulePath($moduleToolName,true).'/public/temp-thumbnail/';        
        
        //append the current session ID to the thumbnail path
        $thumbnailTempPath = $thumbnailTempPath.$sessionID.'/';
        $baseName = pathinfo($this->dpcSteps['step_2']['plugin_thumbnail'], PATHINFO_BASENAME);      
        $pluginThumbnail = $thumbnailTempPath.$baseName;

        return $pluginThumbnail;
    }

    /**
     * This method sets or unsets the translations for each language available in the platform
     * @param string $moduleDir
     * @param boolean $appencConfig    
     * @return string
     */
    protected function setTranslations($moduleDir, $appendConfig = true){
        $languageDir = $moduleDir.'/language/';
        
        //retrieve all available languages
        $coreLang = $this->getServiceManager()->get('MelisCoreTableLang');
        $languages = $coreLang->fetchAll()->toArray();
        $errorCount = 0;
        $res = false;

        foreach($languages as $lang){
            $langFile = $languageDir.$lang['lang_locale'].'.interface.php';
            $langFile = file_exists($langFile)?$langFile:$languageDir.$lang['lang_locale'].'php';

            if($langFile){
                 //get the existing translation of the language
                $translationArr = include $langFile;
                
                if($appendConfig){
                    //set the menu title
                    $translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).'_menu title'] = !empty($this->dpcSteps['step_2'][$lang['lang_locale']]['dpc_plugin_title'])
                                                        ?$this->removeExtraSpace($this->dpcSteps['step_2'][$lang['lang_locale']]['dpc_plugin_title']):"";
                    
                    //set the menu description
                    $translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).'_menu description'] = !empty($this->dpcSteps['step_2'][$lang['lang_locale']]['dpc_plugin_desc'])                                ?$this->removeExtraSpace($this->dpcSteps['step_2'][$lang['lang_locale']]['dpc_plugin_desc']):"";
                    
                    //set the dashboard title
                    $translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).' title'] = !empty($this->dpcSteps['step_3'][$lang['lang_locale']]['dpc_plugin_title'])
                                                        ?$this->removeExtraSpace($this->dpcSteps['step_3'][$lang['lang_locale']]['dpc_plugin_title']):"";  

                    //set the plugin section title
                    $translationArr['tr_PluginSection_'.strtolower($this->moduleName)] = $this->moduleName;

                }else{
                    //unset translation for the dashboard plugin
                    unset($translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).'_menu title']);
                    unset($translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).'_menu description']);
                    unset($translationArr['tr_'.strtolower($this->moduleName).'_dashboard_'.lcfirst($this->pluginName).' title']);                    
                }                
            }     

            //write back to file the updated translation array
            $configFactory = new Factory();
            $write = $configFactory->toFile($langFile, $translationArr);

            if(!$write){
                $errorCount++;
            }
        }

        if($errorCount){
            return $res;
        }else{
            return true;
        }
    }


    /**
     * This method updates module.config.php of the Module by adding or removing the controller_plugin and template_map entry
     * @param string $moduleDir
     * @param boolean $appendConfig
     * @return boolean
     */
    protected function updateModuleConfig($moduleDir, $appendConfig = true){
        $res = false;

        //get the existing module.config.php
        $moduleConfigFile = $moduleDir.'/config/module.config.php';
        $moduleToViewName = $this->convertToViewName($this->moduleName);
        $pluginToViewName = $this->convertToViewName($this->pluginName);
        
        //get the content of module.config.php 
        $moduleFileContent = file_get_contents($moduleConfigFile);

        if($appendConfig){         

            //add the template map entry for the dashboard plugin 
            $moduleFileContent = $this->setTemplateMapEntry($moduleFileContent, $moduleToViewName, $pluginToViewName);

            //add the controller plugin entry for the dashboard plugin 
            $moduleFileContent = $this->setControllerPluginEntry($moduleFileContent, $moduleToViewName, $pluginToViewName);
    
        }else{

            $match = null;
            $controllerPluginKey = $this->moduleName.$this->pluginName.'Plugin';
            
            //find the controller_plugin key of the created dashboard plugin, then remove
            // escape special characters in the query
            $pattern = preg_quote($controllerPluginKey, '/');
            // finalize the regular expression, matching the whole line
            $pattern = "/^.*$pattern.*\$/m";

            // search, and store all matching occurences in $matches
            if(preg_match_all($pattern, $moduleFileContent, $matches)){                  
                $match = implode("\n", $matches[0]);
                //remove the controller plugin key entry
                $moduleFileContent = str_replace($match, '', $moduleFileContent);
            }      

            //find the template_map key of the created dashboard plugin, then remove
            $templateMapKey = $moduleToViewName."/dashboard-plugins/".$pluginToViewName;
             // escape special characters in the query
            $pattern = preg_quote($templateMapKey, '/');
            // finalize the regular expression, matching the whole line
            $pattern = "/^.*$pattern.*\$/m";

            // search, and store all matching occurences in $matches
            if(preg_match_all($pattern, $moduleFileContent, $matches)){ 
                $match = implode("\n", $matches[0]);
                //remove the template map key entry
                $moduleFileContent = str_replace($match, '', $moduleFileContent);
            }   
        }      

        // Write the contents back to the file
        $res = file_put_contents($moduleConfigFile, $moduleFileContent);

        return $res;
    }

     /**
     * This method updates module.config.php of the destination module by adding a template_map key entry of the dashboard plugin
     * @param string $moduleFileContent
     * @param string $moduleToViewName
     * @param string pluginToViewName
     * @return string
     */
    protected function setTemplateMapEntry($moduleFileContent, $moduleToViewName, $pluginToViewName){        
        $templateMapKey = $moduleToViewName."/dashboard-plugins/".$pluginToViewName;
        $templateMapValue = ".'/../view/".$moduleToViewName."/dashboard-plugins/".$pluginToViewName.".phtml'"; 
                  
        $match = null;
        //search for the template_map key
        $pattern = 'template_map\s*[\'"]\s*=>\s*(array\s*\(|\[)\s*';
        $pattern = '/('.$pattern.')/';        

        if(preg_match_all($pattern, $moduleFileContent, $matches)){
            $match = implode("\n", $matches[0]);//return as string
        }

        //if template_map exists in the config, add immediately the entry
        if($match){  
            //check the existence of the template_map entries for the formatting purposes
            $moduleDir = $_SERVER['DOCUMENT_ROOT'].'/../module/'.$this->moduleName;
            $moduleConfig = include $moduleDir.'/config/module.config.php';
            $existingTemplateMapEntry = $moduleConfig['view_manager']['template_map'];

            if(count($existingTemplateMapEntry) > 0){
                $templateMapEntry = "'".$templateMapKey."' => __DIR__".$templateMapValue.",\r\n\t\t\t";
            }else{
                $templateMapEntry = "\t'".$templateMapKey."' => __DIR__".$templateMapValue.",\r\n\t\t";
            }

            $moduleFileContent = str_replace($match, substr_replace($match, $templateMapEntry, strlen($match), 0), $moduleFileContent);

        } else{  
            //search for the view_manager key to add the template_map key
            $pattern = 'view_manager\s*[\'"]\s*=>\s*(array\s*\(|\[)\s*';
            $pattern = '/('.$pattern.')/';        

            if(preg_match_all($pattern, $moduleFileContent, $matches)){
                $match = implode("\n", $matches[0]);//return as string
            }

            if($match){
                $templateMapArr ="'template_map' => ["."\t\t".                    
                    "\r\n\t\t\t'". $templateMapKey."' => __DIR__".$templateMapValue.",\r\n\t\t".                   
                "]";

                //add template_map key inside the view_manager key
                $moduleFileContent = str_replace($match, substr_replace($match, $templateMapArr.",\r\n\t\t", strlen($match), 0), $moduleFileContent);
            }        
        }  

        return  $moduleFileContent;    
    }

    /**
     * This method updates module.config.php of the destination module by adding a controller_plugin key entry of the dashboard plugin
     * @param string $moduleFileContent
     * @param string $moduleToViewName
     * @param string pluginToViewName
     * @return string
     */
    protected function setControllerPluginEntry($moduleFileContent, $moduleToViewName, $pluginToViewName){       
        $controllerPluginKey = $this->moduleName.$this->pluginName.'Plugin';
        $controllerPluginValue =  "\\".$this->moduleName."\Controller\DashboardPlugins\\".$controllerPluginKey."::class";
                
        //search for the controller_plugin invokables key
        $pattern = 'controller_plugins\s*[\'"]\s*=>\s*(array\s*\(|\[)\s*[\'"]invokables\s*[\'"]\s*=>\s*(array\s*\(|\[)';
        $pattern = '/('.$pattern.')/';
        $match = null;

        if(preg_match_all($pattern, $moduleFileContent, $matches)){
            $match = implode("\n", $matches[0]);//return as string
        }
      
        //if controller_plugin/invokables exists in the config, add immediately the entry
        if($match){                  
            $controllerPluginEntry = "'".$controllerPluginKey."' => ".$controllerPluginValue;       
            $moduleFileContent = str_replace($match, substr_replace($match, "\r\n\t\t\t".$controllerPluginEntry.",", strlen($match), 0), $moduleFileContent);
        }else{      

            //search for the 'controllers' key, then add the controller_plugin key before the 'controllers' key 
            $pattern = '[\'"]\s*controllers\s*[\'"]\s*=>\s*(array\s*\(|\[)\s*';
            $pattern = '/('.$pattern.')/';        

            if(preg_match_all($pattern, $moduleFileContent, $matches)){
                $match = implode("\n", $matches[0]);//return as string
            }

            if($match){  

                //set the template
                $template ="'controller_plugins' => ["."\r\n\t\t".
                    "'invokables' => [".
                    "\r\n\t\t\t'". $controllerPluginKey."' => ".$controllerPluginValue.",\r\n\t\t"
                    ."],\r\n\t".
                "],";

                //prepend it to 'controllers' key
                $moduleFileContent = str_replace($match, $template."\r\n\t".$match, $moduleFileContent); 
            }        
        }  

        return  $moduleFileContent;    
    }


    /**
     * This method updates Module.php of the Module by adding/removing the dashboard plugin config path
     * @param $moduleDir
     * @param $appendConfig
     * @return boolean
     */
    protected function updateModuleFile($moduleDir, $appendConfig = true)
    {     
        //get the Module.php file
        $moduleFileContent = file_get_contents($moduleDir.'/Module.php');
                  
        //set the dashboard config file 
        $dashBoardConfigFile = PHP_EOL . "\t\t\t". "include __DIR__ . '/config/dashboard-plugins/".$this->pluginName."Plugin.config.php',";
        
        if($appendConfig){
            //search for the module.config.php keyword to append the dashboard plugin config file 
            $pattern = 'module.config.php\s*[\'"]\s*,';
            $pattern = '/('.$pattern.')/';

            if(preg_match_all($pattern, $moduleFileContent, $matches)){
                $match = implode("\n", $matches[0]);//return as string
            }

            if($match){
                $moduleFileContent = str_replace($match, substr_replace($match, $dashBoardConfigFile, strlen($match), 0), $moduleFileContent);
            }
          
        }else{
            //remove the dashboard plugin config  
            $moduleFileContent = str_replace($dashBoardConfigFile, '', $moduleFileContent);
        }
        
        // Write the contents back to the file
        $res = file_put_contents($moduleDir.'/Module.php', $moduleFileContent);
        return $res;
    }


    /**
     * This method generate files to the directory
     *
     * @param string $fileName - file name
     * @param string $targetDir - the target directory where the file will created
     * @param string $fileContent - will be the content of the file created
     */
    public function generateFile($fileName, $targetDir, $fileContent = null)
    {       
        try{
            //set the plugin name
            $fileContent = str_replace('PluginName', $this->pluginName, $fileContent);
            $fileContent = str_replace('pluginName', lcfirst($this->pluginName), $fileContent);
            $fileContent = str_replace('pluginname', strtolower($this->pluginName), $fileContent);
           
            //set the module name
            $fileContent = str_replace('ModuleTpl', $this->moduleName, $fileContent);
            $fileContent = str_replace('moduleTpl', lcfirst($this->moduleName), $fileContent);
            $fileContent = str_replace('moduletpl', strtolower($this->moduleName), $fileContent);

            //create directory if not yet exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            //add file if not yet exists
            $targetFile = $targetDir.'/'.$fileName;
            if(!file_exists($targetFile)){          
                $targetFile = fopen($targetFile, 'x+');
                fwrite($targetFile, $fileContent);
                fclose($targetFile);
            }

            return true;
        }catch(Exception $e){
            return false;
        }       
    }


    /**
     * This method retrieves the content of the template files for the dashboard generation
     * @param string $path 
     * @return string 
     */
    protected function getTemplateContent($path)
    {
        return file_get_contents($this->dashboardPluginCreatorTplDir.$path);
    }

    
    /**
     * This method converts a Module name to a valid view name directory
     * @param $string
     * @return string
     */
    protected function convertToViewName($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }
       

    /**
     * This method unsets the meliscustom_toolstree_section of the app.toolstree.php file after generating a new module
     * @param string $moduleDir
     * @return boolean
     */
    protected function emptyConfigToolsTreeSection($moduleDir){
        $toolsTreeConfigFile = $moduleDir.'/config/app.toolstree.php';        
        $toolsTreeConfig = include $toolsTreeConfigFile;
        
        //unset the meliscustom_toolstree_section of the newly created module
        if(isset($toolsTreeConfig['plugins']['meliscore']['interface']['meliscore_leftmenu']['interface']['meliscustom_toolstree_section']['interface'][strtolower($this->moduleName).'_conf'])){
            unset($toolsTreeConfig['plugins']['meliscore']['interface']['meliscore_leftmenu']['interface']['meliscustom_toolstree_section']['interface'][strtolower($this->moduleName).'_conf']);
        }
        
        $configFactory = new Factory();
        $write = $configFactory->toFile($toolsTreeConfigFile, $toolsTreeConfig);

        if(!$write){
           return false;
        }

        return true;
    } 


    /**
     * This method removes the directory and its files recursively
     * ref: https://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
     * @param string $dirPath
     * @return boolean
     */
    public function removeDir($dirPath) {
        if (! is_dir($dirPath)) {
            return false;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);

        //remove files/directory inside the dir
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->removeDir($file);
            } else {
                unlink($file);
            }
        }

        //remove dir
        rmdir($dirPath);
    }


    /**
     * This will modified a string to valid zf2 module name, ref: MelisToolCreatorService
     * @param string $str
     * @return string
     */
    public function generateModuleNameCase($str) {
        $str = preg_replace('/([a-z])([A-Z])/', "$1$2", $str);
        $str = str_replace(['-', '_'], '', ucwords(strtolower($str)));
        $str = ucfirst($str);
        $str = $this->cleanString($str);
        return $str;
    }


    /**
     * Clean strings from special characters, ref: MelisToolCreatorService
     * @param string $str
     * @return string
     */
    public function cleanString($str)
    {
        $str = preg_replace("/[áàâãªä]/u", "a", $str);
        $str = preg_replace("/[ÁÀÂÃÄ]/u", "A", $str);
        $str = preg_replace("/[ÍÌÎÏ]/u", "I", $str);
        $str = preg_replace("/[íìîï]/u", "i", $str);
        $str = preg_replace("/[éèêë]/u", "e", $str);
        $str = preg_replace("/[ÉÈÊË]/u", "E", $str);
        $str = preg_replace("/[óòôõºö]/u", "o", $str);
        $str = preg_replace("/[ÓÒÔÕÖ]/u", "O", $str);
        $str = preg_replace("/[úùûü]/u", "u", $str);
        $str = preg_replace("/[ÚÙÛÜ]/u", "U", $str);
        $str = preg_replace("/[’‘‹›‚]/u", "'", $str);
        $str = preg_replace("/[“”«»„]/u", '"', $str);
        $str = str_replace("–", "-", $str);
        $str = str_replace(" ", " ", $str);
        $str = str_replace("ç", "c", $str);
        $str = str_replace("Ç", "C", $str);
        $str = str_replace("ñ", "n", $str);
        $str = str_replace("Ñ", "N", $str);

        return ($str);
    }

    /**
     * Remove accents in the string
     * @param string $str
     * @return string
     */
    public function removeAccents($str){
        $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);
        $normalized = $transliterator->transliterate($str);
        return $normalized;
    }        

    /**
     * This will remove extra spaces in the given string
     * @param string $str
     * @return string
     */
    public function removeExtraSpace($string){
        $cleanStr = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $string)));
        return $cleanStr;
    }

}