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
			$height = isset($config['height']) ? $config['height'] : '500px';
			$i = new XMLElement('iframe', null, array(
				'src' => APPLICATION_URL . self::URL .'?p=' . $context['id'],
				'style' => "width:100%;height:$height;",
				'frameborder' => 'no',
				'scrolling' => 'no',
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

			$fieldset = new XMLElement('fieldset', NULL, array('class' => 'settings two cols'));
			$fieldset->appendChild(new XMLElement('legend', 'Google Analytics Options'));

			$label = Widget::Label('Google Analytics Service account email', Widget::Input('config[email]', $config['email']));
			$fieldset->appendChild($label);
			
			$label = Widget::Label('Google Analytics p12 key file path', Widget::Input('config[keyfile]', $config['keyfile']));
			$fieldset->appendChild($label);
			
			$label = Widget::Label('Height (include units)', Widget::Input('config[height]', $config['height']));
			$fieldset->appendChild($label);

			$label = Widget::Label('Save as default', Widget::Input('default', 'on', 'checkbox'));
			$fieldset->appendChild($label);

			$context['form'] = $fieldset;
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
			$key = @file_get_contents($config['keyfile']);
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