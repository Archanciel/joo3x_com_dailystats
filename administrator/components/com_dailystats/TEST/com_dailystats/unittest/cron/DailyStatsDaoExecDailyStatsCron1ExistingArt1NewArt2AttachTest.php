<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats rec generation for 1 existing article with 1 attachment with 1 past DS
 * and 1 new article with 2 attachments. Those 2 attachments have different dowload counts.
 *  
 * @author Jean-Pierre
 *
 */
class OneExistingArt1NewArt2AttachTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 1 existing article with 1 attachment and 1 daily stat rec d
	 * ated 1 day before cron execution anc 1 new article with 2 attachments.
	 */
	public function testExecDailyStatsCron1ExistingArt1NewArt2Attach1DayInterval() {
		// force existing daily stats rec date to yesterday
		
		$pastDSDate = date("Y-m-d",strtotime("-1 day"));
  		$this->updateDailyStatRec(1,$pastDSDate);
		
  		// execute cron
  		
 		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(4,$count,'4 daily_stats records expected, 1 for yesterday 3 for today');

		// checking new DS for existing article 1
		
		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		// checking 2 new DS for new article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10002 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(20,$res['date_downloads'],'date downloads');
		$this->assertEquals(20,$res['total_downloads_to_date'],'total downloads');
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10003 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(30,$res['date_downloads'],'date downloads');
		$this->assertEquals(30,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 2 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 existing article with 1 attachment and 1 daily stat rec
	 * dated 2 day before cron execution and 1 new article with 2 attachments.
	 */
	public function testExecDailyStatsCron1ExistingArt1NewArt2Attach2DaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$pastDSDate = date("Y-m-d",strtotime("-2 day"));
		$this->updateDailyStatRec(1,$pastDSDate);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(4,$count,'4 daily_stats records expected, 1 for yesterday 3 for today');

		// checking new DS for existing article 1
		
		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		// checking 2 new DS for new article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10002 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(20,$res['date_downloads'],'date downloads');
		$this->assertEquals(20,$res['total_downloads_to_date'],'total downloads');
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10003 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(30,$res['date_downloads'],'date downloads');
		$this->assertEquals(30,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 2 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 2 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 existing article with 1 attachment and 1 daily stat rec
	 * dated 20 day before cron execution anc 1 new article with 2 attachments.
	 */
	public function testExecDailyStatsCron1ExistingArt1NewArt2Attach20DaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$pastDSDate = date("Y-m-d",strtotime("-20 day"));
		$this->updateDailyStatRec(1,$pastDSDate);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(4,$count,'4 daily_stats records expected, 1 for yesterday 3 for today');

		// checking new DS for existing article 1
		
		$today = date("Y-m-d",strtotime("now"));
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		// checking 2 new DS for new article 2
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10002 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(20,$res['date_downloads'],'date downloads');
		$this->assertEquals(20,$res['total_downloads_to_date'],'total downloads');
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 2 AND attachment_id = 10003 AND date = '$today'";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals(222,$res['date_hits'],'date hits');
		$this->assertEquals(222,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(30,$res['date_downloads'],'date downloads');
		$this->assertEquals(30,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 2 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments. GAP EXCEEDS 1 DAY \(gap filled: 20 day\(s\)\).");
	}
	
	/**
	 * Tests daily stats rec generation for 1 existing article with 1 attachment and 1 daily stat rec d
	 * ated 21 day before cron execution anc 1 new article with 2 attachments.
	 */
	public function testExecDailyStatsCron1ExistingArt1NewArt2Attach21DaysInterval() {
		// force existing daily stats rec date to yesterday
	
		$pastDSDate = date("Y-m-d",strtotime("-21 day"));
		$this->updateDailyStatRec(1,$pastDSDate);
	
		// execute cron
	
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
	
		// verify results
			
		/* @var $db JDatabase */
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName();
		$db->setQuery($query);
		$count = $db->loadResult();
	
		$this->assertEquals(3,$count,'3 daily_stats record expected, 1 preexisting and 2 for today for the new article with 2 attachnents. No DS generated for the existing article since max innterval day number is exceeded !');
	
		$today = date("Y-m-d",strtotime("now"));
		
		$this->checkEntryExistInLog("Daily stats for $today added in DB. 2 rows inserted for new attachment\(s\). 0 rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF 20 DAYS !");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\1_existing_art_1_new_art_2_attach_test_data.xml' );
	}
}

?>