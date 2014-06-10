<?php
/*
*********************************
deployWP Module class
*********************************

This is the parent class that all modules must
extend. It provides methods and variables that
are neccesary for any well-planned deployment
module.

Modules that extend this class must provide the
two methods 'collect' and 'deploy'. If the method
need to break the collection or deployment flow,
it must return FALSE. All other return values will
be ignored by the flow.

*/

class deployWP_module {


	/**
	 * Runs on construction and sets the basic variables and
	 * calls any needed methods for setting up the module.
	 *
	 * @return void
	 **/
	function __construct(){

		$this->id 				= 'deployWPmodule'.get_class($this);
		$this->collect_in 		= array('local', 'dev', 'staging');
		$this->deploy_in  		= array('staging', 'dev');
		$this->deploy_from      = 'local';
		$this->collect_on_front = false;
		$this->deploy_on_front 	= false;
		$this->env_dir 			= '';
		$this->env 				= WP_ENV;

		if(!$this->last_deploy = get_transient($this->id.'_last_deploy'))
			$this->last_deploy 	= 0;

		$this->set_deploy_from_dir();
		$this->set_env_dir();
		
	}

	/**
	 * Private function that runs right after collection.
	 * If the child module has the after_collect() method
	 * it will be called.
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function __after_collect(){
		if(method_exists($this, 'after_collect'))
			$this->after_collect();
	}

	/**
	 * Private function that runs right after deployment.
	 * If the child module has the after_deploy() method
	 * it will be called.
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function __after_deploy(){
		set_transient($this->id.'_last_deploy', time());
		if(method_exists($this, 'after_deploy'))
			$this->after_deploy();
	}

	/**
	 * Sets the deploy from dir
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function set_deploy_from_dir(){
		$this->deploy_from_dir  = WP_DEPLOY_FILES_DIR.'/'.$this->deploy_from;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function set_env_dir(){
		$this->env_dir  = WP_DEPLOY_FILES_DIR.'/'.WP_ENV;
	}
}


?>