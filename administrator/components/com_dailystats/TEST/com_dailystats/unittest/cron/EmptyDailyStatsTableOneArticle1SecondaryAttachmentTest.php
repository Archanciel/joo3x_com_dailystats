<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class nsures that when executing a daily stats cron on a db containing 1 published article
 * with one main attachment and one secondary attachment, in an empty daily stats table, only 1 daily 
 * stats rec is generated for the main attachment only.
 * 
 * @author Jean-Pierre
 *
 */
class EmptyDailyStatsTableOneArticle1SecondaryAttachmentTest extends DailyStatsCronTestBase {
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in an empty daily stats 
	 * table
	 */
	public function testExecDailyStatsCronEmptyDailyStatsTableOneArticle1MainAtt1SecAtt() {
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(1,$count,'1 daily_stats records expected (1 for the main attachment, 0 for the secondary attachment');
		
		$today = date("Y-m-d");
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(111,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(11,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("daily_stats table successfully bootstraped. 1 rows inserted.");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\empty_dstable_1_article_2_attach_test_data.xml' );
	}
}

?>