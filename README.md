# Melis Dashboard Plugin Creator

Generates a ready-to-use dashboard plugin, complete with source code and necessary configuration. This will aid the developers, especially the new developers of the platform, to swiftly create a plugin with or without the ample knowledge of the plugin's technicalities.

## Getting started

These instructions will get you a copy of the project up and running on your machine.

### Prerequisites

The following modules need to be installed to run the Melis Link Checker module:

- Melis Core
- Melis Tool Creator

### Installing

Run the composer command:

```
composer require melisplatform/melis-dashboard-plugin-creator
```

### Database

No database is needed for this tool


## Tools and elements provided

- Dashboard Plugin Creator Tool
- Dashboard Plugin Creator Service

### Dashboard Plugin Creator Tool

  - user may opt to create a single or multi-tab dashboard plugin and must specify the destination module(new or existing) for the generated plugin
  - after generation, the source code can be found inside the destination module ready to be updated based on the project's requirements
  - the generated plugin by default, will be shown under the 'Others' section in the Dashboard Plugins menu 

### Dashboard Plugin Creator Service

```
File: 
      - /melis-dashboard-plugin-creator/src/Service/MelisDashboardPluginCreatorService.php
    
```

- MelisDashboardPluginCreatorService
    - This service's main function is to generate a dashboard plugin using the parameters saved in the current session. 
      
    ```     
     $dashboardPluginService = $this->getServiceManager()->get('MelisDashboardPluginCreatorService');
     $result = $dashboardPluginService->generateDashboardPlugin();
    ```    

## Authors

- **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-newsletter/contributors) who participated in this project.

## License

This project is licensed under the Melis Technology premium versions end user license agreement (EULA) - see the [LICENSE.md](LICENSE.md) file for details
