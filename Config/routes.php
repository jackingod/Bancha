<?php
/**
 * Routes configuration
 *
 * This file configures Banchas routing.
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Config
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */


/**
 * Enable support for the file extension js
 *
 * In CakePHP 2.2 and above Router:setExtensions could be used,
 * for legacy support we need the bug fix version below
 */
if(Router::extensions() !== true) { // if all extensions are supported we are done

	// add json to the extensions
	$extensions = Router::extensions();
	if(!is_array($extensions)) {
		$extensions = array('json');
	} else {
		array_push($extensions, 'Json');
	}

	Router::parseExtensions($extensions);
}


/**
 * connect the remote api
 */
Router::connect('/bancha-api', array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'));
Router::connect('/bancha-api/models/:metaDataForModels', array('plugin' => 'bancha', 'controller' => 'bancha', 'action' => 'index'),array('pass'=>array('metaDataForModels')));
