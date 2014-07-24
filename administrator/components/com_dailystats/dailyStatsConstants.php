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
define('EXCLUDED_J16_CATEGORIES_SET',"116, 2, 129, 133, 128, 115, 127");
define('J16_SECTION_LEVEL', 1);
define('EXCLUDED_J15_SECTIONS_SET',"6, 7, 22, 23, 29");

define('CHART_X_SIZE',1375);
define('MAX_PLOT_POINTS',620);	// floor(CHART_X_SIZE / 2.2);
define('NO_ARTICLE_SELECTED',0);
define('MOST_RECENT_ARTICLE_NUMBER',20);

define('CHART_MODE_ARTICLE',100);
define('CHART_MODE_CATEGORY',200);
define('CHART_MODE_CATEGORY_ALL',300);

define('CALLED_FROM_FRONTEND',100);
define('CALLED_FROM_BACKEND',200);

define('DATE_IDX','DATE');
define('LAST_HITS_IDX','LAST_HITS');
define('TOTAL_HITS_IDX','TOTAL_HITS');
define('LAST_DOWNLOADS_IDX','LAST_DOWNLOADS');
define('TOTAL_DOWNLOADS_IDX','TOTAL_DOWNLOADS');

?>