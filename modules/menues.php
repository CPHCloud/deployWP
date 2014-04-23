<?

/**
 * WP Action
 * This action saves the menues that exist locally
 * and saves them in menues.json. On the non-local sites
 * it will retreive the files contents and create the menues
 * and the relevant menu items
 *
 * @author Troels Abrahamsen
 **/
add_action('deploy/local', function(){

	global $deployWP;

	/* Define the file to save the local menues in */
	$file = WP_DEPLOY_ENV_DIR.'/menues.json';

	/* We are in the local environment. Let's roll! */

	/* Get all menues - exit the action if none are found */		
	if(!$menues = get_terms('nav_menu'))
		return false;

	/*
	Loop through the menues and save any menu-items found
	in the 'items' property of the object
	*/
	foreach($menues as &$menu){
		if($items = wp_get_nav_menu_items($menu->slug)){

			foreach($items as &$item){
				if($item->type == 'post_type')
					$item->object = get_post($item->object_id);
			}

			$menu->items = $items;

		}
	}

	/*
	Get menu locations and their corresponding menues
	*/
   if($locations = get_theme_mod('nav_menu_locations')){
		foreach($locations as &$location){
			$location = get_term($location, 'nav_menu');
		}
   }

   /*
   Save all the properties we need in the $data
   variable and convert it to JSON
   */
	$data = json_encode(array(
		'base_url' 	=> WP_BASE_URL,
		'locations' => $locations,
		'menues' 	=> $menues
	));
	
	/* Save the JSON in the file */
	$file = fopen($file, 'w+');
	fwrite($file, $data);
	fclose($file);	
});


/**
 * WP Action
 * Deploys the menues in the right environments
 *
 * @author Troels Abrahamsen
 **/
foreach($wp_env as $env){
	if($env != 'local')
		add_action("deploy-admin/$env", 'deploy_menues');
}
function deploy_menues(){

	global $deployWP;

	/* Define the file to get the menues from */
	$file = WP_DEPLOY_ENV_DIR.'/menues.json';

	/* Check if the file exists before we proceed */
	if(!file_exists($file))
		return false;

	/*
	Attempt to convert the file on record from JSON to an object.
	Exit if we fail.
	*/
	if(!$data = json_decode(file_get_contents($file)))
		return false;

	/* Make sure we have all the info we need */
	if(!$data->menues)
		return false;

	/*
	First remove all saved menues with
	the same slugs as the ones we're adding.
	We do this to make sure our menues are excactly
	the same as the ones from the file.
	*/
	foreach($data->menues as $menu){
		wp_delete_nav_menu($menu->slug);
	}

	/* Now create them again */
	foreach($data->menues as $menu){

		/*
		These two arrays help us track the relationships
		between old items and their new counterparts
		*/
		$old_new = array();
		$need_new_parents = array();

		/* Create the new menu and get the menu object */
		$new_menu = wp_create_nav_menu($menu->name);
		$new_menu = wp_get_nav_menu_object($new_menu);

		/* Save the old ID with a reference to the new one */
		$menu_old_new[$menu->term_id] = $new_menu->term_id;

		/* Check if the menu has any menu items */
		if($menu->items){

			/* Loop through each menu item and create a new item */
			foreach($menu->items as $item){

				/* Default item properties */
				$nitem 							= array();
				$nitem['menu-item-position'] 	= $item->menu_order;
				$nitem['menu-item-type'] 	 	= 'custom';
				$nitem['menu-item-title']		= $item->title;
				$nitem['menu-item-status'] 		= 'publish'; 
				$nitem['menu-item-url']			= str_ireplace($data->base_url, WP_BASE_URL, $item->url);
				$nitem['menu-item-target']		= $item->target;
				$nitem['menu-item-attr-title']	= $item->attr_title;
				$nitem['menu-item-description'] = $item->description;
				$nitem['menu-item-parent-id']   = 0;

				/* Is this an item with an object? */
				if($item->type == 'post_type'){

					/* Yes it is. We need to find it. */

					/* Set the args for retreivng the post */
					$args = array(
						'name' 				=> $item->object->post_name,
						'post_type' 		=> $item->object->post_type,
						'posts_per_page' 	=> 1
					);

					/* Check if the post exists */
					if($posts = get_posts($args)){
						
						/* It does. Add item properties */						
						$nitem['menu-item-type'] 		= 'post_type';
						$nitem['menu-item-object-id'] 	= $posts[0]->ID;
						$nitem['menu-item-object'] 		= $posts[0]->post_type;

					}
					else{
						/* Post does not exist. Send error message. */
						$deployWP->messages[] = array(
							'type' 		=> 'error',
							'message' 	=> 'Missing '.$item->object->post_type.' with name "'.$item->object->post_name.'" when trying to create item for menu "'.$menu->name.'"'
							);
						continue 1;
					}
				}


				if(is_array($item->classes))
					$nitem['menu-item-classes'] = implode(' ', $item->classes);
				
				$new_item_id = wp_update_nav_menu_item($new_menu->term_id, 0, $nitem);

				$nitems[$new_item_id] = $nitem;

				global $wpdb;
				$wpdb->insert( 
				    $wpdb->term_relationships, 
				    array(
				        "object_id" => $new_item_id, 
				        "term_taxonomy_id" => $new_menu->term_taxonomy_id
				    ), 
				    array( "%d", "%d" ) 
				);

				$old_new[$item->ID] = $new_item_id;

				if($item->menu_item_parent)
					$need_new_parents[$new_item_id] = $item->menu_item_parent;

			}
		}

		/* Update menu parent relationships */
		if(is_array($need_new_parents)){
			foreach($need_new_parents as $obj_id => $old_id){

				$new_parent_item = array(
					'menu-item-parent-id' => $old_new[$old_id]
				);

				$new_parent_item = array_merge($nitems[$obj_id], $new_parent_item);

				if(wp_update_nav_menu_item($new_menu->term_id, $obj_id, $new_parent_item))
					$deployWP->message('Deployed menu "'.$new_menu->name.'" succesfully!');
			}
		}
		
	}


	/* Re-add menues to locations */
	if($data->locations){
		foreach($data->locations as $location => $old_menu){
			$new_locations[$location] = $menu_old_new[$old_menu->term_id];
		}
		set_theme_mod('nav_menu_locations', $new_locations);
	}

	/* Remove the menues json file */
	unlink($file);	
}

?>
