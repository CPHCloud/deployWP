<?
/*
Plugin Name: DeployWP
*/

define('WP_DEPLOY_DIR', dirname(__FILE__));
define('WP_DEPLOY_FILES_DIR', WP_DEPLOY_DIR.'/envs');
define('WP_DEPLOY_ENV_DIR', WP_DEPLOY_FILES_DIR.'/'.WP_ENV);

if(!file_exists(WP_DEPLOY_FILES_DIR))
	mkdir(WP_DEPLOY_FILES_DIR);

if(!file_exists(WP_DEPLOY_ENV_DIR))
	mkdir(WP_DEPLOY_ENV_DIR);

class WP_Deploy {

	function __construct(){
		$this->messages = array();
		$this->current_module = '';
	}

	function message($msg){
		$this->messages[$this->current_module][] = array(
			'type' 		=> 'message',
			'message' 	=> $msg
		);
	}


	function error($msg){

		$this->messages[$this->current_module][] = array(
			'type' 		=> 'error',
			'message' 	=> $msg
		);
	}


	function notice($msg){

		$this->messages[$this->current_module][] = array(
			'type' 		=> 'notice',
			'message' 	=> $msg
		);
	}

}

$deployWP = new WP_Deploy();
require('settings.php');

function do_deploy(){
	
	global $deployWP;

	$deployWP->modules = apply_filters('deploy/modules', $deployWP->modules);

	if(is_array($deployWP->modules)){
		foreach($deployWP->modules as $module){
			$file = WP_DEPLOY_DIR.'/modules/'.$module.'.php';
			$deployWP->current_module = $module;
			if(file_exists($file)){
				require($file);
			}
		}
	}

	do_action("deploy/".WP_ENV);
	if(is_admin()){
		do_action("deploy-admin/".WP_ENV);
	}
	else{
		do_action("deploy-front/".WP_ENV);
	}
}
add_action('init', 'do_deploy');


function deploy_notices() {
	global $deployWP;
	foreach($deployWP->messages as $owner => $owner_messages){
		foreach($owner_messages as $message){
			switch ($message['type']) {
				case 'error':
					$type = 'error';
					break;
				
				case 'notice':
					$type = 'update-nag';
					break;
				
				default:
					$type = 'updated';
					break;
			};

		    echo '<div><div class="'.$type.'">
		       	<p>Deploy module <strong>'.$owner.'</strong> : '.$message['message'].'</p>
		    </div></div>';
		}
    }
}
add_action('admin_notices', 'deploy_notices');



/* HELPERS */

/**
 * undocumented function
 *
 * @return void
 * @author Troels Abrahamsen
 **/
function add_deploy_action($envs, $function, $context = false){

	if(!is_array($envs))
		$envs = array($envs);

	$add_context = '';
	if(is_string($context))
		$add_context = '-'.$context;

	foreach($envs as $env){
		add_action("deploy$add_context/$env", $function);
	}	

}
?>