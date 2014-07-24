<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests running the daily stats cron on a DB containing one article with no
 * attachent and one article with one attachment, but in an unpublished state. It ensures
 * that no attachment stats are generated.
 * 
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronNoDailystatsGeneratedTest extends DailyStatsCronTestBase {
	private $daily_stats_table_name = "daily_stats_cron_test";
	
	/**
	 * Tests daily stats cron against a DB containing 1 article with no attachment and
	 * 1 unpublished article with 1 attachment.
	 */
	public function testExecDailyStatsCronForArticleWithNoAttachmentOrUnpublishedArticle() {
		DailyStatsDao::execDailyStatsCron("#__" . $this->daily_stats_table_name,"#__attachments_cron_test","#__content_cron_test");

     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->daily_stats_table_name; 
    	$db->setQuery($query);
    	$count = $db->loadResult();
		
    	$this->assertEquals(0,$count,'0 daily_stats records expected');
		
		$this->checkEntryExistInLog("daily_stats table successfully bootstraped. 0 rows inserted");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_test_data_noAttachments.xml' );
	}
}

?>