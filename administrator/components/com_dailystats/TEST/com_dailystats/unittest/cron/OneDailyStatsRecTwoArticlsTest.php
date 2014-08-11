<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation for a db containing 2 published + 1 unpublished
 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 * for each publshed article, dated at yesterday.
 *  
 * @author Jean-Pierre
 *
 */
class OneDailyStatsRecTwoArticlesTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 2 published article and 1 unpublished article, with 1 
	 * attachment each, in a daily stats table with 1 daily stat rec for each published article dated 
	 * 1 day before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticlesOneDayInterval() {
		// force existing daily stats rec date to yesterday
		
		$yesterday = date("Y-m-d",strtotime("-1 day"));
  		$this->updateAllDailyStatRec($yesterday);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(4,$count,'4 daily_stats records expected, 2 for yesterday and 2 for today');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 2 published article and 1 unpublished article, with 1 
	 * attachment each, in a daily stats table with 1 daily stat rec for each published article dated 
	 * 2 days before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticlesTwoDaysInterval() {
		// force existing daily stats rec date to yesterday
		
		$yesterday = date("Y-m-d",strtotime("-2 day"));
  		$this->updateAllDailyStatRec($yesterday);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(4,$count,'4 daily_stats records expected, 2 for 2 days ago and 2 for today');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 2 day\(s\)\).");
	}
	
	
	/**
	 * Tests daily stats rec generation for 2 published article and 1 unpublished article, with 1 
	 * attachment each, in a daily stats table with 1 daily stat rec for each published article dated 
	 * 20 days before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticles20DaysInterval() {
		// force existing daily stats rec date to yesterday
		
		$yesterday = date("Y-m-d",strtotime("-20 day"));
  		$this->updateAllDailyStatRec($yesterday);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(4,$count,'4 daily_stats records expected, 2 for 20 days ago and 2 for today');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 20 day\(s\)\).");
	}
	
	
	/**
	 * Tests daily stats rec generation for 2 published article and 1 unpublished article, with 1 
	 * attachment each, in a daily stats table with 1 daily stat rec for each published article dated 
	 * 21 days before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticles21DaysInterval() {
		// force existing daily stats rec date to yesterday
		
		$yesterday = date("Y-m-d",strtotime("-21 day"));
  		$this->updateAllDailyStatRec($yesterday);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(2,$count,'2 daily_stats records expected, two for 21 days ago and none for today');

		$today = date("Y-m-d",strtotime("now"));

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 0 rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF 20 DAYS !");
	}
	
	private function updateAllDailyStatRec($forDate) {
		$query= "UPDATE jos_" . $this->getDailyStatsTableName() .
				" SET date = '$forDate'";
		
		$con=mysqli_connect("localhost","root","",self::getDatabaseName());

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
		$query = "TRUNCATE TABLE #__" . $this->getDailyStatsTableName(); 
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\1_daily_stat_2_article_test_data.xml' );
	}
}

?>