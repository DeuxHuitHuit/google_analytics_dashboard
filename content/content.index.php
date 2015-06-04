<?php
	/*
	Copyrights: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	/**
	 *
	 * @author Deux Huit Huit
	 * https://deuxhuithuit.com/
	 *
	 */
	class contentExtensionGoogle_analytics_dashboardIndex extends HTMLPage {

		/**
		 * Builds the content view
		 */
		public function build() {
			if (!Symphony::isLoggedIn()) {
				Administration::instance()->throwCustomError(
					__('You are not authorised to access this page.'),
					__('Access Denied'),
					Page::HTTP_STATUS_UNAUTHORIZED
				);
				return;
			}
			
			// this loads our classes
			$ext = Symphony::ExtensionManager()->create('dashboard');
			$ext = Symphony::ExtensionManager()->create('google_analytics_dashboard');
			
			// html head
			$this->Html->setDTD('<!DOCTYPE html>');
			$this->Html->setAttribute('lang', Lang::get());
			
			$this->Head->appendChild(new XMLElement('title', extension_google_analytics_dashboard::EXT_NAME));
			
			// html body
			$html = '';
			$PANEL_ID = General::sanitize($_REQUEST['p']);
			$panel = Extension_Dashboard::getPanel($PANEL_ID);
			$config = unserialize($panel['config']);
			$client = extension_google_analytics_dashboard::createClient($config, $panel['id']);
			
			if (!isset($config['at'])) {
				$config['at'] = $client->getAccessToken();
			}
			
			if (!($at = @json_decode($config['at']))) {
				$html = <<<HTML
<h1>Server auth failed! Please check your configuration and make sure you have a valid access token</h1>
HTML;
			}
			else {
				
				
				$html = <<<HTML

<section id="auth-button"></section>
<section id="view-selector"></section>
<section id="timeline"></section>
<script>
(function(w,d,s,g,js,fjs){
  g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(cb){this.q.push(cb)}};
  js=d.createElement(s);fjs=d.getElementsByTagName(s)[0];
  js.src='https://apis.google.com/js/platform.js';
  fjs.parentNode.insertBefore(js,fjs);js.onload=function(){g.load('analytics')};
}(window,document,'script'));
</script>

<script>
gapi.analytics.ready(function() {
  gapi.analytics.auth.authorize({
    serverAuth: {
      access_token: '$at->access_token'
    }
  });
  console.log('Autorized ? ' + gapi.analytics.auth.isAuthorized());
  var viewSelector = new gapi.analytics.ViewSelector({
    container: 'view-selector'
  });
  var timeline = new gapi.analytics.googleCharts.DataChart({
    reportType: 'ga',
    query: {
      'dimensions': 'ga:date',
      'metrics': 'ga:sessions',
      'start-date': '30daysAgo',
      'end-date': 'yesterday',
    },
    chart: {
      type: 'LINE',
      container: 'timeline'
    }
  });
  viewSelector.on('change', function(ids) {
    var newIds = {
      query: {
        ids: ids
      }
    }
    timeline.set(newIds).execute();
  });
  viewSelector.execute();
});
</script>

HTML;
			}
			$this->Body->setValue($html);
		}
	}
