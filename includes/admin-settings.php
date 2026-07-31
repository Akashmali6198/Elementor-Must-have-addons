<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class EMHA_Admin_Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_ajax_emha_delete_submission', [ $this, 'delete_submission' ] );
	}

	public function add_settings_menu() {
		add_menu_page(
			esc_html__( 'Must-have Addons', 'elementor-must-have-addons' ),
			esc_html__( 'Must-have Addons', 'elementor-must-have-addons' ),
			'manage_options',
			'emha-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-superhero',
			100
		);
	}

	public function register_settings() {
		register_setting( 'emha_settings_group', 'emha_active_widgets' );
	}

	public function render_settings_page() {
		// Determine current tab
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'widgets';
		?>
		<style>
			.emha-admin-wrap {
				max-width: 1000px;
				margin: 20px 20px 0 0;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.emha-header {
				background: #0d1117;
				padding: 24px 30px;
				border-radius: 8px 8px 0 0;
				color: #fff;
				display: flex;
				align-items: center;
				justify-content: space-between;
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			}
			.emha-header h1 {
				margin: 0;
				color: #fff;
				font-size: 24px;
				font-weight: 700;
				display: flex;
				align-items: center;
				gap: 10px;
			}
			.emha-header h1 span {
				color: #dfa84f;
				font-size: 14px;
				border: 1px solid #dfa84f;
				padding: 2px 8px;
				border-radius: 12px;
				text-transform: uppercase;
				letter-spacing: 1px;
			}
			.emha-nav-tabs {
				background: #161b22;
				border-bottom: 1px solid #30363d;
				padding: 0 20px;
				display: flex;
				gap: 5px;
			}
			.emha-nav-tab {
				padding: 15px 20px;
				color: #c9d1d9;
				text-decoration: none;
				border-bottom: 3px solid transparent;
				font-weight: 600;
				font-size: 14px;
				transition: all 0.2s;
			}
			.emha-nav-tab:hover {
				color: #fff;
				background: rgba(255,255,255,0.05);
			}
			.emha-nav-tab.active {
				color: #dfa84f;
				border-bottom-color: #dfa84f;
				background: rgba(223, 168, 79, 0.05);
			}
			.emha-content-body {
				background: #fff;
				padding: 30px;
				border: 1px solid #e1e4e8;
				border-top: none;
				border-radius: 0 0 8px 8px;
				box-shadow: 0 4px 12px rgba(0,0,0,0.03);
			}
			/* Switch styling */
			.emha-switch-card {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 20px;
				border: 1px solid #e1e4e8;
				border-radius: 8px;
				margin-bottom: 15px;
				transition: border-color 0.2s;
				background: #fafbfc;
			}
			.emha-switch-card:hover {
				border-color: #dfa84f;
			}
			.emha-switch-info h3 {
				margin: 0 0 5px 0;
				font-size: 16px;
				color: #24292e;
			}
			.emha-switch-info p {
				margin: 0;
				color: #586069;
				font-size: 13px;
			}
			.emha-switch {
				position: relative;
				display: inline-block;
				width: 50px;
				height: 26px;
			}
			.emha-switch input {
				opacity: 0;
				width: 0;
				height: 0;
			}
			.emha-slider {
				position: absolute;
				cursor: pointer;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: #ccc;
				transition: .4s;
				border-radius: 34px;
			}
			.emha-slider:before {
				position: absolute;
				content: "";
				height: 18px;
				width: 18px;
				left: 4px;
				bottom: 4px;
				background-color: white;
				transition: .4s;
				border-radius: 50%;
			}
			input:checked + .emha-slider {
				background-color: #dfa84f;
			}
			input:focus + .emha-slider {
				box-shadow: 0 0 1px #dfa84f;
			}
			input:checked + .emha-slider:before {
				transform: translateX(24px);
			}
			/* Submissions table styling */
			.emha-sub-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 15px;
			}
			.emha-sub-table th, .emha-sub-table td {
				padding: 12px 15px;
				text-align: left;
				border-bottom: 1px solid #e1e4e8;
			}
			.emha-sub-table th {
				background-color: #f6f8fa;
				font-weight: 600;
				color: #24292e;
			}
			.emha-sub-table tr:hover {
				background-color: #fcfcfc;
			}
			.emha-btn-delete {
				color: #d73a49;
				text-decoration: none;
				font-weight: 600;
				cursor: pointer;
			}
			.emha-btn-delete:hover {
				text-decoration: underline;
			}
			.emha-sub-details {
				font-size: 12px;
				background: #f6f8fa;
				padding: 8px;
				border-radius: 4px;
				display: block;
				max-width: 400px;
				overflow-x: auto;
				white-space: pre-wrap;
			}
			.submit-btn-container {
				margin-top: 25px;
			}
		</style>

		<div class="emha-admin-wrap">
			<div class="emha-header">
				<h1>Elementor Must-have Addons <span>v1.0.1</span></h1>
			</div>

			<div class="emha-nav-tabs">
				<a href="?page=emha-settings&tab=widgets" class="emha-nav-tab <?php echo $active_tab === 'widgets' ? 'active' : ''; ?>">
					<?php esc_html_e( 'Manage Addons', 'elementor-must-have-addons' ); ?>
				</a>
				<a href="?page=emha-settings&tab=submissions" class="emha-nav-tab <?php echo $active_tab === 'submissions' ? 'active' : ''; ?>">
					<?php esc_html_e( 'Form Submissions', 'elementor-must-have-addons' ); ?>
				</a>
			</div>

			<div class="emha-content-body">
				<?php if ( $active_tab === 'widgets' ) : ?>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'emha_settings_group' );
						$active_widgets = get_option( 'emha_active_widgets', [ 'video-scroll' => '1', 'simple-form' => '1' ] );
						?>
						
						<div class="emha-switch-card">
							<div class="emha-switch-info">
								<h3>3D Video Scroll Widget</h3>
								<p>Enables the interactive, premium scroll-controlled cinematic video highlight section.</p>
							</div>
							<label class="emha-switch">
								<input type="checkbox" name="emha_active_widgets[video-scroll]" value="1" <?php checked( isset( $active_widgets['video-scroll'] ) && $active_widgets['video-scroll'] == '1' ); ?>>
								<span class="emha-slider"></span>
							</label>
						</div>

						<div class="emha-switch-card">
							<div class="emha-switch-info">
								<h3>Simple Submission Form</h3>
								<p>Enables custom premium email capture forms that send alerts and logs data locally.</p>
							</div>
							<label class="emha-switch">
								<input type="checkbox" name="emha_active_widgets[simple-form]" value="1" <?php checked( isset( $active_widgets['simple-form'] ) && $active_widgets['simple-form'] == '1' ); ?>>
								<span class="emha-slider"></span>
							</label>
						</div>

						<div class="submit-btn-container">
							<?php submit_button( esc_html__( 'Save Settings', 'elementor-must-have-addons' ), 'primary' ); ?>
						</div>
					</form>
				<?php else : ?>
					<h2>Form Submissions</h2>
					<?php
					global $wpdb;
					$table_name = $wpdb->prefix . 'emha_submissions';
					
					// Basic check to see if table exists (in case activation hook didn't fire)
					if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
						echo '<p>' . esc_html__( 'No submissions found (Database table does not exist). Please re-activate the plugin to initialize it.', 'elementor-must-have-addons' ) . '</p>';
					} else {
						$submissions = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
						if ( empty( $submissions ) ) {
							echo '<p>' . esc_html__( 'No form submissions received yet.', 'elementor-must-have-addons' ) . '</p>';
						} else {
							?>
							<table class="emha-sub-table">
								<thead>
									<tr>
										<th>ID</th>
										<th>Form ID / Name</th>
										<th>Submitted Fields</th>
										<th>IP Address</th>
										<th>Date</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $submissions as $sub ) : 
										$fields = json_decode( $sub->fields, true );
										?>
										<tr id="emha-row-<?php echo esc_attr( $sub->id ); ?>">
											<td><?php echo esc_html( $sub->id ); ?></td>
											<td>
												<strong><?php echo esc_html( $sub->form_name ); ?></strong><br>
												<small><?php echo esc_html( $sub->form_id ); ?></small>
											</td>
											<td>
												<div class="emha-sub-details"><?php 
													if ( is_array( $fields ) ) {
														foreach ( $fields as $key => $val ) {
															echo '<strong>' . esc_html( ucfirst( $key ) ) . ':</strong> ' . esc_html( $val ) . "\n";
														}
													} else {
														echo esc_html( $sub->fields );
													}
												?></div>
											</td>
											<td><?php echo esc_html( $sub->user_ip ); ?></td>
											<td><?php echo esc_html( $sub->created_at ); ?></td>
											<td>
												<span class="emha-btn-delete" onclick="emhaDeleteSubmission(<?php echo esc_attr( $sub->id ); ?>)">Delete</span>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<script>
								function emhaDeleteSubmission(id) {
									if (!confirm('Are you sure you want to delete this submission?')) {
										return;
									}
									jQuery.post(ajaxurl, {
										action: 'emha_delete_submission',
										id: id,
										_wpnonce: '<?php echo wp_create_nonce( "emha_delete_nonce" ); ?>'
									}, function(response) {
										if (response.success) {
											jQuery('#emha-row-' + id).fadeOut(300, function() {
												jQuery(this).remove();
											});
										} else {
											alert('Failed to delete: ' + (response.data.message || 'Unknown error'));
										}
									});
								}
							</script>
							<?php
						}
					}
					?>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function delete_submission() {
		check_ajax_referer( 'emha_delete_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ] );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Invalid ID' ] );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'emha_submissions';
		$deleted = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );

		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( [ 'message' => 'Database error' ] );
		}
	}
}

new EMHA_Admin_Settings();
