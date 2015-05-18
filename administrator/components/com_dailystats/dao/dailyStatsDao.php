<?php 
/********************************************************************
 Product    : Daily Stats
Date       : March 2013
Copyright  : jps.dev
Contact    : http://jps.dev
Licence    : GNU General Public License
Description: displays article and attached audio file access and usage
daily stats on a chart
Based on   : SimplePlot from Les Arbres Design
(http://extensions.lesarbresdesign.info)
*********************************************************************/

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/dailyStatsConstants.php';

class DailyStatsDao {
     
     /**
      * This method is called when the following request is made on plusconscient:
      * http://localhost/plusconscient15_dev/index.php?option=com_dailystats&cron=yes. 
      * The request is performed daily by a cron job.
      * 
      * The method inserts daily stats in the jox_daily_stats table.
	  * 
	  * @param String $dailyStatsTableName. Only supplied when unit testing the method since
	  *                                     we need to test it against an empty daily_stats table !
	  * @param unknown_type $attachmentsTableName. Only supplied when unit testing.
	  * @param unknown_type $contentTableName. Only supplied when unit testing.
	  */
     public static function execDailyStatsCron($dailyStatsTableName,$attachmentsTableName,$contentTableName) {
		jimport('joomla.error.log');
		define(MAX_DAY_INTERVAL, 20);
		
		if (!isset($dailyStatsTableName)) {
			$dailyStatsTableName = "#__daily_stats";
			$attachmentsTableName = "#__attachments";
			$contentTableName = "#__content";
		}
     	
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) 
				  FROM $dailyStatsTableName;";
    	
    	$count = self::loadResult($db, $query);
    	
    	if ($count > 0) {
    		// daily_stats table not empty
    		$query = "SELECT DATE_FORMAT(MAX(date),'%Y-%m-%d') 
    				  FROM $dailyStatsTableName;";
    		$maxDate = self::loadResult($db, $query);
    		$today = date("Y-m-d");
    		
    		if (strcmp($maxDate,$today) == 0) {
    			// protecting for duplicate insertion of daily stats data
    			
    			$mailSubject = 'Dailystats Cron LAUNCHED AGAIN ON SAME DAY';
				$message = "Daily stats for today already exist in daily_stats table. No data inserted.";
				self::logAndMail($mailSubject, $message);
    			return;
    		}
    		
    		// inserting daily_stats for existing attachments
    		
    		$gap = 1;	// used to handle the case where cron execution was skipped the day(S) before 
    		$rowsNumberForExistingAttachments = 0;
    		
    		while ( $rowsNumberForExistingAttachments == 0	&&
    				$gap <= MAX_DAY_INTERVAL) {
    			$dailyStatsQuery = "INSERT INTO $dailyStatsTableName 
      									(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads) 
									SELECT T2.id AS article_id, T1.id as attachment_id, CURRENT_DATE, T2.hits, T2.hits - T3.total_hits_to_date, T1.download_count,  T1.download_count - T3.total_downloads_to_date
									FROM $attachmentsTableName T1, $contentTableName T2, $dailyStatsTableName T3
									WHERE T1.state = 1 AND T1.user_field_2 != 1 AND T1.parent_id = T2.id AND T2.id = T3.article_id AND T1.id = T3.attachment_id AND DATE_SUB(CURRENT_DATE,INTERVAL $gap DAY) = T3.date;";
	    		
		    	$rowsNumberForExistingAttachments = self::executeInsertQuery($db, $dailyStatsQuery, $log);
		    	
		    	if ($rowsNumberForExistingAttachments == 0) {
			    	$gap++;
		    	}
    		}
    		
    		// inserting daily_stats for new attachments
    		
    		$query = "INSERT INTO $dailyStatsTableName
			    		(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads)
				    		SELECT T1.parent_id, T1.id, CURRENT_DATE, T2.hits, T2.hits, T1.download_count, T1.download_count
				    		FROM $attachmentsTableName T1, $contentTableName T2
				    		WHERE T1.parent_id = T2.id AND T2.state = 1 AND T1.id IN (
					    		SELECT T1.id
					    		FROM $attachmentsTableName T1 LEFT JOIN $dailyStatsTableName ON T1.id = $dailyStatsTableName" . ".attachment_id
					    		WHERE T1.state = 1 AND T1.user_field_2 != 1 AND $dailyStatsTableName" . ".attachment_id IS NULL);";
    		$rowsNumberForNewAttachments = self::executeInsertQuery($db, $query, $log);
    		$newAttachmentsDownloadCountInfo = '';
    		
    		// if new attachments were inserted in DB, obtain the download count of each new attachment in order to log this
    		// information. This will alert of any problem preventing successful download of the attachment. 
    		if ($rowsNumberForNewAttachments > 0) {
    			$newAttachmentsDownloadCountInfo = self::retrieveDownloadCountForNewAttachments($rowsNumberForNewAttachments, $dailyStatsTableName, $attachmentsTableName);
       		}
    		
    		if ($gap > MAX_DAY_INTERVAL) {
    			$message = "Daily stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF " . MAX_DAY_INTERVAL . " DAYS !";
    			$mailSubject = 'Dailystats Cron ERROR';
    		} else if ($gap > 1) {
   				$message = "Daily stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments. GAP EXCEEDS 1 DAY (gap filled: $gap day(s)). ";
   				$mailSubject = "Dailystats Cron completed with gap > 1 day. New $rowsNumberForNewAttachments. Existing $rowsNumberForExistingAttachments.";
    		} else {
    			$message = "Daily stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments (gap filled: $gap day(s)). ";
    			$mailSubject = "Dailystats Cron completed. New $rowsNumberForNewAttachments. Existing $rowsNumberForExistingAttachments.";
    		}
    	} else {
       		// daily_stats table is empty and must be bootstraped
       		$query= "INSERT INTO $dailyStatsTableName 
         				(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads)
					SELECT T1.parent_id, T1.id, CURRENT_DATE, T2.hits, T2.hits, T1.download_count, T1.download_count
					FROM $attachmentsTableName T1, $contentTableName T2
					WHERE T1.state = 1 AND T1.user_field_2 != 1 AND T1.parent_id = T2.id AND T2.state = 1;";
	    	$rowsNumber = self::executeInsertQuery($db, $query, $log);

     		if ($rowsNumber > 0) {
    			$newAttachmentsDownloadCountInfo = self::retrieveDownloadCountForNewAttachments($rowsNumber, $dailyStatsTableName, $attachmentsTableName);
       		}
	    	
 //    		self::executeQuery ( $db, "UPDATE $dailyStatsTableName SET date=DATE_SUB(date,INTERVAL 1 DAY);" ); only for creating test data !!
	    	
    		$mailSubject = "Dailystats Cron completed. New $rowsNumber. Existing 0.";
			$message = "daily_stats table successfully bootstraped. $rowsNumber rows inserted.";
    	}
    	
    	$lastTotalDownloadCountInfo = self::retrieveLastTotalDownloadCountInfo($dailyStatsTableName,$contentTableName);
    	 
//    	self::logAndMail($mailSubject,$message,$downloadCountString);
    	self::logAndMail($mailSubject,$message,$newAttachmentsDownloadCountInfo . $lastTotalDownloadCountInfo);
     }

     private static function retrieveLastTotalDownloadCountInfo($dailyStatsTableName,$contentTableName) {
     	$lastDownloadCountString = "\r\n\r\n";
     	 
     	$array = self::getLastAndTotalHitsAndDownloadsArrForAllCategories($dailyStatsTableName,$contentTableName);
     	$date = $array[DATE_IDX];
     	$dateDownloadCount = $array[LAST_DOWNLOADS_IDX];
     	$lastDownloadCountString .= "Total downloads for $date: $dateDownloadCount.\r\n";
     
     	return $lastDownloadCountString;
     }
      
     /**
      * Obtain the download count of each new attachment.
      * 
	  * @param String $dailyStatsTableName
      * @param int $newAttachmentsNumber
      * @return string 'attachment fileName' 'download count'
      */
     private static function retrieveDownloadCountForNewAttachments($newAttachmentsNumber, $dailyStatsTableName, $attachmentsTableName) {
     	$query =   "SELECT a.filename, d.date_downloads
					FROM $dailyStatsTableName d, $attachmentsTableName a
					WHERE d.attachment_id = a.id
					ORDER BY d.id DESC
					LIMIT $newAttachmentsNumber";

     	$rows = self::executeQuery($query);
     	
     	$retStr = "\r\n\r\n";
     	
     	foreach ($rows as $row) {
     		$retStr .= $row->filename . ": " . $row->date_downloads . " downloads.";
     	}

     	return $retStr;
     }
     
	 private static function executeInsertQuery(JDatabaseDriver $db, $query, $log) {
		$db->setQuery ( $query );
		
		try {
			$db->execute();
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
			//print_r( $e );
			$message = "INVALID DAILY_STATS RECORD ENCOUNTERED. CRON JOB ABORTED. NO DATA INSERTED. NEEDS IMMEDIATE FIX !\r\nERROR MSG FOLLOWS:\r\n$errorMsg\r\n";
			self::logAndMail('Dailystats Cron ERROR',$message);
				
			// throwing an exception instead of using JError::raiseError() makes it possible to
			// unit test the caae causing the exception. In the browser, this simply results in
			// a regular PHP orange error page which displays much more useful infos than JError does !
			// And anyway, we are in a CRON triggered action. No user would see such a page ! And
			// anyway, JError is deprecated in Joomla 3 !
			//			JError::raiseError ( 500, $errorMsg );
			
			throw new Exception($errorMsg);
		}
		
		return $db->getAffectedRows();
	 }
	 
	 /**
      * This method is used to test mail sending. It is called when the following request is 
      * made on plusconscient:
      * http://localhost/plusconscient15_dev/index.php?option=com_dailystats&cron=mail. 
	  */
	 public static function testLogAndMail() {
		$mailSubject = 'Daily stats EMAIL TEST';
		$rowsNumberForNewAttachments = 9;
		$rowsNumberForExistingAttachments = 99;
		$gap = 11;
		$message = "Daily stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments (gap filled: $gap day(s)). ";
		
		$retStr = "\r\n\r\n";
		$retStr .= "filename_one.mp3" . ": " . "11" . " downloads.\r\n";
		$retStr .= "filename_two.mp3" . ": " . "222" . " downloads.\r\n";
		
	 	self::logAndMail($mailSubject, $message, $retStr);
	 }
	 
	private static function logAndMail($subject, $message, $downloadCountString = '') {
		JLog::addLogger ( array (
				// Sets log file name
				'text_file' => 'com_dailystats_log.php',
				// Sets the format of each line
            	'text_entry_format' => '{DATETIME} {MESSAGE}' ), 
				// Sets messages of all log levels to be sent to the file
				JLog::ALL, 
				// The log category/categories which should be recorded in this file
				// In this case, it's just the one category from our extension, still
				// we need to put it inside an array
				array (
						'com_dailystats' ) );
		
		JLog::add ( $message, JLog::INFO, 'com_dailystats' );
		
		if (defined ( 'PHPUNIT_EXECUTION' )) {
			JLog::add ( $downloadCountString, JLog::INFO, 'com_dailystats' );
			return;
		}
		
		// fetch the site's email address and name from the global configuration. These are set in the
		// administration back-end (Global Configuration -> Server -> Mail Settings)
		
		/* @var $mailThis JFactory */
		$config = JFactory::getConfig ();
		$adminMail = array (
				$config->getValue ( 'config.mailfrom' ),
				$config->getValue ( 'config.fromname' ) 
		);
		
		/* @var $mailThis JMail */
		$mailThis = JFactory::getMailer ();
		$mailThis->setSender ( $adminMail );
		// $mailThis->addRecipient($adminMail); // Joomla 3
		$mailThis->addRecipient ( $adminMail [0] ); // Joomla 1.5
		$mailThis->setSubject ( $subject );
		
		if (isset ( $downloadCountString ) && strlen ( $downloadCountString ) > 1) {
			$mailThis->setBody ( $message . $downloadCountString );
		} else {
			$mailThis->setBody ( $message );
		}
		
		$mailThis->Send ();
	}

     private static function loadResult(JDatabaseDriver $db, $query) {
    	$db->setQuery($query);
    	
    	try {
    		$res = $db->loadResult();
   		} catch(Exception $e) {
   			$errorMsg = $e->getMessage();
   			$message = "Query \"$query\" raised an exception ($errorMsg). CRON JOB ABORTED. NO DATA INSERTED. NEEDS IMMEDIATE FIX !\r\nERROR MSG FOLLOWS:\r\n$errorMsg\r\n";
   			self::logAndMail('Dailystats Cron ERROR',$message);
			throw new DatabaseException($errorMsg);
       	}
     	
    	return $res;
     }
	
	/**
	 * Explaining the MAX($yValName) in the CHART_MODE_ARTICLE query below:
	 * 
	 * Without it, for an article with more than one attachments, the query returns
	 * 
	 * 	     downl hits 
	 * 23-04-2013 	0 	0
	 * 24-04-2013 	1 	33
	 * 24-04-2013 	0 	0
	 * 24-04-2013 	0 	0
	 * 25-04-2013 	11 	36
	 * 25-04-2013 	0 	36
	 * 25-04-2013 	0 	36
	 * 
	 * which results in ploting a 0 instead of a 33 value for 24-04-2013 !
	 * 
	 * with the MAX($yValName) and GRUUP BY date addition, the result is
	 * 
	 * 23-04-2013 	0 	0
	 * 24-04-2013 	1 	33
	 * 25-04-2013 	11 	36
	 * 
	 * @param unknown_type $articleId
	 * @param unknown_type $categoryId
	 * @param unknown_type $yValName	either date_hits or date_downloads
	 * @param unknown_type $chartMode
	 * @return string
	 */
	public static function buildPlotDataQuery($articleId, $categoryId, $yValName, $chartMode) {
		switch ($chartMode) {
			case CHART_MODE_ARTICLE:
				$qu =  "SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.{$yValName}
				FROM (
					SELECT date, MAX($yValName) as $yValName
					FROM #__daily_stats
					WHERE article_id = $articleId
					GROUP BY date
					ORDER BY date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			case CHART_MODE_CATEGORY:
				$qu =	"SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.sum AS {$yValName}
				FROM (
					SELECT s.date, SUM(s.{$yValName}) AS sum
					FROM #__daily_stats AS s, #__content as c
					WHERE s.article_id = c.id AND c.sectionid = $categoryId
					GROUP BY s.date
					ORDER BY s.date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			case CHART_MODE_CATEGORY_ALL:
				if(version_compare(JVERSION,'1.6.0','ge')) {
					$excludedCategories = EXCLUDED_J16_CATEGORIES_SET;
				} else {
					$excludedCategories = EXCLUDED_J15_SECTIONS_SET;
				}

				// plotting total site (all categries activity
				$qu =	"SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.sum AS {$yValName}
				FROM (
					SELECT s.date, SUM(s.{$yValName}) AS sum
					FROM #__daily_stats AS s, #__content as c
					WHERE s.article_id = c.id 
					AND c.sectionid NOT IN ($excludedCategories)
					GROUP BY s.date
					ORDER BY s.date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			default:
				return '';
				break;
		}
	}
	
	/**
	 * Returns the list of top level content categories (Joonla 1.6 +) 
	 * or sections (Joomla 1.5)
	 */
	public static function getCategoriesOrSections() {
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$query = self::getCategoryQuery();
		} else {	// Joomla 1.5
			$query = self::getSectionQuery();
		}
		
		return self::executeQuery($query);
	}
	
	private static function executeQuery($query) {
		$db	= JFactory::getDBO();
		$db->setQuery($query);
		
		return $db->loadObjectList();
	}
	
	/**
	 * Adapted to Joomla 1.6 +
	 * 
	 * @return string
	 */
	private static function getCategoryQuery() {
		return "SELECT id,title FROM #__categories WHERE extension LIKE 'com_content' AND level = " . J16_SECTION_LEVEL . " AND id NOT IN (" . EXCLUDED_J16_CATEGORIES_SET . ") ORDER BY title";
	}
	
	/**
	 * Adapted to Joomla 1.5
	 * 
	 * @return string
	 */
	private static function getSectionQuery() {
		return "SELECT id,title FROM #__sections WHERE scope LIKE 'content' AND id NOT IN (" . EXCLUDED_J15_SECTIONS_SET . ") ORDER BY title";
	}
	
	/**
	 * Returns the list of articles for the passed cat/section)
	 */
	public static function getArticlesForCatSec($categorySectionId) {
		$query = self::getArticleQuery($categorySectionId);
	
		return self::executeQuery($query);
	}
	
	/**
	 *
	 * @param int $categorySectionId
	 * @return string
	 */
	private static function getArticleQuery($categorySectionId) {
		return "SELECT id, title, DATE_FORMAT(created,'%a %D, %M %Y') as creation_date
				FROM #__content WHERE sectionid = $categorySectionId
				ORDER BY title";
	}
	
	/**
	 * Returns the list of articles for the passed cat/section)
	 */
	public static function getMostRecentArticles($articleNumber) {
		$query = self::getMostRecentArticlesQuery($articleNumber);
	
		return self::executeQuery($query);
	}
	
	/**
	 *
	 * @param int $articleNumber
	 * @return string
	 */
	private static function getMostRecentArticlesQuery($articleNumber) {
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$excludedCategories = EXCLUDED_J16_CATEGORIES_SET;
		} else {
			$excludedCategories = EXCLUDED_J15_SECTIONS_SET;
		}

		$qu =  "SELECT DISTINCT c.id, c.title, DATE_FORMAT(c.created,'%a %D, %M %Y') as creation_date
				FROM #__daily_stats AS s, #__content as c
				WHERE s.article_id = c.id
				AND c.sectionid NOT IN ($excludedCategories)		
				ORDER BY c.created DESC
				LIMIT $articleNumber";

		return $qu;
	}
	
	public static function getLastAndTotalHitsAndDownloadsArr($chartMode, $id = NULL) {
		switch ($chartMode) {
			case CHART_MODE_ARTICLE:
				$qu = self::getLastAndTotalHitsAndDownloadsForArticleQuery($id);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			case CHART_MODE_CATEGORY:
				$qu = self::getLastAndTotalHitsAndDownloadsForCategoryQuery($id);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			case CHART_MODE_CATEGORY_ALL:
				$ret = self::getLastAndTotalHitsAndDownloadsArrForAllCategories();
				break;
			default:
				break;
		}

		return $ret;
	}

	/**
	 * Returns date hits and downloads plus total hits and downloads to date in an array.
	 * 
	 * @return array
	 */
	private static function getLastAndTotalHitsAndDownloadsArrForAllCategories($dailyStatsTableName,$contentTableName) {
		$qu = self::getLastAndTotalHitsAndDownloadsForAllCategoriesQuery($dailyStatsTableName,$contentTableName);
		$rows = self::executeQuery($qu);
		
		$ret[DATE_IDX] = $rows[0]->displ_date;
		$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
		$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
		$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
		$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;

		return $ret;
	}
	
	/*
	 * Initial query:
	 * 
	 * SELECT DATE_FORMAT(date,'%d-%m') as displ_date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
	 * 		FROM jos_daily_stats
	 * 		WHERE article_id = 502
	 * 		AND date = (
	 * 				SELECT MAX(date)
	 * 				FROM jos_daily_stats t
	 * 				WHERE article_id = t.article_id
	 * 			)
	 * 
	 * Res:
	 * 		
	 * date 		d_hits 	tot_hits_td d_downl  tot_downl_td
	 * 26-04 		  37		106 	0 			1
	 * 26-04 		  37 		106 	0 			1
	 * 26-04 		  37 		106 	5 			17		
	 * 
	 * 
	 * Fixed query:
	 * 		
	 * SELECT T1.date, T1.date_hits, T1.total_hits_to_date, T1.date_downloads, T1.total_downloads_to_date FROM (
	 * 	SELECT date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
	 * 			FROM jos_daily_stats
	 * 			WHERE article_id = 502
	 * 			AND date = (
	 * 					SELECT MAX(date)
	 * 					FROM jos_daily_stats t
	 * 					WHERE article_id = t.article_id
	 * 				)
	 * ) T1
	 * ORDER BY T1.total_downloads_to_date DESC
	 * LIMIT 1
	 * 
	 * Res:
	 * 
	 * date 		d_hits 	tot_hits_td d_downl  tot_downl_td
	 * 2013-04-26 	  37 	   106 		   5			17
	 */
	private static function getLastAndTotalHitsAndDownloadsForArticleQuery($articleId) {
		$qu =  "SELECT DATE_FORMAT(T1.date,'%d-%m') as displ_date, T1.date_hits, T1.total_hits_to_date, T1.date_downloads, T1.total_downloads_to_date FROM (
					SELECT date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
						FROM #__daily_stats ds1
						WHERE ds1.article_id = $articleId
						AND date = (
							SELECT MAX(ds2.date)
							FROM #__daily_stats ds2
							WHERE ds2.article_id = $articleId
						)
				) T1
				ORDER BY T1.total_downloads_to_date DESC
				LIMIT 1";
		return $qu;
	}
	
	private static function getLastAndTotalHitsAndDownloadsForCategoryQuery($categoryId) {
		$qu =  "SELECT DATE_FORMAT(ds1.date,'%d-%m') as displ_date, SUM(ds1.date_hits) date_hits, SUM(ds1.total_hits_to_date) total_hits_to_date, SUM(ds1.date_downloads) date_downloads, SUM(ds1.total_downloads_to_date) total_downloads_to_date
				FROM #__daily_stats ds1, #__content c
				WHERE ds1.article_id = c.id
				AND c.catid = $categoryId
				AND ds1.date = (
					SELECT MAX(ds2.date)
					FROM #__daily_stats ds2, #__content c2
					WHERE ds2.article_id = c2.id
					AND c2.catid = $categoryId)";	
		return $qu;
	}
	
	private static function getLastAndTotalHitsAndDownloadsForAllCategoriesQuery($dailyStatsTableName,$contentTableName) {
		// category id 135 is the id of the audio parent category
		$audio_cat_id = AUDIO_CATEGORY_ID;
		
		$qu =  "SELECT DATE_FORMAT(s.date,'%d-%m') as displ_date, SUM(s.date_hits) date_hits, SUM(s.total_hits_to_date) total_hits_to_date, SUM(s.date_downloads) date_downloads, SUM(s.total_downloads_to_date) total_downloads_to_date
				FROM $dailyStatsTableName AS s, $contentTableName as c
				WHERE s.article_id = c.id
				AND c.catid IN (
					SELECT id
					FROM #__categories
					WHERE parent_id	IN (
						SELECT id
						FROM #__categories
						WHERE parent_id = $audio_cat_id
					)
				)
				AND s.date = (
					SELECT MAX(date)
					FROM $dailyStatsTableName)";
		return $qu;
	}
}

?>