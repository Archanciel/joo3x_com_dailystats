<?php

require_once dirname ( __FILE__ ) . '\..\..\lib\TestCase.php';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_BASE . '\administrator\components\com_dailystats');
define('COM_DAILYSTATS_PATH', JPATH_COMPONENT_ADMINISTRATOR);

/**
 * Base class for all the com_daily_stats test classes.
 * 
 * Defining class as abstract is sementically correct and prevents MakeGood from executing
 * the test class !
 */
abstract class DailyStatsTestBase extends PHPUnit_Extensions_Database_TestCase {

	/**
	 * Sets the connection to the database
	 *
	 * @return connection
	 */
	protected function getConnection() {
// 		$dbURLPHP5_3_6_andLater = 'mysql:host=localhost;dbname=plucon15_dev;charset=UTF-8';
// 		$pdo = new PDO ( $dbURLPHP5_3_6_andLater, 'root', '' );

		$dbURLBeforePHP5_3_6 = 'mysql:host=localhost;dbname=' . self::getDatabaseName();
		$options = array (PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
		$pdo = new PDO ( $dbURLBeforePHP5_3_6, 'root', '', $options );	// no pw for root !

		return $this->createDefaultDBConnection ( $pdo, self::getDatabaseName() );
	}
	
	protected function getDatabaseName() {
		return 'joo3x_com_dailystats';	
	}

	protected function getSetUpOperation() {
		return $this->getOperations ()->INSERT ();
	}

	protected function getTearDownOperation() {
		return $this->getOperations ()->DELETE ();
	}
}

?>