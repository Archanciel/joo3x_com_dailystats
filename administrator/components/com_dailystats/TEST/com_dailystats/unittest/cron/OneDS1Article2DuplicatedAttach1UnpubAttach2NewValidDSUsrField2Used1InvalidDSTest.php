<?php

require_once dirname ( __FILE__ ) . '..\..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation in the following scenario: article 1 had initialy
 * attachment 10001 and two valid ds rec for attachment 10001. Then, attachment 10001 is duplicated
 * twice with id 10002 and 10003, attachment 10001 is unpublished and the attachment FK of the 
 * attachment 10001 DS is changef to 10002 (considered the main duplicated attachment). The class 
 * ensures that duplicating an attachment this way twice does not cause any unexpected problem.
 * 
 * Procedure for adding 2 smaller attachments to replace (unpublish) 1 too large attachment:
 * 
 * ° duplicate the big attachment twice and change the mp3 files they point to. Ensure the display_file_name field is unique
 * ° set user_field_2 of the secondary attachment to 1, which means that no DS will be generated for the secondary attachment
 * ° unpublish the original attachment
 * ° update all daily_stats rec with attachment_id FK pointing to the original attachment with the id of the main new attachment
 *
 * Here, the DS table has one invalid DS for the secondary attachment. Normally, this DS should not exist or should have been deleted.
 * This test ensures that no new DS is generated for the secondary attachment.
 * @author Jean-Pierre
 *
 */
class OneDS1Article2DuplicatedAttach1UnpubAttach2NewValidDSUsrField2Used1InvalidDSTest extends DailyStatsCronTestBase {
	public function testExecDailyStatsCron2NewAttach1UnpubAttach() { 
  		// update first DS rec date
		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-2 day")));
		
		// update new invalid DS rec date
		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
		
		// update new invalid DS rec date
		$this->updateDailyStatRec(3,date("Y-m-d",strtotime("-1 day")));
		
  		// execute cron
  		
		DailyStatsDao::execDailyStatsCron("#__" . $this->getDailyStatsTableName(),"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->getDailyStatsTableName(); 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(4,$count,'4 daily_stats records expected, 3 for the past (for the new main attachment and 1 invalid for the new secondary attach) and 1 for today for article 1 which has now 3 attachments, 1 unpublished, 1 main (user_field_2 empty) and 1 secondary (user_field_2 == 1)');
    	
		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for main duplicated attachment
		
		$query = "SELECT * FROM #__" . $this->getDailyStatsTableName() . " WHERE attachment_id = 10002 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(130,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(5,$res['date_downloads'],'date downloads');
		$this->assertEquals(15,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 1 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\..\data\cron\1_DS_1_article_test_2_dupl_attach_1_unpub_attach_1_new_valid_DS_data_usr_field2_used_1_inval_DS_for_sec_atta.xml' );
	}
}

?>