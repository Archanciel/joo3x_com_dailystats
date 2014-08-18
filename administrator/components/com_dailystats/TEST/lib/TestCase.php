<?php

// We are a valid Joomla entry point.
define ( '_JEXEC', 1 );

// Once your tests run smoothly, you may comment out the next line. Without it,
// PHPUnit will fail silently, even in --verbose mode, every time a Joomla! framework
// include/require is missing ! An alternative is the PHPUnit -d display_errors=On 
// command line argument
ini_set('display_errors', 'on');

// Load PHPUnit plus DB extensions
//require_once 'PHPUnit' . DIRECTORY_SEPARATOR . 'Framework.php';
require_once 'PHPUnit' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'TestCase.php';
require_once 'PHPUnit' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'DataSet' . DIRECTORY_SEPARATOR . 'XmlDataSet.php';

// For Zend Studio 8.0 ...
//require_once 'PHPUnit\Extensions\Database\TestCase.php';
//require_once 'PHPUnit\Extensions\Database\DataSet\XmlDataSet.php';

define ( 'JPATH_BASE', dirname ( dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) ) );
define ( 'JPATH_ROOT', JPATH_BASE );
define ( 'JPATH_ADMINISTRATOR', JPATH_BASE . DIRECTORY_SEPARATOR . 'administrator' );
define ( 'JPATH_CONFIGURATION', JPATH_BASE );
define ( 'JPATH_LIBRARIES', JPATH_BASE . DIRECTORY_SEPARATOR . 'libraries' );
define ( 'JPATH_LIBRARIES_JOOMLA', JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'joomla' );
define ( 'JPATH_METHODS', JPATH_ROOT . DIRECTORY_SEPARATOR . 'methods' );

// Load the library importer, datbase + table classes and configuration
require_once (JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'import.legacy.php');
require_once (JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'legacy' . DIRECTORY_SEPARATOR . 'request' . DIRECTORY_SEPARATOR . 'request.php');
require_once (JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'version' . DIRECTORY_SEPARATOR . 'version.php');
require_once (JPATH_LIBRARIES_JOOMLA . DIRECTORY_SEPARATOR . 'object' . DIRECTORY_SEPARATOR . 'object.php');

require_once (JPATH_LIBRARIES_JOOMLA . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.php');
require_once (JPATH_LIBRARIES_JOOMLA . DIRECTORY_SEPARATOR . 'table' . DIRECTORY_SEPARATOR . 'table.php');
require_once (JPATH_CONFIGURATION . DIRECTORY_SEPARATOR . 'configuration.php');
require_once (JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'cms.php');

// Load the TestLoader
require_once dirname ( __FILE__ ) . '\TestLoader.php';

// Register specific test autloader
if (! defined ( 'LOADED_AUTOLOADER' )) {
	spl_autoload_register ( array ('TestLoader', 'load' ) );
	define ( 'LOADED_AUTOLOADER', true );
}

// Import plugin
jimport ( 'joomla.plugin.plugin' );

// Define configuration
jimport ( 'joomla.registry.registry' );

// Create the JConfig object
$config = new JConfig ();

// Get the global configuration object
$registry = JFactory::getConfig ();

// Load the configuration values into the registry
$registry->loadObject ( $config );

// initialise a session so that it is not started later after header info has been sent to Pear Printer.
// Prevents this error: session_start(): Cannot send session cookie - headers already sent by (output started at C:\xampp\php\PEAR\PHPUnit\Util\Printer.php:173)
JFactory::getSession();
?>