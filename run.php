<?php
/**
 * @package     Lfm
 * @author      HiNa <hina@bouhime.com>
 * @copyright   Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 * @license     https://github.com/fetus-hina/nowplaying/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
 * @link        https://github.com/fetus-hina/nowplaying
 */
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');
set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());

require_once('Zend/Loader/Autoloader.php');
$autoloader =
	Zend_Loader_Autoloader::getInstance()
		->unregisterNamespace(array('Zend_', 'ZendX_'))
        ->setFallbackAutoloader(true);

Lfm_Controller::run();
