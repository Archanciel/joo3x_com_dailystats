<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_BASE . '\administrator\components\com_dailystats');
define('COM_DAILYSTATS_PATH', JPATH_COMPONENT_ADMINISTRATOR);
define('LOG_FILE_PATH', JPATH_BASE . "\logs\com_dailystats_log.php");
define('PHPUNIT_EXECUTION', 'PHPUNIT_MODE');

/**
 * Base class for the com_daily_stats CRON operation test classes.
 * 
 * Defining class as abstract is sementically correct and prevents MakeGood from executing
 * the test class !
 */
abstract class DailyStatsCronTestBase extends DailyStatsTestBase {
	
	public function setUp() {
		// emptying component log rile
		
		$f = @fopen(LOG_FILE_PATH, "r+");
		
		if ($f !== false) {
		    ftruncate($f, 0);
		    fclose($f);
		}	
			
		parent::setUp ();
	}
	
	protected function checkEntryExistInLog($entry) {
		$f = fopen(LOG_FILE_PATH,'r');
		$content = fread($f, filesize(LOG_FILE_PATH));
		fclose($f);	// must be performed befcore the assert
		
		$this->assertEquals(1, preg_match('/' . $entry . '/', $content));
	}
}

?>