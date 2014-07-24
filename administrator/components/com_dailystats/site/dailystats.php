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

// categories to exclude from caregory list: aide, uncategorized, en conscience, les incontournables, pensées, site, webmaster
define(EXCLUDED_J16_CATEGORIES_SET,"116, 2, 129, 133, 128, 115, 127");
define(J16_SECTION_LEVEL, 1);
define(EXCLUDED_J15_SECTIONS_SET,"6, 7, 22, 23, 29");

define(CHART_X_SIZE,1290);
define(MAX_PLOT_POINTS,581);	// floor(CHART_X_SIZE / 2.2);
define(NO_ARTICLE_SELECTED,0);

define(CHART_MODE_ARTICLE,100);
define(CHART_MODE_CATEGORY,200);

// add form controls manipulation javascript
$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_dailystats/js/dailystats.js');

// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'on');

/**
 * 
 * @param unknown_type $articleId
 * @param unknown_type $categoryId
 * @param unknown_type $yValName
 * @param unknown_type $chartMode
 * @return string
 */
function buildPlotDataQuery($articleId, $categoryId, $yValName, $chartMode) {
	switch ($chartMode) {
		case CHART_MODE_ARTICLE:
			$qu =  "SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.{$yValName}
					FROM (
						SELECT date, $yValName
						FROM #__daily_stats
						WHERE article_id = $articleId
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
		default:
			return '';
			break;
	}
}

/****************** section specific to front dailystats.php version ************/
// get parameters from the active menu item

$app = JFactory::getApplication('site');
$menu_params =  & $app->getParams();

if ($menu_params != null) {
	echo '<div class="componentheading">'.$menu_params->get('page_hdr').'</div>';
	echo $menu_params->get('page_text');
}

// WARNING: NXT LINE COMMENTED OUT ON FRONT VERSION
// JToolBarHelper::title('Daily Stats', ''
/****************** section specific to front dailystats.php version ************/

$mainframe = JFactory::getApplication();

// get chart mode, either chart an individual article hisctrory or a category summary history
$chartWholeCategory = (JRequest::getVar('chart_whole_category',""));
$chartMode = (strcmp($chartWholeCategory,"on") == 0) ? CHART_MODE_CATEGORY : CHART_MODE_ARTICLE;


// get list of categories

$db	= JFactory::getDBO();

if(version_compare(JVERSION,'1.6.0','ge')) {
	$query = "SELECT id,title FROM #__categories WHERE extension LIKE 'com_content' AND level = " . J16_SECTION_LEVEL . " AND id NOT IN (" . EXCLUDED_J16_CATEGORIES_SET . ") ORDER BY title";
} else {	// Joomla 1.5
	$query = "SELECT id,title FROM #__sections WHERE scope LIKE 'content' AND id NOT IN (" . EXCLUDED_J15_SECTIONS_SET . ") ORDER BY title";
}

$db->setQuery($query);
$rows = $db->loadObjectList();

// get the selected category from the select list (default it to zero)

$categorySectionId = JRequest::getVar('select_category_section',0);
$previouslySelectedCategorySectionId = $mainframe->getUserState( "option.previous_select_category_section", 0 );
// echo 'Category: from request ' . $categorySectionId . ' previous from session ' . $previouslySelectedCategorySectionId , ' articleId ' . $articleId;

if ($categorySectionId != $previouslySelectedCategorySectionId	&&
		$categorySectionId != 0	&&
		$previouslySelectedCategorySectionId	!= 0) {	// $categorySectionId == 0 and $previouslySelectedCategorySectionId ==0 when launching the Daily Stats component for the first time after login
	// current category did change, so current article selection must be reset
	$mainframe->setUserState( "option.previous_select_category_section",$categorySectionId);
	$articleId = NO_ARTICLE_SELECTED;
} else {
	$articleId = JRequest::getVar('select_article',0);
}

// echo 'Category: from request ' . $categorySectionId . ' previous from session ' . $mainframe->getUserState( "option.previous_select_category_section", 0 ) , ' articleId ' . $articleId;

if ($categorySectionId == 0) {
	$categorySectionId = $rows[0]->id;		// default to the first row
}

// Build an html select list of categories (include Javascript to submit the form)

foreach ($rows as $row) {
	$category_array[] = JHTML::_('select.option', $row->id, $row->title);
	
	// store selected category title
	if ($row->id == $categorySectionId) {
		$displayDataTitle = $row->title;
	}
}

$select_category_section_list = JHTML::_('select.genericlist', $category_array, 'select_category_section',
		'class="inputbox" size="1" onchange="document.dailyStatsForm.submit();"', 'value', 'text', $categorySectionId);

// Build an html select list of articles (include Javascript to submit the form)

$article_array[] = JHTML::_('select.option', NO_ARTICLE_SELECTED, '- Select article -');

// get list of articles

if ($chartMode == CHART_MODE_ARTICLE) {	// optimization: only get the articles from db if in chart article mode !
	$db	= JFactory::getDBO();
	$query = "SELECT id, title, DATE_FORMAT(created,'%a %D, %M %Y') as creation_date FROM #__content WHERE sectionid = $categorySectionId ORDER BY title";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	
	foreach ($rows as $row) {
		$article_array[] = JHTML::_('select.option', $row->id, $row->title);
			
		// store selected article title
		if ($row->id == $articleId) {
			$displayDataTitle = $row->title . ' (created ' . $row->creation_date . ')';
		}
	}
}

switch ($chartMode) {
	case CHART_MODE_ARTICLE:
		if (!isset($displayDataTitle)) {
			// is the case if the user refreshes the page right after having changed the category
			$articleId = NO_ARTICLE_SELECTED;
			$displayDataTitle = '';
		}
		break;
	default:
		;
		break;
}


$disabled = ($chartMode == CHART_MODE_ARTICLE) ? '' : 'disabled="true"';	// disabled="false" not working: simply drop the attribute !
$select_article_list = JHTML::_('select.genericlist', $article_array, 'select_article',
		'class="inputbox" ' . $disabled . '" size="1" onchange="handleSelectArticle();"', 'value', 'text', $articleId);


// draw the form with the select lists

echo '<form action="index.php" method="post" name="dailyStatsForm" method="post">';
echo '<input type="hidden" name="option" value="com_dailystats" />';
echo '<input type="hidden" name="draw_chart" value="no" />';

// category / section -----------------------------

if(version_compare(JVERSION,'1.6.0','ge')) {
	echo 'Select a category: ';
} else {
	echo 'Select a section: ';
}

echo $select_category_section_list;

echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Chart whole category: ';
echo '<input type="checkbox" name="chart_whole_category" ';
if ($chartMode == CHART_MODE_CATEGORY) {
	echo 'checked '; 
}
echo 'onclick="handleChartWholeCategory(this)" />';

// article ----------------------------------------
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select an article: ';
echo $select_article_list;

echo '</form>';

$drawChart = JRequest::getVar('draw_chart','no');

if (($chartMode == CHART_MODE_ARTICLE	&&
	strcmp($drawChart, 'no') != 0		&&
	$articleId > 0)							||
	($chartMode == CHART_MODE_CATEGORY	&&
	$categorySectionId > 0)) {
// 	echo 'draw';
	// pull in the Plotalot helper file from the backend helpers directory

	require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/plotalot.php';

	// --- Hits ------

	// construct the plot info structure

	$plot_info = new stdclass();
	$plot_info->id = 1;		// the id must match the html element that the chart will be drawn in
	$plot_info->chart_title = "Hits: " . $displayDataTitle;
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
	$plot_info->plot_array[0]['query'] = buildPlotDataQuery($articleId, $categorySectionId, "date_hits",$chartMode);

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
	$plot_info->chart_title = "Downloads: " . $displayDataTitle;
	$plot_info->chart_type = CHART_TYPE_BAR_V_GROUP;
	$plot_info->x_size = CHART_X_SIZE;
	$plot_info->y_size = 180;
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
	$plot_info->plot_array[0]['query'] = buildPlotDataQuery($articleId, $categorySectionId, "date_downloads",$chartMode);

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
