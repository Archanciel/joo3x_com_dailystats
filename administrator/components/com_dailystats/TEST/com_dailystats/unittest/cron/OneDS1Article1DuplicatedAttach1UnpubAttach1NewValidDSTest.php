<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation in the following scenario: article 1 had initialy
 * attachment 10001 and two valid ds rec for attachment 10001. Then, attachment 10001 is duplicated
 * with id 10002, attachment 10001 is unpublished and the attachment FK of the attachment 10001 DS
 * is changef to 10002. The class ensures that duplicating an attachment this way does not create 
 * any unexpected problem.
 *  
 * @author Jean-Pierre
 *
 */
class OneDS1Article1DuplicatedAttach1UnpubAttach1NewValidDSTest extends DailyStatsCronTestBase {
	public function testExecDailyStatsCron1NewAttach1UnpubAttach() { 
  		// update first DS rec date
		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-2 day")));
  		
  		// update new invalid DS rec date
  		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
  		
  		// execute cron
  		
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(3,$count,'3 daily_stats records expected, 2 for the past and only 1 for today for article 1 which has now 2 attachments, one of which is unpublished');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for article 1
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE article_id = 1 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(11,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(1,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');

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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\1_DS_1_article_test_1_dupl_attach_1_unpub_attach_1_new_valid_DS_data.xml' );
	}
}

?>