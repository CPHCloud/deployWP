<?
/**
 * WP Action
 * 
 *
 * @author Troels Abrahamsen
 **/
add_action('deploy-admin/local', function(){

	global $deployWP;

	$file 	= WP_DEPLOY_ENV_DIR.'/activated_plugins.json';
	$ap 	= json_encode(get_option('active_plugins'));
	$file	= fopen($file, 'w+');
	fwrite($file, $ap);
	fclose($file);

});


/**
 * WP Action
 * 
 *
 * @author Troels Abrahamsen
 **/
global $wp_envs;
foreach($wp_envs as $env){
	if($env != 'local')
		add_action("deploy-admin/$env", 'deploy_activated_plugins');
}
function deploy_activated_plugins(){

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

?>