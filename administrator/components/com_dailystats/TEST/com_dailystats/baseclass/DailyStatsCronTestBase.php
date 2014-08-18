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
	private $daily_stats_table_name = "daily_stats_cron_test";
	
	public function setUp() {
		// emptying component log rile
		
		$f = @fopen(LOG_FILE_PATH, "r+");
		
		if ($f !== false) {
		    ftruncate($f, 0);
		    fclose($f);
		}	
			
		parent::setUp ();
	}
	
	protected function checkEntryExistInLog($message) {
		$f = fopen(LOG_FILE_PATH,'r');
		$fileSize = filesize(LOG_FILE_PATH);
		
		if ($fileSize <= 0) {
			$this->fail("Log file " . LOG_FILE_PATH . " empty. Should contain \"$message\" !");	
		}
		
		$content = fread($f, $fileSize);
		fclose($f);	// must be performed befcore the assert
		
		$this->assertEquals(1, preg_match('/' . $message . '/', $content));
	}
	
	protected function updateDailyStatRec($id, $forDate) {
		$query= "UPDATE jos_" . $this->getDailyStatsTableName() .
				" SET date = '$forDate'
				 WHERE id = $id";
		
		$con=mysqli_connect("localhost","root","",self::getDatabaseName());

		// Check connection
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		mysqli_query($con,$query);
		
		mysqli_close($con);
	}
	
	protected function getDailyStatsTableName() {
		return $this->daily_stats_table_name;
	}
	
	
	public function tearDown() {
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "TRUNCATE TABLE #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
		$db->execute();
		
		parent::tearDown();
	}
}

?>