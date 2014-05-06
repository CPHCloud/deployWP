<?php

/*
*********************************
EXAMPLE DEPLOY MODULE
*********************************
*/

class deploy_example extends deployWP_module {

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function collect(){
		echo 'collect';
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Troels Abrahamsen
	 **/
	function deploy(){
		echo 'deploy';
	}
}


?>