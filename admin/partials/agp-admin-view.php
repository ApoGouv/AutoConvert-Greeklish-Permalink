<?php
/**
 * The view for admin page
 *
 * @since    2.0.0
 *
 */
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$tabs       = array(
	[
		'id'       => 'permalink_settings',
		'name'     => __( 'Settings', 'agp' ),
		'template' => 'agp-settings-view.php',
	],
	[
		'id'       => 'generate_permalinks',
		'name'     => __( 'Convert old posts/terms', 'agp' ),
		'template' => 'agp-converter-view.php',
	],
);
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['name'];

?>
	<div class="wrap agp-plugin">
		<h1><?php _e( 'Convert Permalinks to Greeklish', 'agp' ) ?></h1>
		<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab ) {
			$is_active = $tab['id'] === $active_tab; ?>
			<a href="?page=agp&tab=<?php echo $tab['id']; ?>" class="nav-tab <?php echo $is_active ? 'nav-tab-active' : ''; ?>">
				<?php echo $tab['name']; ?>
			</a>
		<?php } ?>
		</h2>
		<?php foreach ( $tabs as $tab ) {
			$is_active = $tab['id'] === $active_tab;
			if ( $is_active ) {
				include_once( $tab['template'] );
			}
		} ?>
	</div>
<?php
