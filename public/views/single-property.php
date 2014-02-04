<?php

get_header();

$obj = MH_Deal_Manager::get_instance();
$slug = $obj->get_plugin_slug();

require_once( MHDM_PLUGIN_DIR . '/admin/includes/plugin/meta-box/meta-box.php' );


?>

<div id="main-content">
	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="mhdm_main_title">
						<h1><?php the_title(); ?></h1>
						<span class="mhdm_deal_tags"><?php echo get_the_term_list( get_the_ID(), 'deal_state', '', ', ' ); ?></span>
					</div>

					<div class="entry-content">
						<?php the_content(); ?>
					</div> <!-- .entry-content -->
					
					<!-- .meta -->
					<?php 
					/*
						$data['MLS'] = rwmb_meta( $slug . '_mls' ); 
						$data['Listing_Agent'] = rwmb_meta( $slug . '_listing_agent' ); 
						$data['Buyer_Agent'] = rwmb_meta( $slug . '_buyer_agent' ); 
						$data['Price'] = rwmb_meta( $slug . '_price' );
						$data['Legal_Description'] = rwmb_meta( $slug . '_lot' ); 
						$data['Address'] = rwmb_meta( $slug . '_address' ); 
						$data['City'] = rwmb_meta( $slug . '_city' ); 
						$data['Province'] = rwmb_meta( $slug . '_province' ); 
						$data['Postal_Code'] = rwmb_meta( $slug . '_postal_code' ); 
						$data['Province'] = rwmb_meta( $slug . '_province' ); 
						$data['Country'] = rwmb_meta( $slug . '_country' ); 
					*/
						$data['MLS'] = get_post_meta( get_the_id(), $slug . '_mls', true ); 
						$data['Listing_Agent'] = get_post_meta( get_the_id(),$slug . '_listing_agent', true ); 
						$data['Buyer_Agent'] = get_post_meta( get_the_id(),$slug . '_buyer_agent', true ); 
						$data['Price'] = get_post_meta( get_the_id(),$slug . '_price', true );
						$data['Legal_Description'] = get_post_meta( get_the_id(),$slug . '_lot', true ); 
						$data['Address'] = get_post_meta( get_the_id(),$slug . '_address', true ); 
						$data['City'] = get_post_meta( get_the_id(),$slug . '_city', true ); 
						$data['Province'] = get_post_meta( get_the_id(),$slug . '_province', true ); 
						$data['Postal_Code'] = get_post_meta( get_the_id(),$slug . '_postal_code', true ); 
						$data['Province'] = get_post_meta( get_the_id(),$slug . '_province', true ); 
						$data['Country'] = get_post_meta( get_the_id(),$slug . '_country', true );
						
						$args = array(
									'post_type' => 'associate',
									'post__in' => $data['Listing_Agent']
								);

						$q = new WP_Query( $args );
					
						$data['Listing_Agent'] = '';
						while ( $q->have_posts() ) : $q->the_post();
							$data['Listing_Agent'] .= get_the_title();
						endwhile;
						
						$args = array(
									'post_type' => 'associate',
									'post__in' => $data['Buyer_Agent']
								);

						$q = new WP_Query( $args );
					
						$data['Buyer_Agent'] = '';
						while ( $q->have_posts() ) : $q->the_post();
							$data['Buyer_Agent'] .= get_the_title();
						endwhile;
					
					?>
					<h3>Property Details:</h3>
					<ul>
					<?php foreach( $data as $k => $v ){
						echo '<li>' . $k . ' : '. $v . '</li>';	
					}
					?>
					<ul>
					
										<?php // reset the post data ?>
					<?php wp_reset_postdata(); ?>
		
							
				</article> <!-- .et_pb_post -->

			
			<?php endwhile; ?>


			</div> <!-- #left-area -->


			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->


</div> <!-- #main-content -->

<?php get_footer(); ?>