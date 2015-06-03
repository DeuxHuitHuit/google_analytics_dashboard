<?php
	/*
	Copyight: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
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
				'src' => '/',
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

			$fieldset = new XMLElement('fieldset', NULL, array('class' => 'settings'));
			$fieldset->appendChild(new XMLElement('legend', 'Google Analytics Options'));

			$label = Widget::Label('Height (include units)', Widget::Input('config[height]', $config['height']));
			$fieldset->appendChild($label);

			$context['form'] = $fieldset;
		}

		public function dashboard_panel_validate($context) {
			if ($context['type'] != self::PANEL_NAME) {
				return;
			}
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