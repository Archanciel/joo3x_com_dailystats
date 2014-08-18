<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class nsures that when executing a daily stats cron on a db containing 2 published articles
 * with one attachment each and one unpublished article with one attachment, in an empty daily stats 
 * table, 2 daily stats rec are generated.
 * 
 * @author Jean-Pierre
 *
 */
class EmptyDailyStatsTableTwoArticlesTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 2 articles with 1 attachment each and 1 unpublished article
	 * with 1 attachment in an empty daily stats table
	 */
	public function testExecDailyStatsCronEmptyDailyStatsTableTwoArticles() {
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(2,$count,'2 daily_stats records expected');
		
		$today = date("Y-m-d");
		
		// article 1
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(111,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(11,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		// article 2
		$query = "SELECT * FROM #__daily_stats_cron_test WHERE article_id = 2"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(112,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(12,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("daily_stats table successfully bootstraped. 2 rows inserted.");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\empty_dstable_2_articles_test_data.xml' );
	}
}

?>