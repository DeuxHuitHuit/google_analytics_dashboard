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
<h1>Server auth failed! Please check your configuration.</h1>
HTML;
			}
			else {
				
				
				$html = <<<HTML
<style type="text/css">
	html {
		overflow-x: hidden;
	}
	body {
		margin: 0;
	}
	.hidden {
		display: none;
	}
</style>
<section id="chart"></section>
<a href="#" id="toggleOptions"><small>Options</small></a>
<section id="view-selector" class="hidden"></section>
<script>
(function (g) {
g.each = function (a, cb) {
	Array.prototype.forEach.call(a, cb);
};
g.$ = function (sel, cb) {
	var col = document.querySelectorAll(sel);
	if (!!cb) {
		each(col, cb);
	}
	return col;
};
g.$.remove = function (elem) {
	elem.parentNode.removeChild(elem);
};
})(window);
</script>
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
  var viewSelector = new gapi.analytics.ViewSelector({
    container: 'view-selector'
  });
  var chart = new gapi.analytics.googleCharts.DataChart({
    reportType: 'ga',
    query: {
      'dimensions': '{$config["dimensions"]}',
      'metrics': '{$config["metrics"]}',
      'start-date': '{$config["start-date"]}',
      'end-date': '{$config["end-date"]}',
    },
    chart: {
      type: '{$config["type"]}',
      container: 'chart',
      options: {
        width: '100%',
        height: '100%'
      }
    }
  });
  viewSelector.on('change', function(ids) {
    var newIds = {
      query: {
        ids: ids
      }
    }
    chart.set(newIds).execute();
  });
  chart.on('error', function (err) {
    alert(err.error.message);
  });
  viewSelector.execute();
  window.addEventListener('resize', function () {
    viewSelector.execute();
  });
});
</script>

<script>
var toggleOptions = function (e) {
	$('#view-selector', function (elem) {
		elem.classList.toggle('hidden');
	});
	e.preventDefault();
};
$('#toggleOptions', function (elem) {
	elem.addEventListener('click', toggleOptions);
});
</script>

HTML;
			}
			$this->Body->setValue($html);
		}
	}
