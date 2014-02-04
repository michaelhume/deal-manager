<?php

get_header();

$obj = MH_Deal_Manager::get_instance();
$slug = $obj->get_plugin_slug();

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
					
					<!-- .client-list -->
					<?php // set up a new query to get all of the related clients 
					
						$meta = get_post_meta( get_the_id() );
						
						?>
						<pre>
							<?php print_r($meta); ?>
						</pre>
						<?php $clients = false; 
						
						$args = array(
									'include' => unserialize ( array_pop( $meta[ $slug . '_client' ] ) ),
								);

						$clients = get_users( $args );
						
					?>
					
					<?php if ( $clients ): ?>
						Clients: 
						<ul>
						<?php foreach ( $clients as $client ){ ?>
							
							<li><?php echo $client->display_name; ?></li>
							
						<?php } ?>
						</ul>
					<?php endif; ?>
					
					<!-- .requirement-list -->
					<?php // set up a new query to get all of the related requirements 
						$id = get_the_id();
						$args = array(
									'post_type' => 'requirement',
									'meta_query' => array(
											array(	'key' => $slug . '_deal',
													'value' => $id,
											)
									)
								);

						$req_query = new WP_Query( $args );
					?>
					
					<?php if ( $req_query->have_posts() ): ?>
						Requirements: 
						<ul>
						<?php while ( $req_query->have_posts() ) : $req_query->the_post(); ?>
						
							<li><?php the_title(); ?></li>
							
						<?php endwhile; ?>
						</ul>
					<?php endif; ?>
					
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