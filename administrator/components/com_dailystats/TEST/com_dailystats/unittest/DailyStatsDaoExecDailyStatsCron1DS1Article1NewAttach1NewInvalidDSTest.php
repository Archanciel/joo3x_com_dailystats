<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation for a db in an invalid state. Article 1 had initialy
 * attachment 10001 and a valid ds rec for attachment 10001. Then, attachment 10002 was added for
 * article 1 and a ds rec was manually created, but with a total download count to date >
 * than the download count of the new attachment. This causes a SQL error and a failure of the
 * daily stats generation process. The class ensures this bug is now handled gracefully (entry
 * in log file and error mail sending in prod environment).
 *  
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCron1DS1Article1NewAttach1NewInvalidDSTest extends DailyStatsCronTestBase {
	private $daily_stats_table_name = "daily_stats_cron_test";
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in a daily stats table
	 * with 1 daily stat rec dated 1 day before cron execution.
	 */
	public function testExecDailyStatsCronFailureOneDailyStatsRecOneArticleOneDayInterval() { 
  		// update first DS rec date
		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-2 day")));
  		
  		// update new invalid DS rec date
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
  		
  		// execute cron
  		
  		try {
  			DailyStatsDao::execDailyStatsCron("#__" . $this->daily_stats_table_name,"#__attachments_cron_test","#__content_cron_test");
  		} catch (Exception $e) {
  			$this->checkEntryExistInLog("INVALID DAILY_STATS RECORD ENCOUNTERED. CRON JOB ABORTED. NO DATA INSERTED. NEEDS IMMEDIATE FIX !");
  			$this->assertContains("BIGINT UNSIGNED value is out of range", $e->getMessage());
  			return;
   		}
   		
   		$this->fail("Exception should have been thrown by dailyStatsDao::executeInsertQuery() !");
	}
	
	private function updateDailyStatRec($id, $forDate) {
		$query= "UPDATE jos_" . $this->daily_stats_table_name .
				" SET date = '$forDate'
				 WHERE id = $id";
		
		$con=mysqli_connect("localhost","root","","pluscon15_dev");

		// Check connection
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		mysqli_query($con,$query);
		
		mysqli_close($con);
	}
	
	public function setUp() {
		parent::setUp ();
	}
	
	public function tearDown() {
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "TRUNCATE TABLE #__" . $this->daily_stats_table_name; 
    	$db->setQuery($query);
		$db->query();
		
		parent::tearDown();
	}
	
	/**
	 * Gets the data set to be loaded into the database during setup.
	 * 
	 * @return xml dataset
	 */
	protected function getDataSet() {
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_1_DS_1_article_test_1_new_attach_1_new_inval_DS_data.xml' );
	}
}

?>