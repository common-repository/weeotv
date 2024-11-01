<?php
/*
Plugin Name: WeeoTv 0.0.1
Plugin URI: http://www.insideout.io
Description: WeeoTv 0.0.1
Version: 0.0.1
Author: InSideOut10
Author URI: http://www.insideout.io
License: LGPL-v3
*/

require_once 'WordPressFramework/WordPressFramework.php';
require_once 'services/WeeoTvService.php';

add_action(
    "init",
    create_function('$arguments', "PlugInService::loadCallback('WeeoTvService', \$arguments);")
);

?>