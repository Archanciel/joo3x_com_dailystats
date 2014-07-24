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

class DailyStatsHelper {
	
	/**
	 *
	 * @return either CALLED_FROM_BACKEND or CALLED_FROM_FRONTEND
	 */
	public static function determineExecEnv($file) {
		return (strpos($file, 'administrator')) ? CALLED_FROM_BACKEND : CALLED_FROM_FRONTEND;
	}
	
	public static function determinePreviousCatSecId($categorySectionId, $previouslySelectedCategorySectionId) {
		if ($categorySectionId != $previouslySelectedCategorySectionId	&&
				$categorySectionId != 0									&&
				$previouslySelectedCategorySectionId != 0) {	// $categorySectionId == 0 and $previouslySelectedCategorySectionId ==0 when launching the Daily Stats component for the first time after login
			// current category did change, so current article selection must be reset
			return $categorySectionId;
		} else {
			return 0;
		}
	}
}
?>