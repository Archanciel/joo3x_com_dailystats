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

// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'on');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/dailyStatsConstants.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.'/dao/dailyStatsDao.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/dailyStatsHelper.php';

$cron = JRequest::getVar ( 'cron' , 'no' );

if (strcmp($cron,'yes') == 0) {
    DailyStatsDao::execDailyStatsCron();
	return;
} else if (strcmp($cron,'mail') == 0) {
    DailyStatsDao::testLogAndMail();
	return;
}

// add form controls manipulation javascript
$document = JFactory::getDocument();
$document->addScript(JURI::root().'administrator/components/com_dailystats/js/dailystats.js');

// perfcorms page initialisation differently according to where compponent is called from,
// either from front or from backend
$execEnv = DailyStatsHelper::determineExecEnv(__FILE__);

if ($execEnv == CALLED_FROM_BACKEND) {
	JToolBarHelper::title('Daily Stats', '');	
} else {
	// get parameters from the active menu item
	$app = JFactory::getApplication('site');
	$menu_params =  $app->getParams();
	
	if ($menu_params != null) {
		echo '<div class="componentheading">'.$menu_params->get('page_hdr').'</div>';
		echo $menu_params->get('page_text');
	}
}

// get list of categories

$catSecRows = DailyStatsDao::getCategoriesOrSections();

// get the selected category from the select list (default it to zero)

$categorySectionId = JRequest::getVar('select_category_section',0);
$previouslySelectedCategorySectionId = JRequest::getVar( 'previous_cat_sec_id', 0 );
// echo 'Category: from request ' . $categorySectionId . ' previous from session ' . $previouslySelectedCategorySectionId , ' articleId ' . $articleId;

if ($categorySectionId != $previouslySelectedCategorySectionId	&&
	$previouslySelectedCategorySectionId != 0) {	// $categorySectionId == 0 and $previouslySelectedCategorySectionId ==0 when launching the Daily Stats component for the first time after login
	// current category did change, so current article selection must be reset
	$articleId = NO_ARTICLE_SELECTED;
} else {
	$articleId = JRequest::getVar('select_article',NO_ARTICLE_SELECTED);
}

if ($categorySectionId == 0) {
	// here, DAILY STATS has just be launched
	$chartMode = CHART_MODE_CATEGORY_ALL;
	$categorySectionId = PHP_INT_MAX;
} else if ($categorySectionId == PHP_INT_MAX) {
	// here, All categories is selected in the category drop-down list
	$chartWholeCategoryButtonPressed = (JRequest::getVar('chart_whole_category_button',NULL));
	
	if (isset($chartWholeCategoryButtonPressed)) {
		// Chart whole category button waa pressed
		$chartMode = CHART_MODE_CATEGORY_ALL;
		$articleId = NO_ARTICLE_SELECTED;
	} else {
		if (isset($articleId)	&&
			$articleId != NO_ARTICLE_SELECTED) {
			// one of the most recent article was selected
			$chartMode = CHART_MODE_ARTICLE;
		} else {
			$chartMode = CHART_MODE_CATEGORY_ALL;
			$articleId = NO_ARTICLE_SELECTED;
		}
	}
} else {
	// get chart mode, either chart an individual article hisctrory or a category summary history
	$chartWholeCategoryButtonPressed = (JRequest::getVar('chart_whole_category_button',NULL));
	$chartMode = (isset($chartWholeCategoryButtonPressed)) ? CHART_MODE_CATEGORY : CHART_MODE_ARTICLE;
}


// Build an html select list of categories (include Javascript to submit the form)

// adding a row for all categories

$category_array[] = JHTML::_('select.option', PHP_INT_MAX, 'All categories');

foreach ($catSecRows as $catSecRow) {
	$category_array[] = JHTML::_('select.option', $catSecRow->id, $catSecRow->title);
	
	// store selected category title
	if ($catSecRow->id == $categorySectionId) {
		$displayDataTitle = $catSecRow->title;
		$lastAndTotalHitsArr = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr($chartMode,$categorySectionId);
	}
}

if ($chartMode == CHART_MODE_CATEGORY_ALL) {
	$displayDataTitle = 'All categories';
	$lastAndTotalHitsArr = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr($chartMode);
}

$lastAndTotalHitsTitle = ". Last ({$lastAndTotalHitsArr[DATE_IDX]}): {$lastAndTotalHitsArr[LAST_HITS_IDX]}. Total: {$lastAndTotalHitsArr[TOTAL_HITS_IDX]}.";
$lastAndTotalDownloadsTitle = ". Last ({$lastAndTotalHitsArr[DATE_IDX]}): {$lastAndTotalHitsArr[LAST_DOWNLOADS_IDX]}. Total: {$lastAndTotalHitsArr[TOTAL_DOWNLOADS_IDX]}.";

$select_category_section_list = JHTML::_('select.genericlist', $category_array, 'select_category_section',
		'class="inputbox" size="1" onchange="handleSelectCategory();"', 'value', 'text', $categorySectionId);

// Build an html select list of articles (include Javascript to submit the form)

$article_array[] = JHTML::_('select.option', NO_ARTICLE_SELECTED, '- Select article -');

// get list of articles

if ($chartMode == CHART_MODE_CATEGORY_ALL	||
	$categorySectionId == PHP_INT_MAX) {
	$articleRows = DailyStatsDao::getMostRecentArticles(MOST_RECENT_ARTICLE_NUMBER);
} else {
	if ($chartMode == CHART_MODE_CATEGORY) {
		$articleId = 0;
	}
	
	$articleRows = DailyStatsDao::getArticlesForCatSec($categorySectionId);	
}

if (!empty($articleRows)) {	
	foreach ($articleRows as $articleRow) {
		$article_array[] = JHTML::_('select.option', $articleRow->id, $articleRow->title);
			
		// store selected article title
		if ($articleRow->id == $articleId) {
			$displayDataTitle = $articleRow->title . ' (created ' . $articleRow->creation_date . ')';
			$lastAndTotalHitsArr = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr($chartMode,$articleId);
			$lastAndTotalHitsTitle = ". Last ({$lastAndTotalHitsArr[DATE_IDX]}): {$lastAndTotalHitsArr[LAST_HITS_IDX]}. Total: {$lastAndTotalHitsArr[TOTAL_HITS_IDX]}.";
			$lastAndTotalDownloadsTitle = ". Last ({$lastAndTotalHitsArr[DATE_IDX]}): {$lastAndTotalHitsArr[LAST_DOWNLOADS_IDX]}. Total: {$lastAndTotalHitsArr[TOTAL_DOWNLOADS_IDX]}.";
		}
	}
}

switch ($chartMode) {
	case CHART_MODE_ARTICLE:
		if (!isset($displayDataTitle)) {
			// is the case if the user refreshes the page right after having changed the category
			$articleId = NO_ARTICLE_SELECTED;
			$displayDataTitle = '';
			$lastAndTotalHitsTitle = '';
			$lastAndTotalDownloadsTitle = '';
		}
		break;
	default:
		;
		break;
}

$select_article_list = JHTML::_('select.genericlist', $article_array, 'select_article',
		'class="inputbox" size="1" onchange="handleSelectArticle();"', 'value', 'text', $articleId);


// draw the form with the select lists

// WARNING: action defers for back and front component 
if ($execEnv == CALLED_FROM_BACKEND) {
	echo '<form action="index.php" method="post" name="dailyStatsForm" method="post">';
} else {
	echo '<form action="'.JRoute::_('index.php').'" method="post" name="dailyStatsForm">';
}

echo '<input type="hidden" name="option" value="com_dailystats" />';
echo '<input type="hidden" name="draw_chart" value="no" />';
echo '<input type="hidden" name="previous_cat_sec_id" value="'."<?php echo DailyStatsHelper::determinePreviousCatSecId($categorySectionId, $previouslySelectedCategorySectionId); ?>".'" />';

// ---- category / section -----------------------------

if(version_compare(JVERSION,'1.6.0','ge')) {
	echo 'Select a category: ';
} else {
	echo 'Select a section: ';
}

echo $select_category_section_list;

echo '<input type="submit" class="button" name="chart_whole_category_button" value="Chart whole category"';
echo 'onclick="handleChartWholeCategoryButtonPressed(this)" />';

// ---- article ----------------------------------------

echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select an article: ';
echo $select_article_list;

echo '</form>';

$drawChart = (strcmp(JRequest::getVar('draw_chart','no'),'no') != 0);
// echo '$drawChart ' . $drawChart;
// echo ' $chartMode ' . $chartMode;
// echo ' $categorySectionId ' . $categorySectionId;
// echo ' $articleId ' . $articleId;
if (($chartMode == CHART_MODE_ARTICLE	&&
	$drawChart							&&
	$articleId != NO_ARTICLE_SELECTED)		||
	$chartMode == CHART_MODE_CATEGORY		||
	$chartMode == CHART_MODE_CATEGORY_ALL) {
// 	echo 'draw';

	// pull in the Plotalot helper file from the backend helpers directory

	require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/plotalot.php';

	// --- Hits ------

	// construct the plot info structure

	$plot_info = new stdclass();
	$plot_info->id = 1;		// the id must match the html element that the chart will be drawn in
 	$plot_info->chart_title = "Hits: " . $displayDataTitle . $lastAndTotalHitsTitle;
	$plot_info->chart_type = CHART_TYPE_BAR_V_GROUP;
	$plot_info->x_size = CHART_X_SIZE;
	$plot_info->y_size = 280;
	$plot_info->x_title = "Date";
	$plot_info->x_labels = 10;
	$plot_info->x_format = FORMAT_DATE_DMY;
	// $plot_info->x_start = "SELECT MIN(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
	$plot_info->x_start = "";
//	$plot_info->x_end = "SELECT MAX(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
	$plot_info->y_title = "Hits";
	$plot_info->y_labels = 7;
	$plot_info->y_start = 0;
// 	$plot_info->y_end = 3000;
	$plot_info->legend_type = LEGEND_NONE;
	$plot_info->show_grid = 1;
	$plot_info->num_plots = 1;
	$plot_info->extra_parms = ",chartArea:{left:'3%',top:'10%',width:'90%',height:'75%'}";

	// construct the plot array

	$plot_info->plot_array = array();
	$plot_info->plot_array[0]['enable'] = 1;
	$plot_info->plot_array[0]['colour'] = '7C78FF';
	$plot_info->plot_array[0]['style'] = LINE_THICK_SOLID;
	$plot_info->plot_array[0]['legend'] = 'Hits';
	$plot_info->plot_array[0]['query'] = DailyStatsDao::buildPlotDataQuery($articleId, $categorySectionId, "date_hits",$chartMode);

	// draw the chart

	$plotalot = new Plotalot;
	$chart = $plotalot->drawChart($plot_info);

	if ($chart == '') {
		echo $plotalot->error;
	} else {
		$document = JFactory::getDocument();
		$document->addScript("https://www.google.com/jsapi");	// load the Google jsapi
		$document->addCustomTag($chart);						// load the chart script
		echo '<div id="chart_1"></div>';						// create an element for the chart to be drawn in
	}

	// --- Downloads ------

	// construct the plot info structure

	$plot_info = new stdclass();
	$plot_info->id = 2;						// the id must match the html element that the chart will be drawn in
	$plot_info->chart_title = "Downloads: " . $displayDataTitle . $lastAndTotalDownloadsTitle;
	$plot_info->chart_type = CHART_TYPE_BAR_V_GROUP;
	$plot_info->x_size = CHART_X_SIZE;
	$plot_info->y_size = 200;
	$plot_info->x_title = "Date";
	$plot_info->x_labels = 10;
	$plot_info->x_format = FORMAT_DATE_DMY;
	// $plot_info->x_start = "SELECT MIN(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
	$plot_info->x_start = "";
//	$plot_info->x_end = "SELECT MAX(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
	$plot_info->y_title = "Downloads";
	$plot_info->y_labels = 7;
	$plot_info->y_start = 0;
	//	$plot_info->y_end = 30;
	$plot_info->legend_type = LEGEND_NONE;
	$plot_info->show_grid = 1;
	$plot_info->num_plots = 1;
	$plot_info->extra_parms = ",chartArea:{left:'3%',top:'10%',width:'90%',height:'75%'}";

	// construct the plot array

	$plot_info->plot_array = array();
	$plot_info->plot_array[0]['enable'] = 1;
	$plot_info->plot_array[0]['colour'] = 'FF0000';
	$plot_info->plot_array[0]['style'] = LINE_THICK_SOLID;
	$plot_info->plot_array[0]['legend'] = 'Downloads';
	$plot_info->plot_array[0]['query'] = DailyStatsDao::buildPlotDataQuery($articleId, $categorySectionId, "date_downloads",$chartMode);

	// draw the chart

	$plotalot = new Plotalot;
	$chart = $plotalot->drawChart($plot_info);

	if ($chart == '') {
		echo $plotalot->error;
	} else {
		$document = JFactory::getDocument();
		$document->addScript("https://www.google.com/jsapi");	// load the Google jsapi
		$document->addCustomTag($chart);						// load the chart script
		echo '<div id="chart_2"></div>';						// create an element for the chart to be drawn in
	}
} else {
// 	echo 'no draw';
}

?>
