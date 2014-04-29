<?


class deployWP_module {


	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function __construct(){

		$this->collect_in 		= array('local', 'staging', 'production');
		$this->deploy_in  		= array('staging');
		$this->deploy_from      = 'local';
		$this->collect_on_front = false;
		$this->deploy_on_front 	= false;
		$this->env_dir 			= '';

		$this->set_deploy_from_dir();
		
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function set_deploy_from_dir(){
		$this->deploy_from_dir  = WP_DEPLOY_FILES_DIR.'/'.$this->deploy_from;
	}


}


?>