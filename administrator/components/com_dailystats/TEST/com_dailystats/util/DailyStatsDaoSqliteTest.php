<?php

require_once dirname ( __FILE__ ) . '\..\baseclass\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

class DailyStatsDaoSqliteTest extends DailyStatsTestBase {
	
	private $pdo;

	public function setUp() {
		if (TRUE) {
			if ($this->pdo = new PDO('sqlite::memory:')) {
				$this->pdo->exec("CREATE TABLE IF NOT EXISTS `jos_content` (
									  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
									  `title` varchar(255) NOT NULL DEFAULT '',
									  `alias` varchar(255) NOT NULL DEFAULT '',
									  `title_alias` varchar(255) NOT NULL DEFAULT '',
									  `introtext` mediumtext NOT NULL,
									  `fulltext` mediumtext NOT NULL,
									  `state` tinyint(3) NOT NULL DEFAULT '0',
									  `sectionid` int(11) unsigned NOT NULL DEFAULT '0',
									  `mask` int(11) unsigned NOT NULL DEFAULT '0',
									  `catid` int(11) unsigned NOT NULL DEFAULT '0',
									  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
									  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
									  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
									  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
									  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									  `images` text NOT NULL,
									  `urls` text NOT NULL,
									  `attribs` text NOT NULL,
									  `version` int(11) unsigned NOT NULL DEFAULT '1',
									  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
									  `ordering` int(11) NOT NULL DEFAULT '0',
									  `metakey` text NOT NULL,
									  `metadesc` text NOT NULL,
									  `access` int(11) unsigned NOT NULL DEFAULT '0',
									  `hits` int(11) unsigned NOT NULL DEFAULT '0',
									  `metadata` text NOT NULL,
									  PRIMARY KEY (`id`),
									  KEY `idx_section` (`sectionid`),
									  KEY `idx_access` (`access`),
									  KEY `idx_checkout` (`checked_out`),
									  KEY `idx_state` (`state`),
									  KEY `idx_catid` (`catid`),
									  KEY `idx_createdby` (`created_by`))");
				$this->pdo->exec("CREATE TABLE IF NOT EXISTS `jos_attachments` (
									  `id` int(11) NOT NULL AUTO_INCREMENT,
									  `filename` varchar(80) NOT NULL,
									  `filename_sys` varchar(255) NOT NULL,
									  `file_type` varchar(30) NOT NULL,
									  `file_size` int(11) unsigned NOT NULL,
									  `icon_filename` varchar(20) NOT NULL,
									  `display_filename` varchar(80) NOT NULL DEFAULT '',
									  `description` varchar(255) NOT NULL DEFAULT '',
									  `url` varchar(255) NOT NULL,
									  `uploader_id` int(11) NOT NULL,
									  `article_id` int(11) unsigned NOT NULL,
									  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
									  `user_field_1` varchar(100) NOT NULL DEFAULT '',
									  `user_field_2` varchar(100) NOT NULL DEFAULT '',
									  `user_field_3` varchar(100) NOT NULL DEFAULT '',
									  `create_date` datetime DEFAULT NULL,
									  `modification_date` datetime DEFAULT NULL,
									  `download_count` int(11) unsigned DEFAULT '0',
									  PRIMARY KEY (`id`),
									  KEY `attachment_article_id_index` (`article_id`))");
				$this->pdo->exec("CREATE TABLE IF NOT EXISTS `jos_daily_stats` (
									  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
									  `article_id` int(11) unsigned NOT NULL,
									  `attachment_id` int(11) unsigned NOT NULL,
									  `date` date NOT NULL,
									  `total_hits_to_date` int(11) unsigned NOT NULL,
									  `date_hits` int(11) unsigned NOT NULL DEFAULT '0',
									  `total_downloads_to_date` int(11) unsigned NOT NULL,
									  `date_downloads` int(11) unsigned NOT NULL DEFAULT '0',
									  PRIMARY KEY (`id`))");
			} else {
				die ($this->pdo->errorInfo());
			}
		} else {
			parent::setUp ();
		}
	}

	/**
	 * Tests 1 article with only 1 daily stats rec
	 */
	public function testGetLastAndTotalHitsAndDownloadsArr() {
		$this->getLastAndTotalHitsAndDownloadsArr_1_dailyStats();
		$this->getLastAndTotalHitsAndDownloadsArr_2_dailyStats();
		$this->getLastAndTotalHitsAndDownloadsArr_no_dailyStats();
	}

	/**
	 * Sets the connection to the database
	 *
	 * @return connection
	 */
	protected function getConnection() {
		if (TRUE) {
//			$pdo = new PDO('sqlite::memory:');
			return $this->createDefaultDBConnection($this->pdo, ':memory:');
		} else {
			return parent::getConnection();
		}
	}

	private function getLastAndTotalHitsAndDownloadsArr_1_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,1);

		$this->assertEquals(5, count($res),'count($res)');

		$this->assertEquals('20-10',$res[DATE_IDX],'date');
		$this->assertEquals(15,$res[LAST_HITS_IDX],'date hits');
		$this->assertEquals(150,$res[TOTAL_HITS_IDX],'total hits');
		$this->assertEquals(10,$res[LAST_DOWNLOADS_IDX],'date downloads');
		$this->assertEquals(100,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
	}

	/**
	 * Tests 1 article with only 2 daily stats recs
	 */
	private function getLastAndTotalHitsAndDownloadsArr_2_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,2);

		$this->assertEquals(5, count($res),'count($res)');

		$this->assertEquals('21-11',$res[DATE_IDX],'date');
		$this->assertEquals(150,$res[LAST_HITS_IDX],'date hits');
		$this->assertEquals(1500,$res[TOTAL_HITS_IDX],'total hits');
		$this->assertEquals(100,$res[LAST_DOWNLOADS_IDX],'date downloads');
		$this->assertEquals(1000,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
	}

	/**
	 * Tests 1 article with no daily stats rec
	 */
	private function getLastAndTotalHitsAndDownloadsArr_no_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,3);

		$this->assertEquals(5, count($res),'count($res)');

		$this->assertNull($res[DATE_IDX],'date');
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet() {
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\1_category_1_article_test_data.xml' );
	}
}

?>