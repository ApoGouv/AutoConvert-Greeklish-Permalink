<?php

function auto_gr_permalinks_settings_init() {
	register_setting( 'auto_gr_permalinks', 'auto_gr_permalinks_dipthongs' );
	add_settings_section(
		'auto_gr_permalinks_settings',
		__( 'Settings', 'auto_gr_permalinks' ),
		'auto_gr_permalinks_cb',
		'auto_gr_permalinks'
	);
	add_settings_field(
		'auto_gr_permalinks_dipthongs',
		__( 'Do you want dipthongs to be converted?', 'auto_gr_permalinks' ),
		'auto_gr_permalinks_dipthongs_cb',
		'auto_gr_permalinks',
		'auto_gr_permalinks_settings'
	);
}

add_action( 'admin_init', 'auto_gr_permalinks_settings_init' );

function auto_gr_permalinks_cb() {}

function auto_gr_permalinks_dipthongs_cb() {
	$options = get_option( 'auto_gr_permalinks_dipthongs' );
	$dipthongs = $options === 'enabled' ? 'checked' : '';
	?>
	<input type="checkbox" id="auto_gr_permalinks_dipthongs" name="auto_gr_permalinks_dipthongs" value="enabled" <?php echo $dipthongs; ?>>
	<span><?php _e('For example "ει", "οι" becomes "i", "μπ" becomes "b" etc'); ?></span>
	<?php
}
	
add_action( 'admin_menu', 'auto_gr_permalinks_settings_page' );


function auto_gr_permalinks_settings_page() {
	add_options_page( 
		__( 'AutoConvert Greeklish Permalinks', 'auto_gr_permalinks' ),
		__( 'AutoConvert Greeklish Permalinks', 'auto_gr_permalinks' ),
		'manage_options',
		'auto_gr_permalinks',
		'auto_gr_permalinks_admin_page'
	);
}

function auto_gr_permalinks_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<?php
			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'permalink_settings';
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=auto_gr_permalinks&tab=permalink_settings" class="nav-tab <?php echo $active_tab == 'permalink_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'auto_gr_permalinks'); ?></a>
			<a href="?page=auto_gr_permalinks&tab=generate_permalinks" class="nav-tab <?php echo $active_tab == 'generate_permalinks' ? 'nav-tab-active' : ''; ?>"><?php _e('Generate Greeklish Permalinks', 'auto_gr_permalinks'); ?></a>
		</h2>
		<?php
		if( $active_tab == 'permalink_settings' ) { ?>
			<div>
				<form action="options.php" method="post">
					<?php settings_fields( 'auto_gr_permalinks' ); ?>
					<?php do_settings_sections( 'auto_gr_permalinks' ); ?>
					<?php submit_button( __('Save Settings', 'auto_gr_permalinks' ) ); ?>
				</form>
			</div>
		<?php  } else { ?>
			<div>
				<h2><?php _e('Generate Greeklish Permalinks', 'auto_gr_permalinks'); ?></h2>
				<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>" method="post">
					<table class="form-table">
						<tbody>
							<tr>
								<th>
									<label for="post-type[]"><?php _e('Choose post types to convert', 'auto_gr_permalinks'); ?></label>
								</th>
								<td>
									<select name="post-type[]" multiple required>
									<?php
										$args = array(
											'public' => true,
										);
										$post_types = get_post_types( $args, 'names' );
									?>
									<?php foreach ( $post_types as $post_type ) : ?>
										<option value="<?php esc_attr_e( $post_type ); ?>">
											<?php _e( $post_type , 'auto_gr_permalinks'); ?>
										</option>
									<?php endforeach; ?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<input name="convert-button" type="hidden" value="1" />
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Generate Greeklish Permalinks', 'auto_gr_permalinks'); ?>">
					</p>
				</form>
			</div>
		<?php } ?>
	</div>
	<?php
	if ( isset( $_POST['convert-button'] ) && isset( $_POST['post-type'] ) ) {
		$query = new WP_Query( array(
			'post_type'      =>  $_POST['post-type'],
			'posts_per_page' => -1,
		));
		$update_count = 0;
		foreach ( $query->posts as $post ) {
			setlocale(LC_ALL, 'el_GR');
			$current_post_name = urldecode($post->post_name);
			if ( ! auto_gr_permalinks_is_valid_slug( $current_post_name ) ) {
				if ( ! $update_count ) {
					echo '<h4>'. __('Permalinks updated', 'auto_gr_permalinks') . ':</h4><pre style="width:100%;max-width:800px;max-height:250px;overflow-y:scroll;background: white;border: 1px solid #e0e0e0;padding:  8px 14px;">';
				}
				$post_to_update = array();
				$post_to_update['ID'] = $post->ID;
				$post_to_update['post_name'] = auto_gr_permalinks_sanitize_title( $post->post_title );
				wp_update_post( $post_to_update );
				$update_count++;
				echo $current_post_name . '->' . urldecode($post_to_update['post_name']) . '<br/>';
			}
		}
		echo '</pre><p><strong>Post slugs successfully generated for ' .  $update_count . ' posts</strong></p>';
	}
}
