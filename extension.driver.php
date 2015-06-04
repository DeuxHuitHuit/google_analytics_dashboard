<?php
	/*
	Copyrights: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	require_once(EXTENSIONS . '/google_analytics_dashboard/vendor/autoload.php');
	
	/**
	 *
	 * @author Deux Huit Huit
	 * https://deuxhuithuit.com/
	 *
	 */
	class extension_google_analytics_dashboard extends Extension {

		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Google Analytics Dashboard';
		
		/**
		 * Name of the extension
		 * @var string
		 */
		const PANEL_NAME = 'Google Analytics';

		const URL = '/extension/google_analytics_dashboard/';

		private static $CHART_TYPES = array('LINE', 'COLUMN', 'BAR', 'TABLE', 'GEO', 'PIE');

		/* ********* DELEGATES ******* */

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelRender',
					'callback'  => 'dashboard_render_panel'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelTypes',
					'callback'  => 'dashboard_panel_types'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelOptions',
					'callback'  => 'dashboard_panel_options'
				),
				array(
					'page'      => '/backend/',
					'delegate'  => 'DashboardPanelValidate',
					'callback'  => 'dashboard_panel_validate'
				),
			);
		}

		public function dashboard_render_panel($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			$config = $context['config'];
			$height = isset($config['height']) ? $config['height'] : '225px';
			$i = new XMLElement('iframe', null, array(
				'src' => APPLICATION_URL . self::URL .'?p=' . $context['id'],
				'style' => "width:100%;height:$height;",
				'frameborder' => 'no',
			));

			$context['panel']->appendChild($i);
		}

		public function dashboard_panel_types($context) {
			$context['types'][self::PANEL_NAME] = self::PANEL_NAME;
		}

		public function dashboard_panel_options($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			$config = $context['existing_config'];
			if (empty($config)) {
				$handle = General::createHandle(self::EXT_NAME);
				$settings = Symphony::Configuration()->get($handle);
				if (!empty($settings)) {
					$config = $settings;
				}
			}

			$wrapper = new XMLElement('div');

			$fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
			$fieldset->appendChild(new XMLElement('legend', 'Google Analytics Options'));

			$label = Widget::Label('Google Analytics Service account email', Widget::Input('config[email]', $config['email']));
			$fieldset->appendChild($label);
			
			$label = Widget::Label('Google Analytics p12 key file path (absolute or DOCROOT relative)', Widget::Input('config[keyfile]', $config['keyfile']));
			$fieldset->appendChild($label);
			$wrapper->appendChild($fieldset);
			
			$fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
			$fieldset->appendChild(new XMLElement('legend', 'Reporting Options'));
			$layout = new XMLElement('div', null, array('class' => 'two columns'));
			$fieldset->appendChild($layout);
			
			if (!$config['start-date']) {
				$config['start-date'] = '30daysAgo';
			}
			$label = Widget::Label('Start date', Widget::Input('config[start-date]', $config['start-date']), 'column');
			$layout->appendChild($label);
			
			if (!$config['end-date']) {
				$config['end-date'] = 'yesterday';
			}
			$label = Widget::Label('End date', Widget::Input('config[end-date]', $config['end-date']), 'column');
			$layout->appendChild($label);
			
			if (!$config['dimensions']) {
				$config['dimensions'] = 'ga:date';
			}
			$label = Widget::Label('Dimensions', Widget::Input('config[dimensions]', $config['dimensions']), 'column');
			$layout->appendChild($label);
			
			if (!$config['metrics']) {
				$config['metrics'] = 'ga:sessions';
			}
			$label = Widget::Label('Metrics', Widget::Input('config[metrics]', $config['metrics']), 'column');
			$layout->appendChild($label);
			
			if (!$config['type']) {
				$config['type'] = 'LINE';
			}
			$lineOptions = array();
			foreach (self::$CHART_TYPES as $type) {
				$lineOptions[] = array($type, $config['type'] == $type, strtolower($type));
			}
			$label = Widget::Label('Chart type', Widget::Select('config[type]', $lineOptions), 'column');
			$layout->appendChild($label);
			
			$link = Widget::Anchor('Click here to explore available dimensions and metrics', 'https://developers.google.com/analytics/devguides/reporting/core/dimsmets', null, null, null, array('target' => '_blank'));
			$label = Widget::Label('Help <br />', $link, 'column');
			$layout->appendChild($label);
			$wrapper->appendChild($fieldset);
			
			$fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
			$fieldset->appendChild(new XMLElement('legend', 'Display Options'));
			
			$label = Widget::Label('Height (include units)', Widget::Input('config[height]', $config['height']));
			$fieldset->appendChild($label);

			$label = Widget::Label('Save as default', Widget::Input('default', 'on', 'checkbox'));
			$fieldset->appendChild($label);
			$wrapper->appendChild($fieldset);

			$context['form'] = $wrapper;
		}

		public function dashboard_panel_validate($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
			if (isset($_POST['default']) && $_POST['default'] == 'on') {
				$config = $context['existing_config'];
				$handle = General::createHandle(self::EXT_NAME);
				$client = static::createClient($config, $context['id']);
				if (!isset($config['at'])) {
					$config['at'] = $client->getAccessToken();
				}
				foreach ($config as $key => $value) {
					Symphony::Configuration()->set($key, $value, $handle);
				}
				Symphony::Configuration()->write();
			}
		}

		/* ********* GOOGLE CLIENT ******* */

		public static function createClient(array $config, $panelId) {
			$client = new Google_Client();
			$keyfile = $config['keyfile'];
			if (strpos($keyfile, '/') !== 0) {
				$keyfile = DOCROOT . '/' . $keyfile;
			}
			$key = @file_get_contents($keyfile);
			if (!!$key) {
				$cred = new Google_Auth_AssertionCredentials(
					$config['email'],
					array(Google_Service_Analytics::ANALYTICS_READONLY),
					$key
				);
				$client->setAssertionCredentials($cred);
				if ($client->getAuth()->isAccessTokenExpired()) {
					$client->getAuth()->refreshTokenWithAssertion($cred);
				}
			}
			return $client;
		}

		/* ********* INSTALL/UPDATE/UNINSTALL ******* */

		/**
		 * Creates the table needed for the settings of the field
		 */
		public function install() {
			return true;
		}

		/**
		 * This method will update the extension according to the
		 * previous and current version parameters.
		 * @param string $previousVersion
		 */
		public function update($previousVersion = false) {
			return true;
		}

		public function uninstall() {
			return true;
		}

	}