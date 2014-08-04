<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation for a db containing 2 published + 1 unpublished
 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 * for each published article. Those daily stats rec are not for the same date: 1 rec is one day
 * ahead of the other rec, for example dated at yesterday for article 2, and 2 days ago for article 1.
 *  
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronOneDailyStatsRecAhead1DayTwoArticlsTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats generation for a db containing 2 published + 1 unpublished
 	 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 	 * for each published article. Daily stats rec for article 2 is dated at yesterday
 	 * and ds rec for article 1 is dated 2 days ago.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticlesOneDayInterval() {
		// set first daily stats 2 days before and second daily stats 1 day before.
		// So, the daily stats table is now in an incoherent situation
  		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-2 day")));
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
  		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(3,$count,'3 daily_stats records expected, 2 for the past and only one for today for article 2. Article one will no longer get daily stats records since its DS are left behind !');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertNull($res,'no daily stats expected for article 1');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats generation for a db containing 2 published + 1 unpublished
 	 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 	 * for each published article. Daily stats rec for article 2 is dated at yesterday
 	 * and ds rec for article 1 is dated 3 days ago.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticlesTwoDayInterval() {
		// set first daily stats 3 days before and second daily stats 1 day before.
		// So, the daily stats table is now in an incoherent situation
  		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-3 day")));
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
  		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(3,$count,'3 daily_stats records expected, 2 for the past and only one for today for article 2. Article one will no longer get daily stats records');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertNull($res,'no daily stats expected for article 1');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
		
	/**
	 * Tests daily stats generation for a db containing 2 published + 1 unpublished
 	 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 	 * for each published article. Daily stats rec for article 2 is dated at yesterday
 	 * and ds rec for article 1 is dated 20 days ago.
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticles19DayInterval() {
		// set first daily stats 20 days before and second daily stats 1 day before.
		// So, the daily stats table is now in an incoherent situation
  		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-20 day")));
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
  		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(3,$count,'3 daily_stats records expected, 2 for the past and only one for today for article 2. Article one will no longer get daily stats records');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertNull($res,'no daily stats expected for article 1');

		// check daily stats for article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(2,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}	
	
	/**
	 * Tests daily stats generation for a db containing 2 published + 1 unpublished
 	 * article, each having one attachment, in a daily stats rec table containing one daily stats rec
 	 * for each published article. Daily stats rec for article 2 is dated at yesterday
 	 * and ds rec for article 1 is dated 21 days ago.
 	 * 
 	 * DS rec interval will keep growing for ever !!!
	 */
	public function testExecDailyStatsCronOneDailyStatsRecTwoArticles20DaysInterval() {
		// set first daily stats 20 days before and second daily stats 1 day before.
		// So, the daily stats table is now in an incoherent situation
  		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-21 day")));
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
				
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");

 		// verify results
 			
 		/* @var $db JDatabase */
 		$db = JFactory::getDBO();
 		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
 		$db->setQuery($query);
 		$count = $db->loadResult();
 		
 		$this->assertEquals(3,$count,'3 daily_stats records expected, 2 for the past and only one for today for article 2. Article one will no longer get daily stats records');
 		
 		$today = date("Y-m-d",strtotime("now"));
 		
 		// check daily stats for article 1
 		
 		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'";
 		$db->setQuery($query);
 		$res = $db->loadAssoc();
 		
 		$this->assertNull($res,'no daily stats expected for article 1');
 		
 				// check daily stats for article 2
 		
 		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND date = '$today'";
 		$db->setQuery($query);
 		$res = $db->loadAssoc();
 		
 		$this->assertEquals(2,$res['date_hits'],'date hits');
 		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
 		$this->assertEquals(1,$res['date_downloads'],'date downloads');
 		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');
 		
 		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_1_daily_stat_1_day_ahead_2_article_test_data.xml' );
	}
}

?>