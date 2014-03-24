<?php
/**
 * Probook strategy for Opauth
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2014 Georgi Georgiev georgi.client@gmail.com
 * @link         http://opauth.org
 * @package      Opauth.ProbookStrategy
 * @license      MIT License
 */


class ProbookStrategy extends OpauthStrategy{
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 * eg. array('client_id', 'client_secret');
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}int_callback',
		'response_type' => 'code',
		'scope' => 'basic',
	);

	/**
	 * Auth request
	 */
	public function request(){

		$url = 'https://probook.bg/auth/authorize';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'tmpl' => 4
		);

		if (!empty($this->strategy['scope'])) $params['scope'] = $this->strategy['scope'];
		if (!empty($this->strategy['state'])) $params['state'] = $this->strategy['state'];
		if (!empty($this->strategy['response_type'])) $params['response_type'] = $this->strategy['response_type'];
		if (!empty($this->strategy['display'])) $params['display'] = $this->strategy['display'];
		if (!empty($this->strategy['auth_type'])) $params['auth_type'] = $this->strategy['auth_type'];
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after Probook's OAuth
	 */
	public function int_callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$url = 'http://probook.bg/auth/token';

			$params = array(
					'grant_type' => 'authorization_code',
					'client_id' => $this->strategy['client_id'],
					'client_secret' => $this->strategy['client_secret'],
					'code' => trim($_GET['code']),
					'tmpl' => 4,
					'redirect_uri' => $this->strategy['redirect_uri'],
			);

			$response = $this->serverPost($url, $params, null);

			$results = json_decode($response, true);

			if (!empty($results) && !empty($results['access_token'])){

				$me = $this->me($results['access_token']);
				$this->auth = array(
					'provider' => 'Probook',
					'uid' => $me->user_id,
					'info' => array(
						'name' => $me->user_first_name." ".$me->user_last_name,
						'image' => $me->user_image,
					),
					'credentials' => array(
						'token' => $results['access_token'],
						'expires' => date('c', time() + $results['expires_in'])
					),
					'raw' => $me
				);
				
				if (!empty($me->user_email)) $this->auth['info']['email'] = $me->user_email;
				if (!empty($me->username)) $this->auth['info']['username'] = $me->username;
				if (!empty($me->user_first_name)) $this->auth['info']['first_name'] = $me->user_first_name;
				if (!empty($me->user_last_name)) $this->auth['info']['last_name'] = $me->user_last_name;
				if (!empty($me->city_name)) $this->auth['info']['location'] = $me->city_name;
				if (!empty($me->user_id)) $this->auth['info']['urls']['probook'] = "http://probook.bg/profile/".$me->user_id;
				if (!empty($me->website)) $this->auth['info']['urls']['website'] = $me->website;
				
				/**
				 * Missing optional info values
				 * - description
				 * - phone: not accessible via Probook Graph API
				 */
				
				$this->callback();
			}
			else{
				$error = array(
					'provider' => 'Probook',
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => $headers
				);

				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'provider' => 'Probook',
				'code' => $_GET['error'],
				'message' => $_GET['error_description'],
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Probook Graph API for user info
	 *
	 * @param string $access_token 
	 * @return array Parsed JSON results
	 */
	private function me($access_token){
		$me = $this->serverGet('http://probook.bg/auth/me', array('oauth_token' => $access_token), null);

		if (!empty($me)){
			return json_decode($me);
		} else{
			$error = array(
				'provider' => 'Probook',
				'code' => 'me_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $me,
					'headers' => null
				)
			);

			$this->errorCallback($error);
		}
	}
}
