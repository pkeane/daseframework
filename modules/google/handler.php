<?php
class Dase_ModuleHandler_Google extends Dase_Handler
{
		public $resource_map = array(
				'/' => 'login',
				'{eid}' => 'login',
		);

		protected function setup($r)
		{
		}

		public function getLogin($r)
		{
				$target = $r->get('target');

				$first = $r->get('openid_ext1_value_firstname');
				$last = $r->get('openid_ext1_value_lastname');
				$email = $r->get('openid_ext1_value_email');

				if (!$email) {

						@$xrds = file_get_contents('https://www.google.com/accounts/o8/id');
						$xml = new SimpleXMLElement($xrds);
						$uri = (string) $xml->XRD->Service->URI;
						$params['openid.return_to'] = $r->app_root . '/login?target='.$target;
						$params['openid.mode'] = 'checkid_setup';
						$params['openid.ns'] = 'http://specs.openid.net/auth/2.0';
						$params['openid.claimed_id'] = 'http://specs.openid.net/auth/2.0/identifier_select';
						$params['openid.identity'] = 'http://specs.openid.net/auth/2.0/identifier_select';
						$params['openid.ns.ax'] = 'http://openid.net/srv/ax/1.0';
						$params['openid.ax.mode'] = 'fetch_request';
						$params['openid.ax.required'] = 'email,firstname,lastname';
						$params['openid.ax.type.email'] = 'http://schema.openid.net/contact/email';
						$params['openid.ax.type.firstname'] = 'http://axschema.org/namePerson/first';
						$params['openid.ax.type.lastname'] = 'http://axschema.org/namePerson/last';
						$params['openid.ns.pape'] = 'http://specs.openid.net/extensions/pape/1.0';
						$params['openid.ns.max_auth_age'] = 0;

						$set = array();
						foreach ($params as $k => $v) {
								$set[] = $k.'='.$v;
						}
						$uri = $uri.'?'.join('&',$set);
						header("Location: $uri");
						exit;

				} else {

						$db_user = $r->getUser('none');
						$eid = str_replace('@','.',$email);
						if (!$db_user->retrieveByEid($eid)) {
								$db_user->eid = strtolower($eid); 
								$db_user->name = $first.' '.$last; 
								$db_user->insert();
						}

						$r->setCookie('eid',$db_user->eid);
						$r->setCookie('max',$db_user->max_items);
						$r->setCookie('display',$db_user->display);

						$db_user->getHttpPassword($r->getAuthToken());
						if ($target) {
								$r->renderRedirect(urldecode($target));
						} else {
								$r->renderRedirect();
						}
				}
		}

		/**
		 * this method will be called
		 * w/ an http delete to '/login' *or* '/login/{eid}'
		 *
		 */
		public function deleteLogin($r)
		{
				$r->clearCookies();
				//$return_to = $r->app_root . '/login';
				//$logout_url = 'http://www.google.com/accounts/Logout?continue='.$return_to;
				$logout_url = 'http://www.google.com/accounts/Logout';
				$r->response_mime_type = 'application/json';
				$json = Dase_Json::get(array('location' => $logout_url));
				$r->renderResponse($json);
		}

}

