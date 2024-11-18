<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDashboardPluginCreator\Controller;

use Laminas\Session\Container;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Size;
use Laminas\File\Transfer\Adapter\Http;
use MelisCore\Controller\MelisAbstractActionController;
use MelisCore\Controller\PluginViewController;

class DashboardPluginCreatorController extends MelisAbstractActionController
{
    const NEW_MODE = "new_module";
    
    /**
     * This will render the dashboard plugin creator tool
     * @return ViewModel
    */     
    public function renderToolAction()
    {
        $view = new ViewModel();

        // Initializing the Dashboard Plugin creator session container
        $container = new Container('dashboardplugincreator');
        $container['melis-dashboardplugincreator'] = [];

        //generate new session id, this will be used in creating a temp folder path for plugin thumbnails
        $container->getManager()->regenerateId();
        $container['melis-dashboardplugincreator']['sessionID'] = $container->getManager()->getId();
        return $view;
    }

    /**
     * This will render the header of the tool
     * @return ViewModel
    */ 
    public function renderToolHeaderAction()
    {
        $view = new ViewModel();
        return $view;
    }


    /**
     * This will render the content of the tool
     * @return ViewModel
    */ 
    public function renderToolContentAction()
    {
        $view = new ViewModel();

        /**
         * Checking file permission to file and directories needed to create and activate the plugin
         */
        $filePermissionErr = [];
        if (!is_writable($_SERVER['DOCUMENT_ROOT'] . '/../config/melis.module.load.php'))
            $filePermissionErr[] = 'tr_melisdashboardplugincreator_fp_config';

        if (!is_writable($_SERVER['DOCUMENT_ROOT'] . '/../module'))
            $filePermissionErr[] = 'tr_melisdashboardplugincreator_fp_module';

        //check if temp-thumbnail directory exists and is writable
        $tempThumbnailDirectory = $this->getTempThumbnailDirectory();   
        if (!file_exists($tempThumbnailDirectory)) {
            mkdir($tempThumbnailDirectory, 0755, true);          
        }

        if (!is_writable($tempThumbnailDirectory)) {
            $filePermissionErr[] = 'tr_melisdashboardplugincreator_fp_temp_thumbnail';
        }

        if (!empty($filePermissionErr)) {
            $view->fPErr = $filePermissionErr;
            return $view;
        }

        //get the tool steps defined in the config          
        $view->toolSteps = $this->getStepConfig();
        return $view;
    }


    /**
     * This method render the steps of the tool
     * this will call dynamically the requested step 
     * @return ViewModel
     */
    public function renderDashboardPluginCreatorStepsAction()
    {  
        $view = new ViewModel();

        // The steps requested
        $curStep = $this->params()->fromPost('curStep', 1);
        $nextStep = $this->params()->fromPost('nextStep', 1);
        $validate = $this->params()->fromPost('validate', false);

        // Current viewModel
        $viewStep = new ViewModel();
        $viewStep->setTemplate('melis-dashboard-plugin-creator/render-form');
        $viewRender = $this->getServiceManager()->get('ViewRenderer');

        /**
         * This will validate the current step if 'next' button is clicked, and get the form/data of the next step if no errors in validation,
         * else, it will just get the current step form
         * 
         */
        $stepFunction = 'processStep'.$curStep;
        $viewStep = $this->$stepFunction($viewStep, $nextStep, $validate); 
        $viewStep->id = 'melisdashboardplugincreator_step'.$nextStep;   
        $viewStep->curStep = $nextStep;
        if ($nextStep) {
            $viewStep->nextStep = $nextStep + 1; 
        }         

        //if 'back' or 'next' step button is triggered
        if ($validate || ($curStep > $nextStep)) {
            // Retrieving steps form config
            $stepsConfig = $this->getStepConfig();           
            $translator = $this->getServiceManager()->get('translator');

            if (!empty($viewStep->errors)) {               
                $results['errors'] = $viewStep->errors;
                $results['textMessage'] = $translator->translate('tr_melisdashboardplugincreator_err_message');
                $results['textTitle'] = $translator->translate($stepsConfig['melisdashboardplugincreator_step'.$curStep]['name']);
            } else {   

                if (isset($viewStep->restartRequired)) {
                    $viewStep->setTemplate('melis-dashboard-plugin-creator/render-step5-finalization');
                }

                $results = [
                    'textTitle' => $translator->translate($stepsConfig['melisdashboardplugincreator_step'.$curStep]['name']),
                    'textMessage' => isset($viewStep->textMessage)?$viewStep->textMessage:null,
                    'html' => $viewRender->render($viewStep), // Sending the step view without container
                    'restartRequired' => isset($viewStep->restartRequired)?$viewStep->restartRequired:null
                ];
            }

            return new JsonModel($results);
        }
            
        // Rendering the result view and attach to the container   
        $view->step = $viewRender->render($viewStep);
        return $view;
    }

    
    /**
     * This will process step 1 of the tool
     * @param ViewModel $viewStep
     * @param int $nextStep
     * @param boolean $validate
     * @return ViewModel
    */ 
    private function processStep1($viewStep, $nextStep, $validate)
    {
        $container = new Container('dashboardplugincreator');//session container
        $curStep = 1;       
        $data = array();
        $errorMessages = array();
        $stepForm = null; 
    
        //validate form if Next button is triggered
        if ($validate) {  
            $request = $this->getRequest();
            $postValues = $request->getPost()->toArray(); 
            
            //get current step's form and data
            list($stepForm, $data) = $this->getStepFormAndData($curStep);
            
            $stepForm->setData($postValues['step-form']);   
        
            //if plugin type is single, remove the validation for the tab count
            if (!empty($postValues['step-form']['dpc_plugin_type'])) {
                if ($postValues['step-form']['dpc_plugin_type'] == 'single') {
                    $stepForm->getInputFilter()->remove('dpc_tab_count');
                } else {
                    //if plugin type is multi and tab count is empty, remove the between validator
                    if ($postValues['step-form']['dpc_tab_count'] == "") {  
                        $newValidatorChain = new \Laminas\Validator\ValidatorChain;
                        foreach ($stepForm->getInputFilter()->get('dpc_tab_count')->getValidatorChain()->getValidators() 
                                as $validator) {                            
                            if (!($validator['instance'] instanceof \Laminas\Validator\Between)) {
                                $newValidatorChain->addValidator($validator['instance'],
                                                                true);
                            }
                        }
                        $stepForm->getInputFilter()->get('dpc_tab_count')->setValidatorChain($newValidatorChain);
                    }
                }
            } else {
                $stepForm->getInputFilter()->remove('dpc_tab_count');
            }
        
            //if plugin destination is new, remove the validation for the existing module and vice versa
            if (!empty($postValues['step-form']['dpc_plugin_destination'])) {
                if ($postValues['step-form']['dpc_plugin_destination'] == self::NEW_MODE) {
                    $stepForm->getInputFilter()->remove('dpc_existing_module_name');
                } else {
                    $stepForm->getInputFilter()->remove('dpc_new_module_name');
                }
            } else {
                $stepForm->getInputFilter()->remove('dpc_new_module_name');
                $stepForm->getInputFilter()->remove('dpc_existing_module_name');
            }            


            //if current step is valid, save form data to session and get the view of the next step 
            if ($stepForm->isValid()) {                 
                //validate new module name entered for duplicates or if the name is a reserved word
                if (!empty($postValues['step-form']['dpc_new_module_name'])) {
                    $newModuleName = strtolower($postValues['step-form']['dpc_new_module_name']);

                    //retrieve the list of reserved words           
                    $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
                    $reservedKeywords = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/reserved_keywords');

                    if (in_array(trim($newModuleName), $reservedKeywords)) {
                        // Adding error message to form field
                        $translator = $this->getServiceManager()->get('translator');
                        $stepForm->get('dpc_new_module_name')->setMessages([
                            'ReservedKeyword' => sprintf($translator->translate('tr_melisdashboardplugincreator_err_module_name_reserved_keyword'), $postValues['step-form']['dpc_new_module_name'])
                        ]);

                        $errorMessages = $stepForm->getMessages();
                    }


                    /**
                     * Validating the module entered if its already existing on the platform
                     */
                    $modulesSvc = $this->getServiceManager()->get('ModulesService');
                    $existingModules = array_merge($modulesSvc->getModulePlugins(), \MelisCore\MelisModuleManager::getModules());
                    $existingModules = array_map('strtolower', $existingModules);     

                    //set error if the entered module name has duplicate
                    if (in_array(trim($newModuleName), $existingModules)) {                      
                        // Adding error message to module input
                        $translator = $this->getServiceManager()->get('translator');
                        $stepForm->get('dpc_new_module_name')->setMessages([
                            'ModuleExist' => sprintf($translator->translate('tr_melisdashboardplugincreator_err_module_exist'), $postValues['step-form']['dpc_new_module_name'])
                        ]);

                        $errorMessages = $stepForm->getMessages();
                    }
                }

                //validate plugin name if it already exists for the selected existing module
                if (!empty($postValues['step-form']['dpc_existing_module_name'])) {  
                    $existingModuleName = $postValues['step-form']['dpc_existing_module_name'];
                    //call dashboard plugin creator service 
                    $dpcService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
                    $modulePlugins = $dpcService->getModuleExistingPlugins($existingModuleName);

                    if ($modulePlugins && $modulePlugins[$existingModuleName]['pluginId']) {
                        $dashboardPluginCreatorSrv = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
                        $newPluginName = $dashboardPluginCreatorSrv->generateModuleNameCase($postValues['step-form']['dpc_plugin_name']);

                        if (in_array(trim($newPluginName), $modulePlugins[$existingModuleName]['pluginId'])) {
                            //Adding error message to module input
                            $translator = $this->getServiceManager()->get('translator');
                            $stepForm->get('dpc_plugin_name')->setMessages([
                                'PluginExist' => sprintf($translator->translate('tr_melisdashboardplugincreator_err_plugin_name_exist'), $postValues['step-form']['dpc_plugin_name'])
                            ]);

                            //adding a variable to viewmodel to flag an error
                        $errorMessages = $stepForm->getMessages();
                        }
                    }                    
                }

                //if current step form is valid, save form data to session and get the next step's form 
                if (empty($errorMessages)) {
                    //save to session   
                    $container['melis-dashboardplugincreator']['step_1'] = $stepForm->getData(); 
                                                
                    //get next step's form and data
                    list($stepForm,$data) = $this->getStepFormAndData($nextStep);
                }       

            } else {     
                $errorMessages = $stepForm->getMessages();               
            }

            //format error labels
            if ($errorMessages) {
                foreach ($errorMessages as $keyError => $valueError) {
                    foreach ($stepForm->getElements() as $keyForm => $valueForm) {
                        $elementName = $valueForm->getAttribute('name');
                        $elementLabel = $valueForm->getLabel();              

                        if ($elementName == $keyError &&
                            !empty($elementLabel))
                            $errorMessages[$keyError]['label'] = $elementLabel;
                    }
                }               
            }
        } else {
            list($stepForm, $data) = $this->getStepFormAndData($nextStep);          
        }
        
        $viewStep->stepForm = $stepForm;//the form to be displayed
        $viewStep->errors = $errorMessages;
        $viewStep->data = $data;
        return $viewStep;
    }
    
    
    /**
     * This will process step 2 of the tool
     * @param ViewModel $viewStep
     * @param int $nextStep
     * @param boolean $validate
     * @return ViewModel
    */ 
    private function processStep2($viewStep, $nextStep, $validate)
    {   
        $container = new Container('dashboardplugincreator');//session container
        $sessionID = $container['melis-dashboardplugincreator']['sessionID'];
        $curStep = 2;       
        $data = array();
        $errors = array();
        $languageFormErrorMessages = array();
        $uploadFormErrorMessages = array();
        $textMessage = '';
        $stepForm = null; 
        $isValid = 0;
        $isValid2ndForm = 0;
        $pluginThumbnail = null;

        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');   

        //validate form if Next button is triggered
        if ($validate) {         
            $request = $this->getRequest();
            $postValues = $request->getPost()->toArray();
            $uploadedFile = $request->getFiles()->toArray();
                
            //merge upload data with the other posted values
            if (!empty($uploadedFile)) {
                $postValues = array_merge_recursive($postValues,$uploadedFile);               
            }

            //validate language form
            list($isValidLanguageForm, $languageFormErrorMessages) = $this->validateMultiLanguageForm($curStep, $postValues);  

            //validate upload form
            $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step2_form2', 'melisdashboardplugincreator_step2_form2');
            $stepForm2 = $factory->createForm($appConfigForm);         
            $stepForm2->setData($postValues['step-form']);

            if ($stepForm2->isValid()) {        
                //get the saved thumbnail 
                $pluginThumbnail = !empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])?$container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail']:null;

                //if no saved uploaded thumbnail, try to upload again
                if (empty($pluginThumbnail)) {

                    //process uploading of thumbnail
                    list($isValid2ndForm, $pluginThumbnail, $textMessage) = $this->uploadPluginThumbnail($postValues['step-form']['dpc_plugin_upload_thumbnail']);

                    if ($isValid2ndForm) {
                        //save to session   
                        $container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'] = '/dpc/temp-thumbnail/' . $sessionID . '/' . pathinfo($pluginThumbnail, PATHINFO_FILENAME) . '.' . pathinfo($pluginThumbnail, PATHINFO_EXTENSION);  

                    } else {         
                        //unset previously uploaded file
                        if (!empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])) {
                            unset($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail']); 
                        }                
            
                        $stepForm2->get('dpc_plugin_upload_thumbnail')->setMessages([
                            'pluginError' => $textMessage,
                            'label' => 'Plugin thumbnail'
                        ]);

                        $uploadFormErrorMessages = $this->formatErrors($stepForm2->getMessages(), $stepForm2->getElements());   
                    }  
                } else {
                    $isValid2ndForm = 1;
                }

            } else {    
                $uploadFormErrorMessages = $this->formatErrors($stepForm2->getMessages(), $stepForm2->getElements());
            }    
            
            //check if the forms for the current step are all valid
            if ($isValidLanguageForm && $isValid2ndForm) {
                $isValid = 1;
            } else {
                //merge language and upload form errors 
                $errors = ArrayUtils::merge($languageFormErrorMessages, $uploadFormErrorMessages);                
            }      

            //get next step's form
            if ($isValid) {                
                list($stepForm, $data) = $this->getStepFormAndData($nextStep);
            } 
        } else { 
            list($stepForm, $data) = $this->getStepFormAndData($nextStep); 
        }

        $viewStep->stepForm = $stepForm;//the form to be displayed
        $viewStep->errors = $errors;
        $viewStep->data = $data;
        return $viewStep;
    }
    
    /**
     * This will process step 3 of the tool
     * @param ViewModel $viewStep
     * @param int $nextStep
     * @param boolean $validate
     * @return ViewModel
    */ 
    private function processStep3($viewStep, $nextStep, $validate)
    {       
        $container = new Container('dashboardplugincreator');//session container
        $curStep = 3;       
        $data = array();
        $errors = array();
        $languageFormErrorMessages = array();
        $iconFormErrorMessages = array();
        $stepForm = null; 
        $isValid = 0;
        $isValid2ndForm = 0;  

        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');   

        //validate form if Next button is triggered
        if ($validate) {              
            $request = $this->getRequest();
            $postValues = $request->getPost()->toArray();
        
            //validate language form
            list($isValidLanguageForm, $languageFormErrorMessages) = $this->validateMultiLanguageForm($curStep, $postValues);  

            //validate icon form
            $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step3_form2', 'melisdashboardplugincreator_step3_form2');
            $stepForm2 = $factory->createForm($appConfigForm); 
            //add dashboard tab icon elements to the icon form if multi-tab is selected
            $stepForm2 = $this->setDashboardTabIconElements($stepForm2);

            $stepForm2->setData($postValues['step-form']);

            if ($stepForm2->isValid()) {              
                $isValid2ndForm = 1; 
                $container['melis-dashboardplugincreator']['step_3']['icon_form'] = $stepForm2->getData();

            } else {
                $iconFormErrorMessages = $this->formatErrors($stepForm2->getMessages(), $stepForm2->getElements());  
            }          
                        
            //check if the forms for the current step are all valid
            if ($isValidLanguageForm && $isValid2ndForm) {
                $isValid = 1;
            } else {
                //merge language and upload form errors 
                $errors = ArrayUtils::merge($languageFormErrorMessages, $iconFormErrorMessages);                
            }      

            //get next step's form
            if ($isValid) {
                list($stepForm, $data) = $this->getStepFormAndData($nextStep);
            } 
        } else { 
            list($stepForm, $data) = $this->getStepFormAndData($nextStep); 
        }

        $viewStep->stepForm = $stepForm;//the form to be displayed
        $viewStep->errors = $errors;
        $viewStep->data = $data;
        return $viewStep;
    }

    /**
     * This will process step 4 of the tool
     * @param ViewModel $viewStep
     * @param int $nextStep
     * @param boolean $validate
     * @return ViewModel
    */  
    private function processStep4($viewStep, $nextStep, $validate)
    {           
        $data = array();        
        $stepForm = null;   

        list($stepForm, $data) = $this->getStepFormAndData($nextStep); 

        $viewStep->stepForm = $stepForm;//the form to be displayed       
        $viewStep->data = $data;
        return $viewStep;
    }


    /**
     * This will process step 5 of the tool
     * @param ViewModel $viewStep
     * @param int $nextStep
     * @param boolean $validate
     * @return ViewModel
    */
    private function processStep5($viewStep, $nextStep, $validate)
    {
        $container = new Container('dashboardplugincreator');//session container             
        $data = array();       
        $errors = array();
        $stepForm = null; 
    
        //generate dashboard plugin
        if ($validate) { 
            $request = $this->getRequest();
            $postValues = $request->getPost()->toArray();        
            $isActivatePlugin = !empty($postValues['step-form']['dpc_activate_plugin'])?1:0;

            //if destination of the plugin is the new module, create first the new module before adding the dashboard plugin files
            if ($container['melis-dashboardplugincreator']['step_1']['dpc_plugin_destination'] == self::NEW_MODE) {              
                // Initializing the Tool creator session container
                $toolContainer = new Container('melistoolcreator');
                $toolContainer['melis-toolcreator'] = [];    
                $toolContainer['melis-toolcreator']['step1']['tcf-name'] = $container['melis-dashboardplugincreator']['step_1']['dpc_new_module_name'];
                $toolContainer['melis-toolcreator']['step1']['tcf-tool-type'] = 'blank';
                $toolContainer['melis-toolcreator']['step1']['tcf-tool-edit-type'] = 'modal';
        
                $toolCreatorSrv = $this->getServiceManager()->get('MelisToolCreatorService');
                $toolCreatorSrv->createTool();             
            }
    
            //call service to generate the dashboard plugin 
            $dpcService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
            $result = $dpcService->generateDashboardPlugin();

            if ($result) {
                if ($container['melis-dashboardplugincreator']['step_1']['dpc_plugin_destination'] == self::NEW_MODE) {
                    // Activate new module
                    $moduleSvc = $this->getServiceManager()->get('ModulesService');
                    $moduleSvc->activateModule($toolCreatorSrv->moduleName());

                    // Reloading module paths
                    unlink($_SERVER['DOCUMENT_ROOT'].'/../config/melis.modules.path.php');

                    //unset tool container
                    unset($toolContainer['melis-toolcreator']);  

                    // clear cache for dashboard menu
                    $melisCoreCacheSystem = $this->getServiceManager()->get('MelisCoreCacheSystemService');
                    $melisCoreCacheSystem->deleteCacheByPrefix('meliscore_dashboard_menu_content_', PluginViewController::cacheConfig);
                    
                }
            
                //reload page to activate the plugin
                if ($isActivatePlugin) {   
                    $viewStep->restartRequired = 1;
                } else {      
                    $viewStep->restartRequired = 0;
                }   

            } else {
                //set errors
                $translator = $this->getServiceManager()->get('translator');
                $viewStep->textMessage = $translator->translate('tr_melisdashboardplugincreator_generate_plugin_error_encountered');
            }    
        }
        
        list($stepForm, $data) = $this->getStepFormAndData($nextStep);              
        $viewStep->stepForm = $stepForm;//the form to be displayed       
        $viewStep->data = $data;
        return $viewStep;            
    }


    /**
     * This will retrieve the available forms and data for the given step
     * @param int $curStep
     * @param bool $validate
     * @return array
    */
    private function getStepFormAndData($curStep, $validate = false)
    {
        $container = new Container('dashboardplugincreator');//session container

        //get the current step's form config
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');          
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements); 
        $data = array();
        $errors = array();
        $stepForm = null;
        $stepFormArr = array();
        $tabCount = 0;
        
        switch ($curStep) {
            case 1:      
                $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step1_form', 'melisdashboardplugincreator_step1_form');                               
                $stepForm = $factory->createForm($appConfigForm);                 
            
                //check if there is a session data
                if (!empty($container['melis-dashboardplugincreator']['step_1'])) {                 
                    $stepForm->setData($container['melis-dashboardplugincreator']['step_1']);  
                }      

                //get the current locale used from meliscore session
                $container = new Container('meliscore');
                $data['lang_locale'] = $container['melis-lang-locale'];  
                break;

            case 2:  
                //get language form
                list($stepFormArr, $data['languages']) = $this->getLanguageForms($curStep);            
                
                //get the 2nd form
                $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step2_form2', 'melisdashboardplugincreator_step2_form2'); 
                $stepForm2 = $factory->createForm($appConfigForm);                              

                //check if there is a session data
                if (!empty($container['melis-dashboardplugincreator']['step_2'])) {
                    $stepForm2->setData($container['melis-dashboardplugincreator']['step_2']);
                }            
                $stepFormArr['form2'] = $stepForm2;
                
                //get the thumbnail saved in session
                if (!empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])) {                    
                    $data['thumbnail'] = $container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'];
                }   

                //get the current locale used from meliscore session
                $container = new Container('meliscore');
                $data['lang_locale'] = $container['melis-lang-locale'];                         
            
                $stepForm = $stepFormArr;
                break;

            case 3: 
                //get language form
                list($stepFormArr, $data['languages']) = $this->getLanguageForms($curStep);            
                
                //get the step 3 icon form
                $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step3_form2', 'melisdashboardplugincreator_step3_form2'); 
                $stepForm2 = $factory->createForm($appConfigForm);                

                //add dashboard tab icon elements to the icon form if multi-tab is selected
                $stepForm2 = $this->setDashboardTabIconElements($stepForm2);
                        
                //check if there is a session data
                if (!empty($container['melis-dashboardplugincreator']['step_3']['icon_form'])) {
                    $stepForm2->setData($container['melis-dashboardplugincreator']['step_3']['icon_form']);
                }            
                $stepFormArr['form2'] = $stepForm2;    
        
                $stepForm = $stepFormArr;
                $data['tabCount'] = !empty($container['melis-dashboardplugincreator']['step_1']['dpc_tab_count'])
                                    ?$container['melis-dashboardplugincreator']['step_1']['dpc_tab_count']:0;

                //get the current locale used from meliscore session
                $container = new Container('meliscore');
                $data['lang_locale'] = $container['melis-lang-locale'];                                     
                break;

            case 4:
                //get all the saved values from step 1 to 3
                $container = new Container('dashboardplugincreator');//session container
                $data['dpcSessionData'] = $container['melis-dashboardplugincreator'];
                
                // get all languages available in the plaftform
                $coreLang = $this->getServiceManager()->get('MelisCoreTableLang');
                $data['languages'] = $coreLang->fetchAll()->toArray();

                //get dashboard tab icon options 
                $data['tabIconOptions'] = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/dashboardTabIcons');

                //get all steps
                $data['steps'] = $this->getStepConfig();
                break;

            case 5:
                $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step5_form', 'melisdashboardplugincreator_step5_form');                               
                $stepForm = $factory->createForm($appConfigForm);    
                $data['isInactiveExistingModule'] = $this->isInactiveExistingModule(); 
                $data['existingModule'] =  $existingModuleName = !empty($container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name']) ? $container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name'] : null;

                break;               
        }     

        return array($stepForm, $data);  
    }

    /**
     * This will check if the selected existing module on Step 1 is inactive or not
     * @param obj $stepForm
     * @return obj
    */
    private function isInactiveExistingModule() 
    {  
        $container = new Container('dashboardplugincreator');//session container
        $existingModuleName = !empty($container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name']) ? $container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name'] : null;
        $isExistingModuleInactive = 0;//default to active
        
        if ($existingModuleName) {
            $moduleSvc = $this->getServiceManager()->get('ModulesService');
            $activeModules = $moduleSvc->getActiveModules();
            
            //check if the selected existing module is not one of the active modules of the platform
            if (!in_array($existingModuleName, $activeModules)) {
                $isExistingModuleInactive = 1;
            }
        } 

        return $isExistingModuleInactive;       
    }

    /**
     * This will dynamically set the dashboard tab field elements based on the number of tabs given in step 1
     * @param obj $stepForm
     * @return obj
    */
    private function setDashboardTabIconElements($stepForm)
    {       
        $container = new Container('dashboardplugincreator');//session container
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');  

        //get here the plugin type from step 1
        $pluginType = $container['melis-dashboardplugincreator']['step_1']['dpc_plugin_type'];

        //add dashboard tab icon fields to form if the selected plugin type is multi-tab
        if ($pluginType && $pluginType == 'multi') {
            $tabCount = $container['melis-dashboardplugincreator']['step_1']['dpc_tab_count'];
            $dashboardTabIconOptions = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/dashboardTabIcons');  
            $translator = $this->getServiceManager()->get('translator');
            $inputFilter = $stepForm->getInputFilter();

            //add dashboard tab icon elements to form
            for ($i = 1; $i <= $tabCount; $i++) {
                $element = new \Laminas\Form\Element\Radio('dpc_plugin_tab_icon_'.$i);
                $element->setLabel($translator->translate('tr_melisdashboardplugincreator_dashboard_tab_label').' '.$i);
                $element->setValueOptions($dashboardTabIconOptions); 
                $element->setAttributes([
                    'class' => 'form-control',
                    'required' => 'required']
                );          
                $element->setDisableInArrayValidator(true);           
                $stepForm->add($element);
                            
                //add validator    
                $inputFilter->add([
                    'name' => 'dpc_plugin_tab_icon_'.$i,                
                    'validators' => [
                        [
                        'name' => 'NotEmpty',
                            'options' => [
                                'messages' => [
                                    \Laminas\Validator\NotEmpty::IS_EMPTY => $translator->translate('tr_melisdashboardplugincreator_err_empty'),
                                ],
                            ],

                        ]
                    ],
                ]);
            }     

            $stepForm->setInputFilter($inputFilter);            
        }

        return $stepForm;
    }


    /**
     * This retrieves the language forms and the list of languages available in the platform
     * @param int $step
     * @return array
    */
    private function getLanguageForms($step)
    {
        $container = new Container('dashboardplugincreator');
        // get all languages available in the plaftform
        $coreLang = $this->getServiceManager()->get('MelisCoreTableLang');
        $languages = $coreLang->fetchAll()->toArray();

        //get the language form set for the given step
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');          
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements); 
        $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step'.$step.'_form1', 'melisdashboardplugincreator_step'.$step.'_form1'); 

        //get the current locale used from meliscore session
        $melisCoreContainer = new Container('meliscore');
        $locale = $melisCoreContainer['melis-lang-locale']; 

        //set the current locale as the first language value in the array
        foreach ($languages as $key => $langData) {
            if (trim($langData["lang_locale"]) == trim($locale)) {
                unset($languages[$key]);
                array_unshift($languages,$langData);
            }
        }

        // Generate form for each language
        foreach ($languages As $key => $lang) {           
            $stepFormtmp = $factory->createForm($appConfigForm);
        
            if (!empty($container['melis-dashboardplugincreator']['step_'.$step][$lang['lang_locale']])) {
                $stepFormtmp->setData($container['melis-dashboardplugincreator']['step_'.$step][$lang['lang_locale']]);
            }                                   

            $stepFormtmp->get('dpc_lang_local')->setValue($lang['lang_locale']);

            // Adding language form
            $stepFormArr['languageForm'][$lang['lang_locale']] = $stepFormtmp;

            // Language label
            $languages[$key]['lang_label'] = $this->langLabel($lang['lang_locale'], $lang['lang_name']);
        }       
        
        return array($stepFormArr, $languages);
    }

    /**
     * This retrieves the list of steps of the tool from the config
     * @return array
    */
    private function getStepConfig()
    {
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
        $toolSteps = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/steps'); 
        return $toolSteps;
    }

    /**
     * This method return the language name with flag image
     * if exist
     *
     * @param $locale
     * @param $langName
     * @return string
     */
    private function langLabel($locale, $langName)
    {        
        $langLabel = '<span>'. $langName .'</span>';

        $lang = explode('_', $locale);
        if (!empty($lang[0])) {

            $moduleSvc = $this->getServiceManager()->get('ModulesService');
            $imgPath = $moduleSvc->getModulePath('MelisCore').'/public/assets/images/lang/'.$lang[0].'.png';

            if (file_exists($imgPath)) 
                $langLabel .= '<span class="pull-right"><img src="/MelisCore/assets/images/lang/'.$lang[0].'.png"></span>';
            else
                $langLabel .= '<span style="border: 1px solid #fff;padding: 4px 4px;line-height: 10px;float: right;margin: 5px;">'. strtoupper($lang[0]) .'</span>';
        }

        return $langLabel;
    }

    /**
     * This validates the language forms for the given step
     * @param int $curStep
     * @param array $formData
     * @return array
    */
    private function validateMultiLanguageForm($curStep, $formData)
    {
        $container = new Container('dashboardplugincreator');//to store the session data
        $translator = $this->getServiceManager()->get('translator');
        // Meliscore languages
        $coreLang = $this->getServiceManager()->get('MelisCoreTableLang');
        $languages = $coreLang->fetchAll()->toArray();
        $languageCount = count($languages);

        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');          
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);       
        $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step'.$curStep.'_form1', 'melisdashboardplugincreator_step'.$curStep.'_form1');  
        $validFormCount = 0;
        $isValid = 0;
        $pluginTitleDuplicate = 0;
        $errors = array();

        foreach ($languages as $lang) {
            // Generating form for each language
            $stepFormtmp = $factory->createForm($appConfigForm);
            
            //loop through the language form data
            $ctr = 1;
            foreach ($formData['step-form'] as $val) {               
                if ($val['dpc_lang_local'] && $val['dpc_lang_local'] == $lang['lang_locale']) {                  
                    $stepFormtmp->setData($val);                    
                }  

                $ctr++;

                if ($ctr > $languageCount) {                    
                    break;
                }              
            }
        
            if ($stepFormtmp->isValid()) {   

                //validate plugin title entered for duplicates if current step is 2 and destination is existing module
                if ($curStep == 2 && !empty($container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name'])) {     
                    //call dashboard plugin creator service 
                    $dpcService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');

                    $existingTranslatedPluginTitle = $dpcService->getExistingTranslatedPluginTitle($dpcService->getModuleExistingPlugins($container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name']), $container['melis-dashboardplugincreator']['step_1']['dpc_existing_module_name']);
            
                    //check here if the plugin title for the specific language has duplicates
                    if ($existingTranslatedPluginTitle && in_array($dpcService->removeExtraSpace($stepFormtmp->get('dpc_plugin_title')->getValue()), $existingTranslatedPluginTitle[$lang['lang_locale']])) {
                                    
                        $stepFormtmp->get('dpc_plugin_title')->setMessages([
                            'PluginTitleExist_'.$lang['lang_locale'] => sprintf($translator->translate('tr_melisdashboardplugincreator_err_plugin_title_exist'), $stepFormtmp->get('dpc_plugin_title')->getValue(), $lang['lang_name'])
                        ]);

                        $pluginTitleDuplicate++;
                        $errors = ArrayUtils::merge($errors, $stepFormtmp->getMessages());

                    } else {
                        $validFormCount++;                      
                    }
                } else {
                    $validFormCount++;                    
                }                                 

            } else {                              

                //if no duplicate in plugin title, get the form error messages
                if ($pluginTitleDuplicate == 0) {
                    $errors = ArrayUtils::merge($errors, $stepFormtmp->getMessages());
                }
            }   

            //add to session the posted values
            $container['melis-dashboardplugincreator']['step_'.$curStep][$lang['lang_locale']] = $stepFormtmp->getData();

        }//end foreach

        //if at least 1 form is valid, or if there are no duplicates in the plugin title for each language, flag as valid
        if ($validFormCount && $pluginTitleDuplicate == 0) {            
            $isValid = 1;
            $errors = array();
        } else {           
            $errors = $this->formatErrors($errors, $stepFormtmp->getElements());
        }       

        return array($isValid, $errors);
    }

    /**
     * This creates a directory if not yet existed
     * @param string $path
     * @return boolean
    */
    private function createFolder($path)
    {  
        if (file_exists($path)) {          
            chmod($path, 0777);
            $status = true;
        } else {            
            $status = mkdir($path, 0777, true);           
        }
        return $status;
    }

    /**
     * This uploads the plugin thumbnail to the temp folder
     * @param array $uploadedFile
     * @return array
    */
    private function uploadPluginThumbnail($uploadedFile)
    {        
        $container = new Container('dashboardplugincreator');
        $_FILES = array($uploadedFile);

        try {
            $tool = $this->getServiceManager()->get('MelisCoreTool'); 
            $thumbnailTempPath = $this->getTempThumbnailDirectory();
            //attach current session id to temp-thumbnail directory
            $sessionID = $container['melis-dashboardplugincreator']['sessionID'];
            $thumbnailTempPath = $thumbnailTempPath.$sessionID.'/';

            $upload = false;
            $textMessage = '';
            $fileName = '';

            $imageValidator = new IsImage([
                'messages' => [
                    'fileIsImageFalseType' => $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_image_imageFalseType'),
                    'fileIsImageNotDetected' => $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_image_imageNotDetected'),
                    'fileIsImageNotReadable' => $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_image_imageNotReadable'),
                ],
            ]);           

            if (!empty($uploadedFile['name'])) { 
            
                if ($this->createFolder($thumbnailTempPath)) {    

                    //call dashboard plugin creator service 
                    $dpcService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
                    //clean filename by removing accents and spaces
                    $fileName = $dpcService->removeAccents(str_replace(' ','_',trim($uploadedFile['name'])));

                    /** validate image  */
                    if (!empty($uploadedFile['tmp_name'])) {

                        //check if file is image
                        $sourceImg = @imagecreatefromstring(@file_get_contents($uploadedFile['tmp_name']));
                        if ($sourceImg === false) {                          
                            return array($upload, $fileName, $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_image_imageFalseType'));
                        }

                        //check if size does not exceed the limit set
                        //set size validator
                        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
                        $pluginThumbnailConfig = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/plugin_thumbnail');          
                        $minSize = $pluginThumbnailConfig['min_size'];
                        $maxSize = $pluginThumbnailConfig['max_size'];
                    
                        // Limit the file size based on what is set in the config
                        $sizeValidator = new Size([
                            'min' => $minSize,              
                            'max' => $maxSize,
                        ]);

                        // Perform validation with file path
                        if (!$sizeValidator->isValid($uploadedFile['tmp_name'])) {                           
                            return array($upload, $fileName, $tool->getTranslation('tr_melisdashboardplugincreator_upload_too_big', array($this->formatBytes($maxSize)))   );
                        }
                    }

                    $adapter = new Http();     
                    $validator = array($imageValidator);  
                    $adapter->setValidators($validator, $fileName);

                    if ($adapter->isValid()) {
                        $adapter->setDestination($thumbnailTempPath);
                        //adds file directory to filename      
                        $fileName = $thumbnailTempPath .'/'. $fileName;          
                                                
                        $adapter->addFilter('Laminas\Filter\File\Rename', [
                            'target' => $fileName,
                            'overwrite' => true,
                        ]);

                        // if uploaded successfully
                        if ($adapter->receive()) {
                            $upload = true;                            
                        } else {                          
                            $textMessage = $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_error_encounter');
                        }
                    } else {   
                        foreach ($adapter->getMessages() as $message) {
                            $textMessage = $message;
                        }
                    }
                } else {
                    $textMessage = $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_file_path_rights_error');
                }
            } else {
                $textMessage = $tool->getTranslation('tr_melisdashboardplugincreator_save_upload_empty_file');
            }

            return array($upload, $fileName, $textMessage);         

        } catch (\Exception $ex) {
            exit($ex->getMessage());
        }
    }

    /**
     * This processes the uploading of the plugin thumbnail     
     * @return Laminas\View\Model\JsonModel
    */
    public function processUploadAction()
    {   
        $container = new Container('dashboardplugincreator');//session container
        $sessionID = $container['melis-dashboardplugincreator']['sessionID'];   
        $errors = array();       
        $uploadFormErrorMessages = array();
        $textMessage = '';    
        $isValid2ndForm = 0;
        $pluginThumbnail = null;

        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');   
    
        $request = $this->getRequest();       
        $uploadedFile = $request->getFiles()->toArray();

        //validate upload form
        $appConfigForm = $melisCoreConfig->getFormMergedAndOrdered('melisdashboardplugincreator/forms/melisdashboardplugincreator_step2_form2', 'melisdashboardplugincreator_step2_form2');
        $stepForm2 = $factory->createForm($appConfigForm);         
        $stepForm2->setData($uploadedFile['dpc_plugin_upload_thumbnail']);

        if ($stepForm2->isValid()) {  
            //process uploading of thumbnail
            list($isValid2ndForm, $pluginThumbnail, $textMessage) = $this->uploadPluginThumbnail($uploadedFile['dpc_plugin_upload_thumbnail']);

            if ($isValid2ndForm) {
                //save to session   
                $container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'] = '/dpc/temp-thumbnail/' . $sessionID . '/' . pathinfo($pluginThumbnail, PATHINFO_FILENAME) . '.' . pathinfo($pluginThumbnail, PATHINFO_EXTENSION); 

            } else {         
                //unset previously uploaded file
                if (!empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])) {
                    unset($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail']); 
                }                
    
                $stepForm2->get('dpc_plugin_upload_thumbnail')->setMessages([
                    'pluginError' => $textMessage,
                    'label' => 'Plugin thumbnail'
                ]);

                $uploadFormErrorMessages = $this->formatErrors($stepForm2->getMessages(), $stepForm2->getElements());   
            }  

        } else {           
            $uploadFormErrorMessages = $this->formatErrors($stepForm2->getMessages(), $stepForm2->getElements());
        }           

        // Retrieving steps form config
        $stepsConfig = $this->getStepConfig();
        $translator = $this->getServiceManager()->get('translator');

        $results = array(
            'success' => $isValid2ndForm,
            'errors' => $uploadFormErrorMessages,
            'pluginThumbnail' => !empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])?$container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail']:null,
            'textTitle' => $translator->translate($stepsConfig['melisdashboardplugincreator_step2']['name']),
            'textMessage' => ''
        );

        return new JsonModel($results);
    }


    /**
     * This sets the label for the error messages
     * @param array $errors
     * @param array $formElements
     * @return array
    */
    private function formatErrors($errors, $formElements)
    {       

        foreach ($errors as $keyError => $valueError) {       
            foreach ($formElements as $keyForm => $valueForm) {
                $elementName = $valueForm->getAttribute('name');
                //override form label with the custom one if given
                $elementLabel = !empty($valueError['label'])?$valueError['label']:$valueForm->getLabel();              

                if ($elementName == $keyError &&
                    !empty($elementLabel))
                    $errors[$keyError]['label'] = $elementLabel;
            }
        }

        return $errors;
    }


    /**
     * This method removes the temp thumbnail directory for the current session
     * @return boolean
    */
    public function removeTempThumbnailDirAction()
    {   
        // Initializing the Dashboard Plugin creator session container
        $container = new Container('dashboardplugincreator');
        
        if (!empty($container['melis-dashboardplugincreator']) && !empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])) {       
            //call dashboard plugin creator service 
            $dpcService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');

            //get the temp directory that stored the uploaded plugin thumbnails        
            $tempPath = pathinfo($dpcService->getTempThumbnail(), PATHINFO_DIRNAME);  
        
            if ($tempPath) {               
                //remove temp thumbnail directory 
            $dpcService->removeDir($tempPath); 
            }  
        }
        return true;
    }

    /*ref: MelisComDocumentController*/
    /**
     * this will format the size of the uploaded file into kb
     * @param number of bytes
     * @return bytes
    */
    private function formatBytes($bytes) 
    {
        $size = $bytes;
        $units = array( 'B', 'Ko', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return round(number_format($size / pow(1024, $power), 2, '.', ',')) .  $units[$power];
    }


    /**
     * This method will delete the uploaded plugin thumbnail from the session data
     * @return boolean
    */
    public function removePluginThumbnailAction()
    {   
        // Initializing the Dashboard Plugin creator session container
        $container = new Container('dashboardplugincreator');
        
        if (!empty($container['melis-dashboardplugincreator'])) { 
            if (!empty($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail'])) {
                unset($container['melis-dashboardplugincreator']['step_2']['plugin_thumbnail']);
            }          
        }
        
        $results = array(
            'success' => 1       
        );

        return new JsonModel($results);
    }

    /**
     * Retrieves the temp thumbnail directory
     * @return string
    */
    private function getTempThumbnailDirectory()
    {
        $melisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
        $pluginThumbnailConfig = $melisCoreConfig->getItem('melisdashboardplugincreator/datas/plugin_thumbnail');       

        return $pluginThumbnailConfig['path'];
    }
}