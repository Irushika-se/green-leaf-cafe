<?php
/**
 * Plugin Name: Green Leaf Cafe Setup
 * Description: Site builder for a small cafe. On activation it creates the Home, About, Menu and Contact pages, the main menu, the homepage setting and the styling. Adds a contact form ([gl_contact]) with a Messages inbox, and a settings page (Settings > Green Leaf Cafe) for address, phone, email and opening hours.
 * Version: 2.0
 * Author: Green Leaf Cafe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =====================================================================
 * 1. Settings (Settings > Green Leaf Cafe)
 * ===================================================================== */
function gl_fields() {
	return array(
		'address' => array( 'Address', 'text', 'e.g. 12 Garden Lane, Colombo' ),
		'phone'   => array( 'Phone', 'text', 'e.g. +94 11 234 5678' ),
		'email'   => array( 'Email', 'email', 'e.g. hello@yourcafe.com' ),
		'hours_1' => array( 'Opening hours, line 1', 'text', 'e.g. Monday - Friday: 7:00 AM - 9:00 PM' ),
		'hours_2' => array( 'Opening hours, line 2', 'text', 'e.g. Saturday - Sunday: 8:00 AM - 10:00 PM' ),
	);
}

function gl_opt( $key, $default = '' ) {
	$o = get_option( 'gl_opts', array() );
	return ( is_array( $o ) && isset( $o[ $key ] ) && '' !== $o[ $key ] ) ? $o[ $key ] : $default;
}

add_action( 'admin_init', function () {
	register_setting( 'gl_settings', 'gl_opts', array(
		'sanitize_callback' => function ( $in ) {
			$out = array();
			foreach ( gl_fields() as $k => $f ) {
				$v         = isset( $in[ $k ] ) ? wp_unslash( $in[ $k ] ) : '';
				$out[ $k ] = 'email' === $f[1] ? sanitize_email( $v ) : sanitize_text_field( $v );
			}
			return $out;
		},
	) );
} );

add_action( 'admin_menu', function () {
	add_options_page( 'Green Leaf Cafe', 'Green Leaf Cafe', 'manage_options', 'gl-settings', function () {
		echo '<div class="wrap"><h1>Green Leaf Cafe</h1><p>These details appear on the Home and Contact pages. Leave a field empty to hide it.</p><form method="post" action="options.php">';
		settings_fields( 'gl_settings' );
		echo '<table class="form-table">';
		foreach ( gl_fields() as $k => $f ) {
			printf(
				'<tr><th scope="row"><label for="gl_%1$s">%2$s</label></th><td><input class="regular-text" id="gl_%1$s" type="%3$s" name="gl_opts[%1$s]" value="%4$s" placeholder="%5$s"></td></tr>',
				esc_attr( $k ),
				esc_html( $f[0] ),
				esc_attr( $f[1] ),
				esc_attr( gl_opt( $k ) ),
				esc_attr( $f[2] )
			);
		}
		echo '</table>';
		submit_button();
		echo '</form></div>';
	} );
} );

/* Line icons (inline SVG). */
function gl_icon( $name ) {
	$p = array(
		'pin'    => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
		'phone'  => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mail'   => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M22 6l-10 7L2 6"/>',
		'clock'  => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'leaf'   => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
		'coffee' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><path d="M6 1v3"/><path d="M10 1v3"/><path d="M14 1v3"/>',
		'heart'  => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="gl-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

/* [gl_info]: contact details, only the fields that are filled in. */
add_shortcode( 'gl_info', function () {
	$rows = '';
	foreach ( array( 'address' => 'pin', 'phone' => 'phone', 'email' => 'mail' ) as $k => $icon ) {
		$v = gl_opt( $k );
		if ( '' === $v ) {
			continue;
		}
		if ( 'phone' === $k ) {
			$v = '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $v ) ) . '">' . esc_html( $v ) . '</a>';
		} elseif ( 'email' === $k ) {
			$v = '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>';
		} else {
			$v = esc_html( $v );
		}
		$rows .= '<li><span class="gl-ic">' . gl_icon( $icon ) . '</span><span>' . $v . '</span></li>';
	}
	$hours = array_filter( array( gl_opt( 'hours_1' ), gl_opt( 'hours_2' ) ) );
	if ( $hours ) {
		$rows .= '<li><span class="gl-ic">' . gl_icon( 'clock' ) . '</span><span>' . implode( '<br>', array_map( 'esc_html', $hours ) ) . '</span></li>';
	}
	if ( '' === $rows ) {
		return current_user_can( 'manage_options' ) ? '<p class="gl-empty">Add your address, phone and email under Settings &gt; Green Leaf Cafe. (Only you can see this note.)</p>' : '';
	}
	return '<div class="gl-info"><h3>Visit us</h3><ul>' . $rows . '</ul></div>';
} );

/* [gl_hours]: opening hours block (hidden when empty). */
add_shortcode( 'gl_hours', function () {
	$hours = array_filter( array( gl_opt( 'hours_1' ), gl_opt( 'hours_2' ) ) );
	if ( ! $hours ) {
		return '';
	}
	$out = '<div class="gl-hours"><span class="gl-ic gl-ic--lg">' . gl_icon( 'clock' ) . '</span><h2>Opening hours</h2>';
	foreach ( $hours as $h ) {
		$out .= '<p>' . esc_html( $h ) . '</p>';
	}
	return $out . '</div>';
} );

/* =====================================================================
 * 2. Contact form and Messages inbox
 * ===================================================================== */
add_action( 'init', function () {
	register_post_type( 'gl_message', array(
		'labels'       => array( 'name' => 'Messages', 'singular_name' => 'Message', 'menu_name' => 'Messages', 'not_found' => 'No messages yet.' ),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-email-alt',
		'menu_position'=> 26,
		'supports'     => array( 'title', 'editor' ),
		'capabilities' => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap' => true,
	) );
} );

add_shortcode( 'gl_contact', function () {
	$status = isset( $_GET['gl_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['gl_sent'] ) ) : '';
	ob_start();
	if ( '1' === $status ) {
		echo '<div class="gl-note gl-ok" role="status">Thank you! Your message has been received and we will reply soon.</div>';
	} elseif ( '0' === $status ) {
		echo '<div class="gl-note gl-err" role="alert">Please enter your name, a valid email and a message.</div>';
	}
	?>
	<form class="gl-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<h3>Send us a message</h3>
		<input type="hidden" name="action" value="gl_contact_submit">
		<input type="hidden" name="gl_back" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'gl_contact', 'gl_nonce' ); ?>
		<label>Your name <input type="text" name="gl_name" required></label>
		<label>Email address <input type="email" name="gl_email" required></label>
		<label>Message <textarea name="gl_message" rows="6" required></textarea></label>
		<p class="gl-hp" aria-hidden="true"><label>Leave empty <input type="text" name="gl_website" tabindex="-1" autocomplete="off"></label></p>
		<button type="submit">Send message</button>
	</form>
	<?php
	return ob_get_clean();
} );

function gl_contact_handle() {
	$back = isset( $_POST['gl_back'] ) ? esc_url_raw( wp_unslash( $_POST['gl_back'] ) ) : home_url( '/' );
	$nonce = isset( $_POST['gl_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gl_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'gl_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'gl_sent', '0', $back ) );
		exit;
	}
	if ( ! empty( $_POST['gl_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'gl_sent', '1', $back ) );
		exit;
	}
	$name    = isset( $_POST['gl_name'] ) ? sanitize_text_field( wp_unslash( $_POST['gl_name'] ) ) : '';
	$email   = isset( $_POST['gl_email'] ) ? sanitize_email( wp_unslash( $_POST['gl_email'] ) ) : '';
	$message = isset( $_POST['gl_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gl_message'] ) ) : '';
	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'gl_sent', '0', $back ) );
		exit;
	}
	wp_insert_post( array(
		'post_type'    => 'gl_message',
		'post_status'  => 'publish',
		'post_title'   => 'Message from ' . $name,
		'post_content' => 'From: ' . $name . ' <' . $email . ">\n\n" . $message,
	) );
	@wp_mail( get_option( 'admin_email' ), '[' . get_bloginfo( 'name' ) . '] Message from ' . $name, "Name: $name\nEmail: $email\n\n$message\n" );
	wp_safe_redirect( add_query_arg( 'gl_sent', '1', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_gl_contact_submit', 'gl_contact_handle' );
add_action( 'admin_post_gl_contact_submit', 'gl_contact_handle' );

/* =====================================================================
 * 3. Menu data (edit names, descriptions and prices here)
 * ===================================================================== */
function gl_menu_items() {
	return array(
		array( 'cappuccino', 'Cappuccino', 'Rich espresso with silky steamed milk and foam.', 'Rs. 650' ),
		array( 'matcha-latte', 'Matcha Latte', 'Ceremonial-style green tea whisked with warm milk.', 'Rs. 750' ),
		array( 'iced-latte', 'Iced Latte', 'Chilled espresso and milk over ice. Refreshing.', 'Rs. 700' ),
		array( 'butter-croissant', 'Butter Croissant', 'Flaky, golden and baked fresh every morning.', 'Rs. 450' ),
		array( 'chocolate-cake', 'Chocolate Cake', 'Moist layered chocolate cake with a cherry on top.', 'Rs. 850' ),
		array( 'club-sandwich', 'Club Sandwich', 'Toasted bread, egg, tomato, lettuce and cheese.', 'Rs. 990' ),
	);
}

/* =====================================================================
 * 4. Pages, menu and styling (run on activation)
 * ===================================================================== */
register_activation_hook( __FILE__, 'gl_setup' );

function gl_sideload_images() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$urls = array();
	$dir  = plugin_dir_path( __FILE__ ) . 'images/';
	foreach ( gl_menu_items() as $item ) {
		$slug     = $item[0];
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'name' => 'gl-' . $slug, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		if ( $existing ) {
			$urls[ $slug ] = wp_get_attachment_url( $existing[0] );
			continue;
		}
		$src = $dir . $slug . '.jpg';
		if ( ! file_exists( $src ) ) {
			continue;
		}
		$tmp = wp_tempnam( $slug . '.jpg' );
		copy( $src, $tmp );
		$id = media_handle_sideload( array( 'name' => 'gl-' . $slug . '.jpg', 'tmp_name' => $tmp ), 0, ucwords( str_replace( '-', ' ', $slug ) ) );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			continue;
		}
		$urls[ $slug ] = wp_get_attachment_url( $id );
	}
	return $urls;
}

function gl_card( $img, $title, $text, $price ) {
	return '<div class="gl-card"><div class="gl-card-img"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"><span class="gl-tag">' . esc_html( $price ) . '</span></div><div class="gl-body"><h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $text ) . '</p></div></div>';
}

function gl_page_html( $key, $img ) {
	$items = gl_menu_items();
	$cards = function ( $only = null ) use ( $items, $img ) {
		$out = '';
		foreach ( $items as $i => $it ) {
			if ( null !== $only && ! in_array( $i, $only, true ) ) {
				continue;
			}
			$out .= gl_card( isset( $img[ $it[0] ] ) ? $img[ $it[0] ] : '', $it[1], $it[2], $it[3] );
		}
		return $out;
	};
	$feature = function ( $icon, $title, $text ) {
		return '<div class="gl-feature"><span class="gl-ic gl-ic--lg">' . gl_icon( $icon ) . '</span><h3>' . esc_html( $title ) . '</h3><p>' . esc_html( $text ) . '</p></div>';
	};
	$name = esc_html( get_option( 'blogname', 'Green Leaf Caf&eacute;' ) );

	switch ( $key ) {
		case 'home':
			return '<div class="gl-hero"><h1>' . $name . '</h1><p>Fresh coffee, warm pastries and a quiet corner to relax.</p><a class="gl-btn" href="' . esc_url( home_url( '/menu/' ) ) . '">See our menu</a></div>'
				. '<div class="gl-section"><h2>Customer favourites</h2><p class="gl-sub">Our most loved drinks and bakes.</p><div class="gl-grid">' . $cards( array( 0, 3, 4 ) ) . '</div></div>'
				. '<div class="gl-section gl-band"><h2>Why you will love it here</h2><div class="gl-grid">'
				. $feature( 'coffee', 'Great coffee', 'Quality beans, carefully brewed.' )
				. $feature( 'leaf', 'Fresh and green', 'Baked daily with simple, good ingredients.' )
				. $feature( 'heart', 'Warm welcome', 'A calm, friendly place for everyone.' )
				. '</div></div>'
				. '<div class="gl-section">[gl_hours]</div>'
				. '<div class="gl-section"><div class="gl-cta"><h2>Come say hello</h2><p>We would love to see you at our caf&eacute;.</p><a class="gl-btn" href="' . esc_url( home_url( '/contact/' ) ) . '">Contact us</a></div></div>';
		case 'about':
			return '<div class="gl-section"><h2>Our story</h2><p class="gl-lead">' . $name . ' is a small, friendly caf&eacute; built around good coffee, fresh baking and a calm, green space. We use quality beans, bake in small batches and believe every cup should feel like a little break from the day.</p></div>'
				. '<div class="gl-section"><h2>What we believe in</h2><div class="gl-grid">'
				. $feature( 'coffee', 'Fresh', 'Baked and brewed daily.' )
				. $feature( 'heart', 'Friendly', 'A welcoming place for everyone.' )
				. $feature( 'leaf', 'Green', 'Simple, thoughtful and kind to the planet.' )
				. '</div></div>';
		case 'menu':
			return '<div class="gl-section"><h2>Our menu</h2><p class="gl-sub">Coffee, tea, bakes and light bites.</p><div class="gl-grid">' . $cards() . '</div></div>';
		case 'contact':
			return '<div class="gl-section"><h2>Get in touch</h2><p class="gl-sub">Questions, bookings or feedback. We would love to hear from you.</p><div class="gl-two"><div>[gl_info]</div><div>[gl_contact]</div></div></div>';
	}
	return '';
}

function gl_make_page( $title, $slug, $html ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	$id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->",
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, 'site-sidebar-layout', 'no-sidebar' );
		update_post_meta( $id, 'site-content-layout', 'normal-width-container' );
		if ( 'home' === $slug ) {
			update_post_meta( $id, 'site-post-title', 'disabled' );
		}
		return $id;
	}
	return 0;
}

function gl_refresh_css() {
	$file = plugin_dir_path( __FILE__ ) . 'css/green-leaf-cafe.css';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$sig = md5_file( $file );
	$cur = wp_get_custom_css();
	if ( false !== strpos( $cur, '/* GREEN LEAF CSS */' ) && get_option( 'gl_css_sig' ) === $sig ) {
		return;
	}
	$cur = preg_replace( '#/\* GREEN LEAF CSS \*/.*?/\* END GREEN LEAF CSS \*/#s', '', $cur, 1, $n );
	if ( ! $n ) {
		$cur = preg_replace( '#/\* GREEN LEAF CSS \*/.*$#s', '', $cur, 1 ); // Old version without an end marker.
	}
	wp_update_custom_css_post( rtrim( $cur ) . "\n" . file_get_contents( $file ) );
	update_option( 'gl_css_sig', $sig );
}
add_action( 'admin_init', 'gl_refresh_css' );

/* One-time upgrade from version 1: replace the made-up demo text on the pages. */
function gl_upgrade_v2() {
	if ( get_option( 'gl_v2_done' ) ) {
		return;
	}
	$img = gl_sideload_images();
	foreach ( array( 'home' => 'home', 'about' => 'about', 'menu' => 'menu', 'contact' => 'contact' ) as $slug => $key ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_content' => wp_slash( "<!-- wp:html -->\n" . gl_page_html( $key, $img ) . "\n<!-- /wp:html -->" ) ) );
		}
	}
	update_option( 'gl_v2_done', 1 );
}
add_action( 'admin_init', 'gl_upgrade_v2' );

function gl_setup() {
	$img = gl_sideload_images();

	$ids = array(
		'home'    => gl_make_page( 'Home', 'home', gl_page_html( 'home', $img ) ),
		'about'   => gl_make_page( 'About', 'about', gl_page_html( 'about', $img ) ),
		'menu'    => gl_make_page( 'Menu', 'menu', gl_page_html( 'menu', $img ) ),
		'contact' => gl_make_page( 'Contact', 'contact', gl_page_html( 'contact', $img ) ),
	);
	update_option( 'gl_v2_done', 1 );

	update_option( 'blogname', 'Green Leaf Café' );
	update_option( 'blogdescription', 'Fresh coffee and warm pastries' );
	if ( $ids['home'] ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	// Main menu.
	$name = 'Green Leaf Menu';
	if ( ! wp_get_nav_menu_object( $name ) ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( ! is_wp_error( $menu_id ) ) {
			$pos = 1;
			foreach ( array( 'home' => 'Home', 'about' => 'About', 'menu' => 'Menu', 'contact' => 'Contact' ) as $k => $label ) {
				if ( $ids[ $k ] ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'     => $label,
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $ids[ $k ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $pos++,
					) );
				}
			}
			$locs            = get_theme_mod( 'nav_menu_locations', array() );
			$locs['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locs );
		}
	}

	gl_refresh_css();
	flush_rewrite_rules();
}
