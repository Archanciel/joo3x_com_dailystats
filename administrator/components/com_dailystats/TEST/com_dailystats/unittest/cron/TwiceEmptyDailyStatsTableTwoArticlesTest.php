<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class ensures that when executing a daily stats cron for the second time on the same day
 * on a db containing 2 published articles with one attachment each and one unpublished article with 
 * one attachment, in an empty daily stats table, no daily stats rec are generated.
 * 
 * @author Jean-Pierre
 *
 */
class TwiceEmptyDailyStatsTableTwoArticlesTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 2 articles with 1 attachment each and 1 unpublished article
	 * with 1 attachment in an empty daily stats table
	 */
	public function testExecDailyStatsCronTwiceEmptyDailyStatsTableTwoArticles() {
		// first cron
		
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(2,$count,'2 daily_stats records expected');
		
		$today = date("Y-m-d");

		// second cron

		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		$db->setQuery($query);
		$count = $db->loadResult();
		
		$this->assertEquals(2,$count,'2 daily_stats records expected');
		
		$this->checkEntryExistInLog("daily_stats table successfully bootstraped. 2 rows inserted.");
		$this->checkEntryExistInLog("Daily stats for today already exist in daily_stats table. No data inserted.");
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