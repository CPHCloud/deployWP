<?
/*
Plugin Name: DeployWP
*/

define('WP_DEPLOY_DIR', dirname(__FILE__));
define('WP_DEPLOY_CONTENT_DIR', WP_CONTENT_DIR.'/deployWP');
define('WP_DEPLOY_FILES_DIR', WP_DEPLOY_CONTENT_DIR.'/envs');
define('WP_DEPLOY_ENV_DIR', WP_DEPLOY_FILES_DIR.'/'.WP_ENV);

/* Create neccesary files */
if(!file_exists(WP_DEPLOY_CONTENT_DIR))
	mkdir(WP_DEPLOY_CONTENT_DIR);

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

	require_once(WP_DEPLOY_DIR.'/deployWP_module.class.php');

	if(is_array($deployWP->modules)){

		foreach($deployWP->modules as $k => $module){
			$deployWP->modules[$module] = WP_DEPLOY_DIR.'/modules/'.$module.'.php';
			unset($deployWP->modules[$k]);
		}

		$deployWP->modules = apply_filters('deployWP/modules', $deployWP->modules);

		foreach($deployWP->modules as $module => $file){

			$module_name 				= $module;

			if(file_exists($file)){
				
				require($file);
				$classname 	= 'deploy_'.$module_name;
				$module 	= new $classname();

				$deployWP->current_module 	= $module_name;
				
				$module = apply_filters('deployWP/pre', $module);
				$module = apply_filters('deployWP/pre/'.$module_name, $module);

				if(in_array(WP_ENV, $module->collect_in)){
					
					$module = apply_filters('deployWP/collect', $module);
					$module = apply_filters('deployWP/collect/'.$module_name, $module);
					
					if(!$module->collect_on_front){
						if(is_admin())
							$module->collect();
					}
					else{
						$module->collect();
					}
				}
				
				if(in_array(WP_ENV, $module->deploy_in)){
					
					$module = apply_filters('deployWP/deploy', $module);
					$module = apply_filters('deployWP/deploy/'.$module_name, $module);

					if(!$module->deploy_on_front){
						if(is_admin())
							$module->deploy();
					}
					else{
						$module->deploy();
					}
				}

			}
		}
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