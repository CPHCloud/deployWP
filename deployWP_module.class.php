<?


class deployWP_module {


	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function __construct(){

		$this->collect_in 		= array('local');
		$this->deploy_in  		= array('staging', 'production');
		$this->collect_on_front = false;
		$this->deploy_on_front 	= false;

	}


}


?>