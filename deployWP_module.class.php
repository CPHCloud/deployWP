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

		$this->deploy_from_dir  = WP_DEPLOY_DIR.'/envs/'.$this->deploy_from;
		
	}


}


?>