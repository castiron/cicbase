<?php

namespace CIC\Cicbase\Formhandler\Interceptor;

/**
 * <code>
 * saveInterceptors.1.class = CIC\Cicbase\Formhandler\Interceptor\SearchEngineQueryGrabber
 * saveInterceptros.1.config.fieldname = myhiddenfieldname
 * </code>
 */
class SearchEngineQueryGrabber extends \Tx_Formhandler_AbstractInterceptor {

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		// TODO: Implement more flexibility as to where the ref URL is stored 
		// capterra: $referringURL = 'http://www.capterra.com/search?query=my+lucky+search';
		// bing: $referringURL = 'http://www.bing.com/search?q=my+lucky+search&go=&qs=n&form=QBLH&pq=my+lucky+search&sc=0-10&sp=-1&sk=';
		// google: $referringURL = 'https://www.google.com/#hl=en&sclient=psy-ab&q=my+lucky+search&oq=my+lucky+search&aq=f&aqi=g-v1g-b1&aql=&gs_l=hp.3..0i15j0i8.1495l3919l0l4391l15l12l0l3l3l0l321l1293l8j2j1j1l15l0.frgbld.&pbx=1&bav=on.2,or.r_gc.r_pw.r_qf.,cf.osb&fp=3a4c3f9900aaf4a9&biw=1676&bih=952';
		$referringURL = $_COOKIE['acref']; 

		if(substr_count($referringURL, "www.capterra.com") > 0) {
			$searchEngine = 'capterra';
		} elseif(substr_count($referringURL, "www.bing.com") > 0) {
			$searchEngine = 'bing';
		} elseif(substr_count($referringURL, "www.google.com") > 0) {
			$searchEngine = 'google';
			// workaround for google's funny '? -> #' trick
			$referringURL = str_replace("#", "?",$referringURL);
		} else {
			return $this->gp;
		}

		$parsed = parse_url($referringURL, PHP_URL_QUERY);
		parse_str($parsed, $query);
		$searchPhrase = null;
		switch($searchEngine) {
			case 'capterra':
				$searchPhrase = $query['query'];
				break;
			case 'bing':
			case 'google':
				$searchPhrase = $query['q'];
				break;
		}

		if($this->settings['searchTermsFieldname']) $this->gp[$this->settings['searchTermsFieldname']] = $searchPhrase;
		if($this->settings['referringUrlFieldname']) $this->gp[$this->settings['referringUrlFieldname']] = $referringURL;
		return $this->gp;
	}
}
?>
