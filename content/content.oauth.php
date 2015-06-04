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
	class contentExtensionGoogle_analytics_dashboardOauth extends TextPage {

		const SELF_URL = '/extension/google_analytics_dashboard/oauth/';

		public function view() {
			// this loads our class
			$ext = Symphony::ExtensionManager()->create('google_analytics_dashboard');

			// https://developers.google.com/api-client-library/php/auth/web-app
			// https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php
			if (!isset($_GET['code'])) {
				//$auth_url = $client->createAuthUrl();
				//redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
				echo 'Error! ' . General::sanitize($_GET['error']);
			} else {
				
				$client = extension_google_analytics_dashboard::createClient($config);
				$client->setRedirectUri(APPLICATION_URL . self::SELF_URL);
				$client->authenticate($_GET['code']);
				$_SESSION['access_token'] = $client->getAccessToken();
				redirect(APPLICATION_URL . '/extension/dashboard/index/');
			}
			die();
		}
	}
