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

/**
 * Called when a category is selected in the category combo
 */
function handleSelectCategory() {
//	var selectBox = document.forms['dailyStatsForm'].select_category_section;
//
//	if (selectBox.options[0].selected) {
//		// 'All categories' was selected
//		document.forms['dailyStatsForm'].chart_whole_category.checked = true;
//	}

	document.dailyStatsForm.submit();
}

/**
 * Called when an article is selected in the article combo
 */
function handleSelectArticle() {
	drawChart();
}

/**
 * Called when the chart whole category button is pressed
 */
function handleChartWholeCategoryButtonPressed(button) {
	drawChart();
}

/**
 * Sets the hidden draw_chart input field to yes and submit the form causing
 * the chart to be drawn.
 */
function drawChart() {
	document.forms['dailyStatsForm'].draw_chart.value = 'yes';
	document.dailyStatsForm.submit();
}