<?

/**
 * WP Action
 * This action exports or registers all the ACF fields needed for
 * the application to work properly.
 *
 * @author Troels Abrahamsen
 **/
add_action('deploy-admin/local', function(){
	global $deployWP;

	/* The path to the file that registers the fields */
	$file 	  = WP_DEPLOY_ENV_DIR.'/register-acf-fields.php';

	/* The arguments to get all ACF fields */
	$args = array(
				'numberposts' 		=> -1,
				'post_type' 		=> 'acf',
				'orderby' 			=> 'menu_order title',
				'order' 			=> 'asc',
				'suppress_filters' 	=> false,
			);

	/* Get fields */
	if($acfs = get_posts($args)){
		/* 
		Fields where found.
		Now we need to get an array of their IDs
		*/
		foreach($acfs as &$acf){
			$acf = $acf->ID;
		}

		/* Require the export class of the ACF plugin */
		require_once(WP_PLUGIN_DIR.'/advanced-custom-fields/core/controllers/export.php');

		/*
		This will fool the ACF exporter into believing that
		a POST request with the fields to export has been made.
		*/
		$_POST['acf_posts'] = $acfs;
		
		/* New export object */
		$export = new acf_export();

		/*
		This is so dirty.
		The html_php method outputs the needed html for the wp-admin
		area. We capture that with ob_start and split it by html tags
		in order to find the value of the textarea that holds the PHP
		code we need. Dirty dirty dirty.
		*/
		$buffer = ob_start();
		$export->html_php();
		$contents = ob_get_contents();
		ob_end_clean();

		$contents = preg_split('~readonly="true">~', $contents);
		$contents = preg_split('~</textarea>~', $contents[1]);
		$contents = '<?php '.$contents[0].' ?>';

		/* Write the contents to the file */
		$file = fopen($file, 'w+');
		fwrite($file, $contents);
		fclose($file);

	}
});

/**
 * WP Action
 * 
 *
 * @author Troels Abrahamsen
 **/
add_action('deploy/dev', 'deploy_acf');
add_action('deploy/staging', 'deploy_acf');
add_action('deploy/live', 'deploy_acf');
function deploy_acf(){
	global $deployWP;

	/* The path to the file that registers the fields */
	$file 	  = WP_DEPLOY_ENV_DIR.'/register-acf-fields.php';

	/* We are live - or dev or staging - fetch the file that registers the fields. */
	require_once($file);
}

?>