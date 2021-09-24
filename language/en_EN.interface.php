<?php
    return [
		'tr_melisdashboardplugincreator_title' => 'Dashboard Plugin Creator',
		'tr_melisdashboardplugincreator_desc' => 'The dashboard plugin creator generates new ready-to-use dashboard plugins.',

        //Buttons
        'tr_melisdashboardplugincreator_back' => 'Back',
        'tr_melisdashboardplugincreator_next' => 'Next',
        'tr_melisdashboardplugincreator_finish_and_create_the_plugin' => 'Finish and create the plugin',

		 // Warnings
	    'tr_melisdashboardplugincreator_fp_title' => 'File permission denied',
	    'tr_melisdashboardplugincreator_fp_msg' => 'In-order to create dashboard plugin using this module, please give the rights to write in the following directories or contact the administrator if the problem persists',
	    'tr_melisdashboardplugincreator_fp_config' => '<b>/config/melis.module.load.php</b> - this file is required to activate a new module after its creation',
	 	'tr_melisdashboardplugincreator_fp_module' => '<b>/module</b> - The directory where the created modules are saved',

	     // Error messages
	    'tr_melisdashboardplugincreator_err_message' => 'Unable to proceed to the next step, please try again',
	    'tr_melisdashboardplugincreator_err_invalid_name' => 'Only alphabetic characters are authorized',	
        'tr_melisdashboardplugincreator_err_long_50' => 'Value is too long, it should be less than 50 characters',
	    'tr_melisdashboardplugincreator_err_empty' => 'The input is required and cannot be empty',
	    'tr_melisdashboardplugincreator_value_must_be_between_2_to_25' => 'The input must be between 2 and 25',
        'tr_melisdashboardplugincreator_integer_only' => 'The input must be integer only',

        'tr_melisdashboardplugincreator_save_upload_image_imageFalseType' => 'Invalid image format, please upload a valid image',
        'tr_melisdashboardplugincreator_save_upload_image_imageNotDetected' => 'Unknown image format, please upload a valid image',
        'tr_melisdashboardplugincreator_save_upload_image_imageNotReadable' => 'Image does not exists, or is not readable',
        'tr_melisdashboardplugincreator_save_upload_file_path_rights_error' => 'You do not have the rights to execute this action, please contact the administrator',
        'tr_melisdashboardplugincreator_save_upload_empty_file' => 'Please submit an image file',
        'tr_melisdashboardplugincreator_save_upload_error_encounter' => 'Error encountered while uploading the thumbnail.',
        'tr_melisdashboardplugincreator_save_upload_file' => 'File',
        'tr_melisdashboardplugincreator_err_module_exist' => '"%s" already exists, please try another one',
        'tr_melisdashboardplugincreator_err_plugin_name_exist' => '"%s" already exists for the selected module, please try another one',
        'tr_melisdashboardplugincreator_err_plugin_title_exist' => '"%s" plugin title already exists for the "%s" language of the selected module, please try another one',
        'tr_melisdashboardplugincreator_generate_plugin_error_encountered' => 'Error encountered while generating the dashboard plugin.',

	    // Steps
	    'tr_melisdashboardplugincreator_plugin' => 'Plugin',
	    'tr_melisdashboardplugincreator_menu_texts_display' => 'Menu Texts & Display',
	    'tr_melisdashboardplugincreator_dashboard_texts_display' => 'Dashboard Texts & Display',
	    'tr_melisdashboardplugincreator_summary' => 'Summary',
	    'tr_melisdashboardplugincreator_finalization' => 'Finalization',

	    //Step1 Form
	    'tr_melisdashboardplugincreator_title_step_1' => 'Plugin’s properties',
        'tr_melisdashboardplugincreator_desc_step_1' => 'Enter the name of the plugin.<br>Then choose the code’s destination, new module or existing module.',
	    'tr_melisdashboardplugincreator_dpc_plugin_name' => 'Plugin name',
	    'tr_melisdashboardplugincreator_dpc_plugin_name tooltip' => 'Enter Plugin name.',
	    'tr_melisdashboardplugincreator_dpc_plugin_type' => 'View type',
        'tr_melisdashboardplugincreator_dpc_plugin_type tooltip' => 'Select plugin type.',
        'tr_melisdashboardplugincreator_single_tab' => 'Single',
        'tr_melisdashboardplugincreator_multi_tab' => 'Multi-tabs',
        'tr_melisdashboardplugincreator_with' => 'with',
        'tr_melisdashboardplugincreator_tabs' => 'tabs',
        'tr_melisdashboardplugincreator_tab_count' => 'Tab count',
        'tr_melisdashboardplugincreator_dpc_plugin_destination' => 'Destination',
        'tr_melisdashboardplugincreator_dpc_plugin_destination tooltip' => 'Select the plugin\'s destination',
        'tr_melisdashboardplugincreator_destination_new_opt' => 'New module',
        'tr_melisdashboardplugincreator_destination_existing_opt' => 'Existing module',
        'tr_melisdashboardplugincreator_dpc_new_module_name' => 'New module name',
        'tr_melisdashboardplugincreator_dpc_new_module_name tooltip' => 'Enter the name of the module to be created',
        'tr_melisdashboardplugincreator_dpc_existing_module_name' => 'Choose a module',
        'tr_melisdashboardplugincreator_dpc_existing_module_name tooltip' => 'Select existing module',
        'tr_melisdashboardplugincreator_dpc_existing_module_placeholder' => 'Choose a module',
        
        //Step2 Form
        'tr_melisdashboardplugincreator_title_step_2' => 'Plugin’s menu translations and image',
        'tr_melisdashboardplugincreator_desc_step_2' => 'Enter the text translations in different languages, at least one language must be filled in.<br>Choose the image of your plugin that will appear in the right expandable menu.',
        'tr_melisdashboardplugincreator_dpc_plugin_title' => 'Plugin title',
        'tr_melisdashboardplugincreator_dpc_plugin_title tooltip' => 'Enter the plugin title',
        'tr_melisdashboardplugincreator_dpc_plugin_desc' => 'Plugin description',
        'tr_melisdashboardplugincreator_dpc_plugin_desc tooltip' => 'Enter the description',       
        'tr_melisdashboardplugincreator_upload_thumbnail' => 'Plugin thumbnail image (recommended 190x100)',
        'tr_melisdashboardplugincreator_upload_thumbnail tooltip' => 'Upload the plugin\'s thumbnail',
        
        //Step3 Form
        'tr_melisdashboardplugincreator_title_step_3' => 'Plugin’s dashboard translations and image',
        'tr_melisdashboardplugincreator_desc_step_3' => 'Enter the text translations in different languages, at least one language must be filled in.<br>Choose the icon of your plugin that will appear in the left top corner on the dashboard.',
        'tr_melisdashboardplugincreator_plugin_icon' => 'Plugin icon',
        'tr_melisdashboardplugincreator_plugin_icon tooltip' => 'Select the plugin icon',
        'tr_melisdashboardplugincreator_dashboard_tab_icon' => 'Plugin\'s tabs\' icons',
        'tr_melisdashboardplugincreator_dashboard_tab_icon tooltip' => 'Select the icon for each tab',
        'tr_melisdashboardplugincreator_dashboard_tab_label' => 'Plugin Tab',  
       
         //Step4 Form
        'tr_melisdashboardplugincreator_title_step_4' => 'Summary',
        'tr_melisdashboardplugincreator_desc_step_4' => 'Review your settings before creating the dashboard plugin',
        'tr_melisdashboardplugincreator_desc_step_4_dashboard_tab' => 'Plugin Tab',
        'tr_melisdashboardplugincreator_desc_step_4_dashboard_tab_icon' => 'Plugin Tab Icon',
        'tr_melisdashboardplugincreator_desc_step_4_icon' => 'Icon',
       
        //Step5 Form
        'tr_melisdashboardplugincreator_title_step_5' => 'Finalization',
        'tr_melisdashboardplugincreator_desc_step_5' => 'Tick the box below if you wish to activate the plugin upon creation.',
        'tr_melisdashboardplugincreator_activate_plugin_after_creation' => 'Activate plugin after creation',
        'tr_melisdashboardplugincreator_activate_plugin_note' => '<b>Note</b>: Activating the plugin will require to restart the platform',
        'tr_melisdashboardplugincreator_finalization_success_title' => 'The plugin has been successfully created',
        'tr_melisdashboardplugincreator_finalization_success_desc_with_counter' => 'The platform will refresh in <strong><span id="tc-restart-cd">5</span></strong>',
        'tr_melisdashboardplugincreator_finalization_success_desc' => 'You can manually activate the plugin by reloading the page',
        'tr_melisdashboardplugincreator_execute_aadtnl_setup' => 'Executing additional setup',
        'tr_melisdashboardplugincreator_please_wait' => 'Please wait',
        'tr_melisdashboardplugincreator_refreshing' => 'Refreshing'	
    ];