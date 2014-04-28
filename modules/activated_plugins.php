<?

/*
*********************************
EXAMPLE DEPLOY MODULE
*********************************
*/

class activated_plugins extends deployWP_module {

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function collect(){
		global $deployWP;

		$file 	= WP_DEPLOY_ENV_DIR.'/activated_plugins.json';
		$ap 	= json_encode(get_option('active_plugins'));
		$file	= fopen($file, 'w+');
		fwrite($file, $ap);
		fclose($file);
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function deploy(){
		global $deployWP;
		$file 	= WP_DEPLOY_FILES_DIR.'/local/activated_plugins.json';
		if(file_exists($file)){
			$ap = json_decode(file_get_contents($file));
			if(update_option('active_plugins', $ap)){
				$deployWP->message('Deployed activated plugins');
				unlink($file);
			}
		}
	}
}

?>