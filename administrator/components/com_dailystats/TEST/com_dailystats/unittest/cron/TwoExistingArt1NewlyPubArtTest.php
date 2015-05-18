<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation for a db containing 2 published articles with one attachment 
 * each an one daily stats rec each dated at yesterday and 1 newly published article with one attachement
 * and no daily stats rec.
 *  
 * @author Jean-Pierre
 *
 */
class TwoExistingArt1NewlyPubArtTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 2 articles published in the past (id = 1, 2) and 1 newly 
	 * published article, with 1 attachment each, in a daily stats table with 1 daily stat rec for each
	 * old article dated 1 day before cron execution and no daily stats rec for the newly published
	 * article (id = 3).
	 */
	public function testExecDailyStatsCron2OldArticles1NewArticle1DayInterval() {
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

		$this->assertEquals(5,$count,'5 daily_stats records expected, 2 for yesterday and 3 for today');

		$today = date("Y-m-d",strtotime("now"));
		$today_d_m = date("d-m",strtotime("now"));
		
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

		// check daily stats for article 3
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 3 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(30,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(3,$res['date_downloads'],'date downloads');
		$this->assertEquals(3,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 1 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
		$this->checkEntryExistInLog("article_3_attach_10003.mp3: 3 downloads.\r\n\r\nTotal downloads for $today_d_m: 5.");
	}
	
	/**
	 * Tests daily stats rec generation for 2 articles published in the past (id = 1, 2) and 1 newly 
	 * published article, with 1 attachment each, in a daily stats table with 1 daily stat rec for each
	 * old article dated 2 days before cron execution and no daily stats rec for the newly published
	 * article (id = 3).
	 */
	public function testExecDailyStatsCron2OldArticles1NewArticle2DaysInterval() {
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

		$this->assertEquals(5,$count,'5 daily_stats records expected, 2 for 2 days ago and 3 for today');

		$today = date("Y-m-d",strtotime("now"));
		$today_d_m = date("d-m",strtotime("now"));
		
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

		// check daily stats for article 3

		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 3 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();

		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(30,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(3,$res['date_downloads'],'date downloads');
		$this->assertEquals(3,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 1 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 2 day\(s\)\).");
		$this->checkEntryExistInLog("article_3_attach_10003.mp3: 3 downloads.\r\n\r\nTotal downloads for $today_d_m: 5.");
	}
	
	
	/**
	 * Tests daily stats rec generation for 2 articles published in the past (id = 1, 2) and 1 newly 
	 * published article, with 1 attachment each, in a daily stats table with 1 daily stat rec for each
	 * old article dated 20 days before cron execution and no daily stats rec for the newly published
	 * article (id = 3).
	 */
	public function testExecDailyStatsCron2OldArticles1NewArticle20DaysInterval() {
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

		$this->assertEquals(5,$count,'5 daily_stats records expected, 2 for 20 days ago and 3 for today');

		$today = date("Y-m-d",strtotime("now"));
		$today_d_m = date("d-m",strtotime("now"));
		
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

		// check daily stats for article 3
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 3 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(30,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(3,$res['date_downloads'],'date downloads');
		$this->assertEquals(3,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 1 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 20 day\(s\)\).");
		$this->checkEntryExistInLog("article_3_attach_10003.mp3: 3 downloads.\r\n\r\nTotal downloads for $today_d_m: 5.");
	}
	
	/**
	 * Tests daily stats rec generation for 2 articles published in the past (id = 1, 2) and 1 newly
	 * published article, with 1 attachment each, in a daily stats table with 1 daily stat rec for each
	 * old article dated 21 days before cron execution and no daily stats rec for the newly published
	 * article (id = 3).
	 */
	public function testExecDailyStatsCron2OldArticles1NewArticle21DaysInterval() {
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

		$this->assertEquals(3,$count,'3 daily_stats records expected, two 21 days ago and one for today for the newly published article');

		$today = date("Y-m-d",strtotime("now"));
		$today_d_m = date("d-m",strtotime("now"));
		
		// check daily stats for article 3
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 3 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(30,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(3,$res['date_downloads'],'date downloads');
		$this->assertEquals(3,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 1 rows inserted for new attachment\(s\). 0 rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF 20 DAYS !");
		$this->checkEntryExistInLog("article_3_attach_10003.mp3: 3 downloads.\r\n\r\nTotal downloads for $today_d_m: 3.");
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
		parent::tearDown();
	}
	
	/**
	 * Gets the data set to be loaded into the database during setup.
	 * 
	 * @return xml dataset
	 */
	protected function getDataSet() {
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\2_old_article_1_new_article_test_data.xml' );
	}
}

?>