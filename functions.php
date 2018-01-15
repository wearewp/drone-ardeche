<?php
//	Deactivate the LayerSlider plugin	
add_theme_support('deactivate_layerslider');

//	Suppression des titres h dans les widgets
add_action( 'widgets_init', '_wearewp_unregister_sidebar', 11 );
function _wearewp_unregister_sidebar() {
	unregister_sidebar('av_everywhere');
	unregister_sidebar('av_blog');
	unregister_sidebar('av_pages');
	if(class_exists( 'woocommerce' )) {
		unregister_sidebar('av_shop_overview');
		unregister_sidebar('av_shop_single');
	}
	
	$footer_columns = avia_get_option('footer_columns','5');
	for ($i = 1; $i <= $footer_columns; $i++) {
		unregister_sidebar('av_footer_'.$i);
	}
	
}

add_action( 'widgets_init', '_wearewp_register_sidebar', 12 );
function _wearewp_register_sidebar() {
	
	register_sidebar(array(
		'name' => 'Displayed Everywhere',
		'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
		'after_widget' => '<span class="seperator extralight-border"></span></section>', 
		'before_title' => '<div class="widgettitle">', 
		'after_title' => '</div>',
		'id'=>'av_everywhere'
	));

	register_sidebar(array(
		'name' => 'Sidebar Blog',
		'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
		'after_widget' => '<span class="seperator extralight-border"></span></section>', 
		'before_title' => '<div class="widgettitle">', 
		'after_title' => '</div>', 
		'id'=>'av_blog'
	));
		
	register_sidebar(array(
		'name' => 'Sidebar Pages',
		'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
		'after_widget' => '<span class="seperator extralight-border"></span></section>', 
		'before_title' => '<div class="widgettitle">', 
		'after_title' => '</div>', 
		'id'=>'av_pages'
	));

	
	if(class_exists( 'woocommerce' )) {
		
		register_sidebar(array(
			'name' => 'Shop Overview Page',
			'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
			'after_widget' => '<span class="seperator extralight-border"></span></section>', 
			'before_title' => '<div class="widgettitle">', 
			'after_title' => '</div>', 
			'id'=>'av_shop_overview'
		));
	
	
	
		register_sidebar(array(
			'name' => 'Single Product Pages',
			'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
			'after_widget' => '<span class="seperator extralight-border"></span></section>', 
			'before_title' => '<div class="widgettitle">', 
			'after_title' => '</div>', 
			'id'=>'av_shop_single'
		));
		
	}
		


	
	//dynamic widgets
	
	#footer
	$footer_columns = avia_get_option('footer_columns','5');
	
	for ($i = 1; $i <= $footer_columns; $i++) {
		register_sidebar(array(
			'name' => 'Footer - column'.$i,
			'before_widget' => '<section id="%1$s" class="widget clearfix %2$s">', 
			'after_widget' => '<span class="seperator extralight-border"></span></section>', 
			'before_title' => '<div class="widgettitle">', 
			'after_title' => '</div>', 
			'id'=>'av_footer_'.$i
		));
	}
	
}



class avia_post_slider
{	
	static  $slide = 0;
	protected $atts;
	protected $entries;

	function __construct($atts = array())
	{
		$this->atts = shortcode_atts(array(	'type'		=> 'slider', // can also be used as grid
											'style'		=> '', //no_margin
											'columns' 	=> '4',
											'items' 	=> '16',
											'taxonomy'  => 'category',
											'wc_prod_visible'	=>	'',
											'prod_order_by'		=>	'',
											'prod_order'		=>	'',
											'post_type'=> get_post_types(),
											'contents' 	=> 'excerpt',
											'preview_mode' => 'auto',
											'image_size' => 'portfolio',
											'autoplay'  => 'no',
											'animation' => 'fade',
											'paginate'	=> 'no',
											'use_main_query_pagination' => 'no',
											'interval'  => 5,
											'class'		=> '',
											'categories'=> array(),
											'custom_query'=> array(),
											'offset' => 0,
											'custom_markup' => '',
											'av_display_classes' => ''
											), $atts, 'av_postslider');
											
						
	}

	public function html()
	{
		global $avia_config;

		$output = "";

		if(empty($this->entries) || empty($this->entries->posts)) return $output;

		avia_post_slider::$slide ++;
		extract($this->atts);

		if($preview_mode == 'auto') $image_size = 'portfolio';
		$extraClass 		= 'first';
		$grid 				= 'one_third';
		$post_loop_count 	= 1;
		$loop_counter		= 1;
		$autoplay 			= $autoplay == "no" ? false : true;
		$total				= $columns % 2 ? "odd" : "even";
		$blogstyle 			= function_exists('avia_get_option') ? avia_get_option('blog_global_style','') : "";
		$excerpt_length 	= 60;
		
		
		if($blogstyle !== "")
		{
			$excerpt_length = 240;
		}
		
		switch($columns)
		{
			case "1": $grid = 'av_fullwidth';  if($preview_mode == 'auto') $image_size = 'large'; break;
			case "2": $grid = 'av_one_half';   break;
			case "3": $grid = 'av_one_third';  break;
			case "4": $grid = 'av_one_fourth'; if($preview_mode == 'auto') $image_size = 'portfolio_small'; break;
			case "5": $grid = 'av_one_fifth';  if($preview_mode == 'auto') $image_size = 'portfolio_small'; break;
		}


		$data = AviaHelper::create_data_string(array('autoplay'=>$autoplay, 'interval'=>$interval, 'animation' => $animation, 'show_slide_delay'=>90));

		$thumb_fallback = "";
		$markup = avia_markup_helper(array('context' => 'blog','echo'=>false, 'custom_markup'=>$custom_markup));
		$output .= "<div {$data} class='avia-content-slider avia-content-{$type}-active avia-content-slider".avia_post_slider::$slide." avia-content-slider-{$total} {$class} {$av_display_classes}' $markup>";
		$output .= 		"<div class='avia-content-slider-inner'>";

			foreach ($this->entries->posts as $entry)
			{
				$the_id 	= $entry->ID;
				$parity		= $loop_counter % 2 ? 'odd' : 'even';
				$last       = $this->entries->post_count == $post_loop_count ? " post-entry-last " : "";
				$post_class = "post-entry post-entry-{$the_id} slide-entry-overview slide-loop-{$post_loop_count} slide-parity-{$parity} {$last}";
				$link		= get_post_meta( $the_id ,'_portfolio_custom_link', true ) != "" ? get_post_meta( $the_id ,'_portfolio_custom_link_url', true ) : get_permalink( $the_id );
				$excerpt	= "";
				$title  	= '';
				$show_meta  = !is_post_type_hierarchical($entry->post_type);
				$commentCount = get_comments_number($the_id);
				$thumbnail  = get_the_post_thumbnail( $the_id, $image_size );
				$format 	= get_post_format( $the_id );
				if(empty($format)) $format = "standard";

				if($thumbnail)
				{
					$thumb_fallback = $thumbnail;
					$thumb_class	= "real-thumbnail";
				}
				else
				{
					$thumbnail = "<span class=' fallback-post-type-icon' ".av_icon_string($format)."></span><span class='slider-fallback-image'>{{thumbnail}}</span>";
					$thumb_class	= "fake-thumbnail";
				}


				$permalink = '<div class="read-more-link"><a href="'.get_permalink($the_id).'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow"></span></a></div>';
				$prepare_excerpt = !empty($entry->post_excerpt) ? $entry->post_excerpt : avia_backend_truncate($entry->post_content, apply_filters( 'avf_postgrid_excerpt_length' , $excerpt_length) , apply_filters( 'avf_postgrid_excerpt_delimiter' , " "), "…", true, '');

						if($format == 'link')
						{
								$current_post = array();
								$current_post['content'] = $entry->post_content;
								$current_post['title'] =  $entry->post_title;
								
								if(function_exists('avia_link_content_filter'))
								{
									$current_post = avia_link_content_filter($current_post);
								}
		
								$link = $current_post['url'];
							}
						
				
				switch($contents)
				{
					case "excerpt":
							$excerpt = $prepare_excerpt;
							$title = $entry->post_title;
							break;
					case "excerpt_read_more":
							$excerpt = $prepare_excerpt;
							$excerpt .= $permalink;
							$title = $entry->post_title;
							break;
					case "title":
							$excerpt = '';
							$title = $entry->post_title;
							break;
					case "title_read_more":
							$excerpt = $permalink;
							$title = $entry->post_title;
							break;
					case "only_excerpt":
							$excerpt = $prepare_excerpt;
							$title = '';
							break;
					case "only_excerpt_read_more":
							$excerpt = $prepare_excerpt;
							$excerpt .= $permalink;
							$title = '';
							break;
					case "no":
							$excerpt = '';
							$title = '';
							break;
				}
				
				$title = apply_filters( 'avf_postslider_title', $title, $entry );
				
				if($loop_counter == 1) $output .= "<div class='slide-entry-wrap'>";
				
				$post_format = get_post_format($the_id) ? get_post_format($the_id) : 'standard';
				
				$markup = avia_markup_helper(array('context' => 'entry','echo'=>false, 'id'=>$the_id, 'custom_markup'=>$custom_markup));
				$output .= "<article class='slide-entry flex_column {$style} {$post_class} {$grid} {$extraClass} {$thumb_class}' $markup>";
				$output .= $thumbnail ? "<a href='{$link}' data-rel='slide-".avia_post_slider::$slide."' class='slide-image' title=''>{$thumbnail}</a>" : "";
				
				if($post_format == "audio")
				{	
					$current_post = array();
					$current_post['content'] = $entry->post_content;
					$current_post['title'] =  $entry->post_title;
					
					$current_post = apply_filters( 'post-format-'.$post_format, $current_post );
					
					if(!empty( $current_post['before_content'] )) $output .= '<div class="big-preview single-big audio-preview">'.$current_post['before_content'].'</div>';
				}
				
				$output .= "<div class='slide-content'>";

				$markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'id'=>$the_id, 'custom_markup'=>$custom_markup));
				$output .= '<header class="entry-content-header">';
				$meta_out = "";
				
				if (!empty($title))
				{
					if($show_meta)
					{
						$taxonomies  = get_object_taxonomies(get_post_type($the_id));
						$cats = '';
						$excluded_taxonomies = array_merge( get_taxonomies( array( 'public' => false ) ), array('post_tag','post_format') );
						$excluded_taxonomies = apply_filters('avf_exclude_taxonomies', $excluded_taxonomies, get_post_type($the_id), $the_id);
		
						if(!empty($taxonomies))
						{
							foreach($taxonomies as $taxonomy)
							{
								if(!in_array($taxonomy, $excluded_taxonomies))
								{
									$cats .= get_the_term_list($the_id, $taxonomy, '', ', ','').' ';
								}
							}
						}
						
						if(!empty($cats))
						{
							$meta_out .= '<span class="blog-categories minor-meta">';
							$meta_out .= $cats;
							$meta_out .= '</span>';
						}
					}
					
					/**
					 * Allow to change default output of categories - by default supressed for setting Default(Business) blog style
					 * 
					 * @since 4.0.6
					 * @param string $blogstyle						'' | 'elegant-blog' | 'elegant-blog modern-blog'
					 * @param avia_post_slider $this
					 * @return string								'show_elegant' | 'show_business' | 'use_theme_default' | 'no_show_cats' 
					 */
					$show_cats = apply_filters( 'avf_postslider_show_catergories', 'use_theme_default', $blogstyle, $this );
					
					switch( $show_cats )
					{
						case 'no_show_cats':
							$new_blogstyle = '';
							break;
						case 'show_elegant':
							$new_blogstyle = 'elegant-blog';
							break;
						case 'show_business':
							$new_blogstyle = 'elegant-blog modern-blog';
							break;
						case 'use_theme_default':
						default:
							$new_blogstyle = $blogstyle;
							break;
					}
					
						//	elegant style
					if( ( strpos( $new_blogstyle, 'modern-blog' ) === false ) && ( $new_blogstyle != "" ) )
					{
						$output .= $meta_out;
					}
					
					$output .=  "<h2 class='slide-entry-title entry-title' $markup><a href='{$link}' title='".esc_attr(strip_tags($title))."'>".$title."</a></h2>";
					
						//	modern business style
					if( ( strpos( $new_blogstyle, 'modern-blog' ) !== false ) && ( $new_blogstyle != "" ) ) 
					{
						$output .= $meta_out;
					}
					
					$output .= '<span class="av-vertical-delimiter"></span>';
				}
				
				$output .= '</header>';

				if($show_meta && !empty($excerpt))
				{
					$meta  = "<div class='slide-meta'>";
					if ( $commentCount != "0" || comments_open($the_id) && $entry->post_type != 'portfolio')
					{
						$link_add = $commentCount === "0" ? "#respond" : "#comments";
						$text_add = $commentCount === "1" ? __('Comment', 'avia_framework' ) : __('Comments', 'avia_framework' );

						$meta .= "<div class='slide-meta-comments'><a href='{$link}{$link_add}'>{$commentCount} {$text_add}</a></div><div class='slide-meta-del'>/</div>";
					}
					$markup = avia_markup_helper(array('context' => 'entry_time','echo'=>false, 'id'=>$the_id, 'custom_markup'=>$custom_markup));
					$meta .= "<time class='slide-meta-time updated' $markup>" .get_the_time(get_option('date_format'), $the_id)."</time>";
					$meta .= "</div>";
					
					if( strpos($blogstyle, 'elegant-blog') === false )
					{
						$output .= $meta;
						$meta = "";
					}
				}
				$markup = avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'id'=>$the_id, 'custom_markup'=>$custom_markup));
				$excerpt = apply_filters( 'avf_post_slider_entry_excerpt', $excerpt, $prepare_excerpt, $permalink, $entry );
				$output .= !empty($excerpt) ? "<div class='slide-entry-excerpt entry-content' $markup>".$excerpt."</div>" : "";

				$output .= "</div>";
				$output .= '<footer class="entry-footer">';
				if( !empty($meta) ) $output .= $meta;
				$output .= '</footer>';
				
				$output .= av_blog_entry_markup_helper( $the_id );
				
				$output .= "</article>";

				$loop_counter ++;
				$post_loop_count ++;
				$extraClass = "";

				if($loop_counter > $columns)
				{
					$loop_counter = 1;
					$extraClass = 'first';
				}

				if($loop_counter == 1 || !empty($last))
				{
					$output .="</div>";
				}
			}

		$output .= 		"</div>";

		if($post_loop_count -1 > $columns && $type == 'slider')
		{
			$output .= $this->slide_navigation_arrows();
		}
		
		global $wp_query;
		if($use_main_query_pagination == 'yes' && $paginate == "yes")
		{
			$avia_pagination = avia_pagination($wp_query->max_num_pages, 'nav');
		}
		else if($paginate == "yes")
		{
			$avia_pagination = avia_pagination($this->entries, 'nav');
		}

		if(!empty($avia_pagination)) $output .= "<div class='pagination-wrap pagination-slider'>{$avia_pagination}</div>";


		$output .= "</div>";

		$output = str_replace('{{thumbnail}}', $thumb_fallback, $output);

		wp_reset_query();
		return $output;
	}

	protected function slide_navigation_arrows()
	{
		$html  = "";
		$html .= "<div class='avia-slideshow-arrows avia-slideshow-controls'>";
		$html .= 	"<a href='#prev' class='prev-slide' ".av_icon_string('prev_big').">".__('Previous','avia_framework' )."</a>";
		$html .= 	"<a href='#next' class='next-slide' ".av_icon_string('next_big').">".__('Next','avia_framework' )."</a>";
		$html .= "</div>";

		return $html;
	}

	//fetch new entries
	public function query_entries($params = array())
	{	
		global $avia_config;

		if(empty($params)) $params = $this->atts;

		if(empty($params['custom_query']))
		{
			$query = array();

			if(!empty($params['categories']))
			{
				//get the portfolio categories
				$terms 	= explode(',', $params['categories']);
			}

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
			if(!$page || $params['paginate'] == 'no') $page = 1;

			//if we find no terms for the taxonomy fetch all taxonomy terms
			if(empty($terms[0]) || is_null($terms[0]) || $terms[0] === "null")
			{
				$terms = array();
				$allTax = get_terms( $params['taxonomy']);
				foreach($allTax as $tax)
				{
					$terms[] = $tax->term_id;
				}

			}

			if($params['offset'] == 'no_duplicates')
			{
				$params['offset'] = false;
				$no_duplicates = true;
			}
			
			
			//wordpress 4.4 offset fix
			if( $params['offset'] == 0 )
			{
				$params['offset'] = false;
			}
			else
			{	
				//if the offset is set the paged param is ignored. therefore we need to factor in the page number
				$params['offset'] = $params['offset'] + ( ($page -1 ) * $params['items']);
			}
			
			
			if(empty($params['post_type'])) $params['post_type'] = get_post_types();
			if(is_string($params['post_type'])) $params['post_type'] = explode(',', $params['post_type']);

			$orderby = 'date';
			$order = 'DESC';
			
			// Meta query - replaced by Tax query in WC 3.0.0
			$meta_query = array();
			$tax_query = array();

			// check if taxonomy are set to product or product attributes
			$tax = get_taxonomy( $params['taxonomy'] );
			
			if( is_object( $tax ) && isset( $tax->object_type ) && in_array( 'product', (array) $tax->object_type ) )
			{
				$avia_config['woocommerce']['disable_sorting_options'] = true;
				
				avia_wc_set_out_of_stock_query_params( $meta_query, $tax_query, $params['wc_prod_visible'] );
				
					//	sets filter hooks !!
				$ordering_args = avia_wc_get_product_query_order_args( $params['prod_order_by'], $params['prod_order'] );
						
				$orderby = $ordering_args['orderby'];
				$order = $ordering_args['order'];
			}

			if( ! empty( $terms ) )
			{
				$tax_query[] =  array(
									'taxonomy' 	=>	$params['taxonomy'],
									'field' 	=>	'id',
									'terms' 	=>	$terms,
									'operator' 	=>	'IN'
							);
			}				
			
			$query = array(	'orderby'		=>	$orderby,
							'order'			=>	$order,
							'paged'			=>	$page,
							'post_type'		=>	$params['post_type'],
//								'post_status'	=>	'publish',
							'offset'		=>	$params['offset'],
							'posts_per_page' =>	$params['items'],
							'post__not_in'	=>	( ! empty( $no_duplicates ) ) ? $avia_config['posts_on_current_page'] : array(),
							'meta_query'	=>	$meta_query,
							'tax_query'		=>	$tax_query
						);
															
		}
		else
		{
			$query = $params['custom_query'];
		}


		$query = apply_filters('avia_post_slide_query', $query, $params);

		@$this->entries = new WP_Query( $query ); //@ is used to prevent errors caused by wpml

		// store the queried post ids in
		if( $this->entries->have_posts() )
		{
			while( $this->entries->have_posts() )
			{
				$this->entries->the_post();
				$avia_config['posts_on_current_page'][] = get_the_ID();
			}
		}
		
		if( function_exists( 'WC' ) )
		{
			avia_wc_clear_catalog_ordering_args_filters();
			$avia_config['woocommerce']['disable_sorting_options'] = false;
		}
	}
}



function avia_which_archive()
{
	$output = "";

	if ( is_category() )
	{
		$output = single_cat_title('',false);
	}
	elseif (is_day())
	{
		$output = get_the_time( __('F jS, Y','avia_framework') );
	}
	elseif (is_month())
	{
		$output = get_the_time( __('F, Y','avia_framework') );
	}
	elseif (is_year())
	{
		$output = get_the_time( __('Y','avia_framework') );
	}
	elseif (is_search())
	{
		global $wp_query;
		if(!empty($wp_query->found_posts))
		{
			if($wp_query->found_posts > 1)
			{
				$output =  $wp_query->found_posts ." ". __('search results for:','avia_framework')." ".esc_attr( get_search_query() );
			}
			else
			{
				$output =  $wp_query->found_posts ." ". __('search result for:','avia_framework')." ".esc_attr( get_search_query() );
			}
		}
		else
		{
			if(!empty($_GET['s']))
			{
				$output = __('Search results for:','avia_framework')." ".esc_attr( get_search_query() );
			}
			else
			{
				$output = __('To search the site please enter a valid term','avia_framework');
			}
		}

	}
	elseif (is_author())
	{
		$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));

		if(isset($curauth->nickname) && isset($curauth->ID))
		{
			$output = apply_filters('avf_author_nickname', $curauth->nickname, $curauth->ID);
		}

	}
	elseif (is_tag())
	{
		$output = single_tag_title('',false);
	}
	elseif(is_tax())
	{
		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		$output = $term->name;
	}
	else
	{
		$output = __('Archives','avia_framework')." ";
	}

	if (isset($_GET['paged']) && !empty($_GET['paged']))
	{
		$output .= " (".__('Page','avia_framework')." ".$_GET['paged'].")";
	}

		$output = apply_filters('avf_which_archive_output', $output);
		
	return $output;
}