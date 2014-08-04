<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation for a db containing 1 published article having one attachment,
 * in a daily stats rec table containing one daily stats rec for the published article, dated at 
 * yesterday.
 *  
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronOneDailyStatsRecOneArticleTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in a daily stats table
	 * with 1 daily stat rec dated 1 day before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecOneArticleOneDayInterval() {
		// force existing daily stats rec date to yesterday
		
		$yesterday = date("Y-m-d",strtotime("-1 day"));
  		$this->updateDailyStatRec(1,$yesterday);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(2,$count,'2 daily_stats records expected, one for yesterday and one for today');

		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in a daily stats table
	 * with 1 daily stat rec dated 2 day before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecOneArticleTwoDaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$yesterday = date("Y-m-d",strtotime("-2 day"));
		$this->updateDailyStatRec(1,$yesterday);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(2,$count,'2 daily_stats records expected, one preexisting and one for today');
	
		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
	
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
	
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 2 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in a daily stats table
	 * with 1 daily stat rec dated 20 day before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecOneArticle20DaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$yesterday = date("Y-m-d",strtotime("-20 day"));
		$this->updateDailyStatRec(1,$yesterday);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(2,$count,'2 daily_stats records expected, one preexisting and and one for today');
	
		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
	
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
	
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 20 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in a daily stats table
	 * with 1 daily stat rec dated 21 day before cron execution.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecOneArticle21DaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$yesterday = date("Y-m-d",strtotime("-21 day"));
		$this->updateDailyStatRec(1,$yesterday);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(1,$count,'1 daily_stats record expected, one preexisting and none for today');
	
		$today = date("Y-m-d",strtotime("now"));
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 0 rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF 20 DAYS !");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_1_daily_stat_1_article_test_data.xml' );
	}
}

?>