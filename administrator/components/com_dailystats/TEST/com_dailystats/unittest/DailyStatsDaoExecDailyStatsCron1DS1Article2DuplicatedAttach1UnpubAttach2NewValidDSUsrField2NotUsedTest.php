<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class tests daily stats generation in the following scenario: article 1 had initialy
 * attachment 10001 and two valid ds rec for attachment 10001. Then, attachment 10001 is duplicated
 * twice with id 10002 and 10003, attachment 10001 is unpublished and the attachment FK of the 
 * attachment 10001 DS is changef to 10002 (considered the main duplicated attachment). The class 
 * ensures that duplicating an attachment this way twice does not cause any unexpected problem.
 * 
 * WARNING: Here, the user_field_2 of the duplicated attachments are not used, which is not correct any
 * longer !
 *  
 * Procedure for adding 2 smaller attachments to replace (unpublish) 1 too large attachment:
 * 
 * ° duplicate the big attachment twice and change the mp3 files they point to. Ensure the display_file_name field is unique
 * ° unpublish the original attachment
 * ° update all daily_stats rec with attachment_id FK pointing to the original attachment with the id of the main new attachment
 * ° duplicate the most recent DS for the main attachment and change its attachment_id FK to the id of the secondary new attachment
 *
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCron1DS1Article2DuplicatedAttach1UnpubAttach2NewValidDSUsrField2NotUsedTest extends DailyStatsCronTestBase {
	private $daily_stats_table_name = "daily_stats_cron_test";
	
	public function testExecDailyStatsCron2NewAttach1UnpubAttach() { 
  		// update first DS rec date
		$this->updateDailyStatRec(1,date("Y-m-d",strtotime("-2 day")));
		
		// update new invalid DS rec date
		$this->updateDailyStatRec(2,date("Y-m-d",strtotime("-1 day")));
		
		// update new invalid DS rec date
		$this->updateDailyStatRec(3,date("Y-m-d",strtotime("-1 day")));
		
  		// execute cron
  		
		DailyStatsDao::execDailyStatsCron("#__" . $this->daily_stats_table_name,"#__attachments_cron_test","#__content_cron_test");
		
 		// verify results
 		
		/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->daily_stats_table_name; 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(5,$count,'5 daily_stats records expected, 3 for the past and 2 for today for article 1 which has now 3 attachments, one of which is unpublished');

		$today = date("Y-m-d",strtotime("now"));

		// check daily stats for main duplicated attachment
		
		$query = "SELECT * FROM #__" . $this->daily_stats_table_name . " WHERE attachment_id = 10002 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(130,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(5,$res['date_downloads'],'date downloads');
		$this->assertEquals(15,$res['total_downloads_to_date'],'total downloads');

		// check daily stats for secondary duplicated attachment
		
		$query = "SELECT * FROM #__" . $this->daily_stats_table_name . " WHERE attachment_id = 10003 AND date = '$today'"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(30,$res['date_hits'],'date hits');
		$this->assertEquals(130,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(3,$res['date_downloads'],'date downloads');
		$this->assertEquals(13,$res['total_downloads_to_date'],'total downloads');

		$this->checkEntryExistInLog("Daily stats for $today added in DB. 0 rows inserted for new attachment\(s\). 2 rows inserted for existing attachments \(gap filled: 1 day\(s\)\).");
	}
	
	private function updateDailyStatRec($id, $forDate) {
		$query= "UPDATE jos_" . $this->daily_stats_table_name .
				" SET date = '$forDate'
				 WHERE id = $id";
		
		$con=mysqli_connect("localhost","root","","pluscon15_dev");

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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_1_DS_1_article_test_2_dupl_attach_1_unpub_attach_1_new_valid_DS_data_usr_field2_empty.xml' );
	}
}

?>