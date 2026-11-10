<?php
/**
 * Registro de páginas de la web.
 * Cada página tiene una clave, su plantilla (plantillas/*.php) y su dirección en cada idioma.
 * Con esto se decide qué plantilla pintar, se generan las etiquetas hreflang y el selector de idioma,
 * y en Apariencia → Páginas Dryicepack se crean las que falten con un clic.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_paginas( $recargar = false ) {
	static $paginas = null;
	if ( null !== $paginas && ! $recargar ) return $paginas;
	$paginas = array(
		'inicio'       => array( 'plantilla' => 'inicio', 'es' => '', 'ca' => 'ca', 'en' => 'en' ),
		'producto'     => array( 'plantilla' => 'producto', 'es' => 'producto/hielo-seco', 'ca' => 'ca/comprar-gel-sec', 'en' => 'en/buy-dry-ice' ),
		'empresas'     => array( 'plantilla' => 'empresas', 'es' => 'empresas', 'ca' => 'ca/empreses', 'en' => 'en/business' ),
		'aplicaciones' => array( 'plantilla' => 'aplicaciones', 'es' => 'aplicaciones-del-hielo-seco', 'ca' => 'ca/aplicacions', 'en' => 'en/uses' ),
		'hosteleria'   => array( 'plantilla' => 'hosteleria', 'es' => 'hielo-seco-hosteleria', 'ca' => 'ca/gel-sec-hostaleria', 'en' => 'en/dry-ice-for-bars-and-restaurants' ),
		'transporte'   => array( 'plantilla' => 'transporte', 'es' => 'hielo-seco-transporte', 'ca' => 'ca/gel-sec-transport', 'en' => 'en/dry-ice-for-shipping' ),
		'industria'    => array( 'plantilla' => 'industria', 'es' => 'hielo-seco-industria', 'ca' => 'ca/gel-sec-industria', 'en' => 'en/industrial-dry-ice' ),
		// Uso B2B hermano de Industria (10/11/2026): pellets de 3 mm para máquinas de limpieza criogénica. Sin inglés.
		'limpieza'     => array( 'plantilla' => 'limpieza', 'es' => 'hielo-seco-limpieza-criogenica', 'ca' => 'ca/gel-sec-neteja-criogenica' ),
		'laboratorios' => array( 'plantilla' => 'laboratorios', 'es' => 'hielo-seco-laboratorios', 'ca' => 'ca/gel-sec-laboratoris', 'en' => 'en/dry-ice-for-laboratories' ),
		'eventos'      => array( 'plantilla' => 'eventos', 'es' => 'hielo-seco-eventos', 'ca' => 'ca/gel-sec-festes', 'en' => 'en/dry-ice-for-events' ),
		'halloween'    => array( 'plantilla' => 'halloween', 'es' => 'hielo-seco-halloween' ),
		'zonas'        => array( 'plantilla' => 'zonas', 'es' => 'zonas', 'ca' => 'ca/zones', 'en' => 'en/areas' ),
		'envios'       => array( 'plantilla' => 'envios', 'es' => 'envios-y-plazos', 'ca' => 'ca/enviaments', 'en' => 'en/delivery' ),
		'que-es'       => array( 'plantilla' => 'que-es', 'es' => 'que-es-el-hielo-seco', 'ca' => 'ca/que-es-el-gel-sec', 'en' => 'en/what-is-dry-ice' ),
		'seguridad'    => array( 'plantilla' => 'seguridad', 'es' => 'seguridad-del-hielo-seco', 'ca' => 'ca/seguretat-del-gel-sec', 'en' => 'en/dry-ice-safety' ),
		'contacto'     => array( 'plantilla' => 'contacto', 'es' => 'contacto', 'ca' => 'ca/contacte', 'en' => 'en/contact' ),
		'guias'        => array( 'plantilla' => 'guias', 'es' => 'guias', 'ca' => 'ca/guies', 'en' => 'en/guides' ),
		'aviso-legal'  => array( 'plantilla' => 'legal', 'es' => 'aviso-legal', 'ca' => 'ca/avis-legal', 'en' => 'en/legal-notice' ),
		'privacidad'   => array( 'plantilla' => 'legal', 'es' => 'politica-de-privacidad', 'ca' => 'ca/politica-de-privacitat', 'en' => 'en/privacy-policy' ),
		'cookies'      => array( 'plantilla' => 'legal', 'es' => 'politica-de-cookies', 'ca' => 'ca/politica-de-galetes', 'en' => 'en/cookie-policy' ),
		'condiciones'  => array( 'plantilla' => 'legal', 'es' => 'condiciones-de-venta', 'ca' => 'ca/condicions-de-venda', 'en' => 'en/terms-of-sale' ),
		'factura'      => array( 'plantilla' => 'factura', 'es' => 'factura' ),
	);
	// Zonas de entrega (datos en datos/zonas.php): una página por zona con la misma plantilla y datos propios.
	if ( function_exists( 'dipt_zonas' ) ) {
		foreach ( dipt_zonas() as $clave => $z ) {
			$paginas[ 'zona-' . $clave ] = array( 'plantilla' => 'zona', 'es' => $z['es'], 'ca' => $z['ca'] ?? null, 'en' => $z['en'] ?? null, 'zona' => $clave );
		}
	}
	// Provincias y ciudades de la península (inc/lugares.php): hielo-seco-<clave> y, en Cataluña, ca/gel-sec-<clave>. Sin inglés.
	// Las ciudades, solo con el interruptor de Apariencia → Páginas Dryicepack. Una ruta ya registrada no se pisa.
	if ( function_exists( 'dipt_provincias' ) ) {
		$ocupadas = array();
		foreach ( $paginas as $p ) {
			foreach ( array( 'es', 'ca', 'en' ) as $i ) if ( ! empty( $p[ $i ] ) ) $ocupadas[ $p[ $i ] ] = true;
		}
		foreach ( array( 'provincia' => dipt_provincias(), 'ciudad' => dipt_ciudades() ) as $tipo => $lugares ) {
			foreach ( $lugares as $clave => $l ) {
				if ( $tipo . '-' . $clave !== $l['pagina'] ) continue; // Barcelona y las zonas ya tienen su página
				$es = 'hielo-seco-' . $clave;
				$ca = $l['catalunya'] ? 'ca/gel-sec-' . $clave : null;
				if ( isset( $ocupadas[ $es ] ) || ( $ca && isset( $ocupadas[ $ca ] ) ) ) continue;
				$paginas[ $l['pagina'] ] = array( 'plantilla' => 'provincia', 'es' => $es, 'ca' => $ca, 'en' => null, 'lugar' => array( $tipo, $clave ) );
				$ocupadas[ $es ] = true;
				if ( $ca ) $ocupadas[ $ca ] = true;
			}
		}
	}
	// Guías (contenido/{idioma}/guias/*.php): páginas hijas de /guias/, /ca/guies/ y /en/guides/ con su slug en cada idioma
	foreach ( dipt_guias() as $slug => $g ) {
		$paginas[ 'guia-' . $slug ] = array( 'plantilla' => 'guia', 'es' => 'guias/' . $g['es'], 'ca' => isset( $g['ca'] ) ? 'ca/guies/' . $g['ca'] : null, 'en' => isset( $g['en'] ) ? 'en/guides/' . $g['en'] : null, 'guia' => $slug );
	}
	return $paginas;
}

/** Guías disponibles: slug del archivo (el de castellano) → slug de la dirección en cada idioma. */
function dipt_guias() {
	static $guias = null;
	if ( null !== $guias ) return $guias;
	$guias = array();
	foreach ( (array) glob( DIPT_DIR . '/contenido/es/guias/*.php' ) as $archivo ) {
		$slug = basename( $archivo, '.php' );
		foreach ( array_keys( dipt_idiomas() ) as $idioma ) {
			$g = dipt_guia( $slug, $idioma, false );
			if ( $g ) $guias[ $slug ][ $idioma ] = sanitize_title( $g['slug'] ?? $slug );
		}
	}
	ksort( $guias );
	return $guias;
}

/** Datos de una guía en un idioma (sin traducción: castellano, salvo que $respaldo sea false). */
function dipt_guia( $slug, $idioma = null, $respaldo = true ) {
	static $cache = array();
	$idioma = $idioma ?: dipt_idioma();
	$slug   = sanitize_file_name( $slug );
	$clave  = $idioma . '/' . $slug . ( $respaldo ? '' : '/x' );
	if ( isset( $cache[ $clave ] ) ) return $cache[ $clave ];
	$archivo = DIPT_DIR . '/contenido/' . $idioma . '/guias/' . $slug . '.php';
	if ( ! file_exists( $archivo ) && $respaldo ) $archivo = DIPT_DIR . '/contenido/es/guias/' . $slug . '.php';
	$datos = file_exists( $archivo ) ? include $archivo : null;
	return $cache[ $clave ] = is_array( $datos ) ? dipt_marcadores( $datos, $idioma ) : null; // {pk}, {recogida:10}… (inc/idiomas.php)
}

/** Dirección de una página en un idioma (o la de castellano si no está traducida). */
function dipt_url( $clave, $idioma = null, $ancla = '' ) {
	$idioma = $idioma ?: dipt_idioma();
	$p      = dipt_paginas()[ $clave ] ?? null;
	if ( ! $p ) return home_url( '/' );
	$ruta = $p[ $idioma ] ?? null;
	if ( null === $ruta ) {
		$idioma = 'es';
		$ruta   = $p['es'] ?? '';
	}
	if ( 'producto' === $clave && 'es' === $idioma && function_exists( 'dip_url_producto' ) ) {
		return dip_url_producto() . ( $ancla ? '#' . $ancla : '' );
	}
	return home_url( '/' . ( '' === $ruta ? '' : trailingslashit( $ruta ) ) ) . ( $ancla ? '#' . $ancla : '' );
}

/** ¿Existe esta página en ese idioma? */
function dipt_traducida( $clave, $idioma ) {
	return ! empty( dipt_paginas()[ $clave ][ $idioma ] ) || ( 'inicio' === $clave );
}

/** Clave de la página que se está viendo (o null si no es una del registro). */
function dipt_pagina_actual() {
	static $actual = false;
	if ( false !== $actual ) return $actual;
	$actual = null;
	if ( is_front_page() ) return $actual = 'inicio';
	if ( function_exists( 'is_product' ) && is_product() ) return $actual = 'producto';
	if ( is_page() ) {
		$ruta = get_page_uri( get_queried_object_id() );
		foreach ( dipt_paginas() as $clave => $p ) {
			foreach ( array_keys( dipt_idiomas() ) as $i ) {
				if ( isset( $p[ $i ] ) && null !== $p[ $i ] && $p[ $i ] === $ruta ) return $actual = $clave;
			}
		}
	}
	if ( is_home() ) return $actual = 'guias';
	return $actual;
}

/* ---------- Qué plantilla se pinta ---------- */
add_filter( 'template_include', static function ( $plantilla ) {
	if ( is_admin() ) return $plantilla;
	$clave = dipt_pagina_actual();
	if ( ! $clave || 'factura' === $clave ) return $plantilla; // /factura/ usa page.php con el shortcode del plugin
	$nombre  = dipt_paginas()[ $clave ]['plantilla'] ?? '';
	$archivo = DIPT_DIR . '/plantillas/' . $nombre . '.php';
	return file_exists( $archivo ) ? $archivo : $plantilla;
}, 50 );

/* ---------- hreflang: cada página enlaza sus traducciones ---------- */
add_action( 'wp_head', static function () {
	$clave = dipt_pagina_actual();
	if ( ! $clave || in_array( $clave, array( 'factura' ), true ) ) return;
	$p      = dipt_paginas()[ $clave ];
	$codigo = array( 'es' => 'es', 'ca' => 'ca', 'en' => 'en' );
	$hay    = 0;
	foreach ( $codigo as $i => $hreflang ) {
		if ( isset( $p[ $i ] ) && null !== $p[ $i ] ) $hay++;
	}
	if ( $hay < 2 ) return;
	foreach ( $codigo as $i => $hreflang ) {
		if ( isset( $p[ $i ] ) && null !== $p[ $i ] ) printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $hreflang ), esc_url( dipt_url( $clave, $i ) ) );
	}
	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( dipt_url( $clave, 'es' ) ) );
}, 2 );

/* ---------- Apariencia → Páginas Dryicepack: comprobar y crear las que faltan ---------- */
add_action( 'admin_menu', static function () {
	add_theme_page( 'Páginas Dryicepack', 'Páginas Dryicepack', 'edit_pages', 'dipt-paginas', 'dipt_pantalla_paginas' );
} );

function dipt_titulo_pagina( $clave, $idioma ) {
	if ( 'guia' === dipt_paginas()[ $clave ]['plantilla'] ) return dipt_guia( dipt_paginas()[ $clave ]['guia'], $idioma )['titulo'] ?? $clave;
	if ( 'provincia' === dipt_paginas()[ $clave ]['plantilla'] && function_exists( 'dipt_lugar_de_pagina' ) ) {
		$l = dipt_lugar_de_pagina( $clave );
		return $l ? wp_strip_all_tags( dipt_lugar_texto( array( 'es' => 'Hielo seco en {nombre}', 'ca' => 'Gel sec a {nombre}', 'en' => 'Dry ice in {nombre}' )[ $idioma ] ?? '{nombre}', $l, $idioma ) ) : $clave;
	}
	$c = dipt_contenido( dipt_paginas()[ $clave ]['plantilla'] === 'zona' ? 'zonas' : $clave, $idioma );
	if ( 'zona' === dipt_paginas()[ $clave ]['plantilla'] && function_exists( 'dipt_zonas' ) ) {
		$z = dipt_zonas()[ dipt_paginas()[ $clave ]['zona'] ];
		// Con las contracciones de cada idioma: "Gel sec al Maresme", "Gel sec a l'Hospitalet"
		return wp_strip_all_tags( dipt_zona_texto( array( 'es' => 'Hielo seco en {nombre}', 'ca' => 'Gel sec a {nombre}', 'en' => 'Dry ice in {nombre}' )[ $idioma ], $z, $idioma ) );
	}
	return $c['menu'] ?? ( $c['seo']['titulo'] ?? ucfirst( str_replace( '-', ' ', $clave ) ) );
}

function dipt_crear_paginas() {
	$creadas = array();
	// El registro empieza por 'inicio': en catalán e inglés se crea primero la página madre (/ca/, /en/).
	foreach ( array( 'es', 'ca', 'en' ) as $idioma ) {
		foreach ( dipt_paginas() as $clave => $p ) {
			$ruta = $p[ $idioma ] ?? null;
			if ( null === $ruta || '' === $ruta || 'producto' === $clave && 'es' === $idioma ) continue;
			if ( get_page_by_path( $ruta ) ) continue;
			$partes = explode( '/', $ruta );
			$slug   = array_pop( $partes );
			$padre  = 0;
			if ( $partes ) {
				$madre = get_page_by_path( implode( '/', $partes ) );
				if ( ! $madre ) continue; // la madre se crea antes (inicio ca/en)
				$padre = $madre->ID;
			}
			$id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $slug,
				'post_parent' => $padre,
				'post_title'  => wp_strip_all_tags( dipt_titulo_pagina( $clave, $idioma ) ),
			) );
			if ( $id && ! is_wp_error( $id ) ) $creadas[] = $ruta;
		}
	}
	return $creadas;
}

/**
 * Interruptor de las páginas de ciudades (10/11/2026): encendido de serie (el propietario las quiere todas desde el
 * principio). Apagado: las rutas de ciudad no se registran ni se enlazan y sus páginas redirigen a su provincia.
 * Devuelve true si ha cambiado. Al cambiar se vacía la caché de WP Rocket (cambian los enlaces de provincias y /zonas/).
 */
function dipt_guardar_interruptor_ciudades( $activar ) {
	$activar = $activar ? 1 : 0;
	if ( (int) dipt_ciudades_activas() === $activar ) return false;
	update_option( 'dipt_ciudades', $activar, true );
	dipt_paginas( true );
	if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();
	return true;
}

/**
 * Páginas de WordPress que el tema no usa (10/11/2026): restos de versiones anteriores (páginas vacías como /transporte/
 * o /barcelona/, duplicados como /factura-2/, la página de Divi de «Programar suministro», borradores viejos…).
 * En uso: las del registro en los tres idiomas y sus madres, las de ciudades (aunque estén apagadas), portada, blog,
 * privacidad, las de WooCommerce y /factura/ (plugin).
 */
function dipt_paginas_en_uso_ids() {
	$ids    = array();
	$marcar = static function ( $ruta ) use ( &$ids ) {
		$pg = $ruta ? get_page_by_path( $ruta ) : null;
		if ( ! $pg ) return;
		$ids[ $pg->ID ] = true;
		foreach ( get_post_ancestors( $pg ) as $a ) $ids[ $a ] = true;
	};
	foreach ( dipt_paginas() as $p ) {
		foreach ( array( 'es', 'ca', 'en' ) as $i ) $marcar( $p[ $i ] ?? null );
	}
	if ( function_exists( 'dipt_ciudades' ) ) {
		foreach ( array_keys( dipt_ciudades( true ) ) as $k ) {
			$marcar( 'hielo-seco-' . $k );
			$marcar( 'ca/gel-sec-' . $k );
		}
	}
	$marcar( 'factura' );
	foreach ( array( 'page_on_front', 'page_for_posts', 'wp_page_for_privacy_policy' ) as $o ) {
		$v = (int) get_option( $o );
		if ( $v ) $ids[ $v ] = true;
	}
	if ( function_exists( 'wc_get_page_id' ) ) {
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount', 'terms' ) as $w ) {
			$v = (int) wc_get_page_id( $w );
			if ( $v > 0 ) $ids[ $v ] = true;
		}
	}
	return $ids;
}
function dipt_paginas_antiguas() {
	$uso = dipt_paginas_en_uso_ids();
	$todas = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	return array_values( array_filter( $todas, static fn( $p ) => ! isset( $uso[ $p->ID ] ) ) );
}

function dipt_pantalla_paginas() {
	if ( ! current_user_can( 'edit_pages' ) ) return;
	$creadas = null;
	$cambio  = false;
	$a_papelera = null;
	if ( isset( $_POST['dipt_papelera'] ) && check_admin_referer( 'dipt_papelera' ) && current_user_can( 'delete_pages' ) ) {
		$sobran     = wp_list_pluck( dipt_paginas_antiguas(), 'ID' );
		$a_papelera = array();
		foreach ( array_map( 'absint', (array) wp_unslash( $_POST['dipt_antiguas'] ?? array() ) ) as $id ) {
			if ( in_array( $id, $sobran, true ) && wp_trash_post( $id ) ) $a_papelera[] = $id;
		}
		if ( $a_papelera && function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();
	}
	if ( ( isset( $_POST['dipt_crear'] ) || isset( $_POST['dipt_guardar'] ) ) && check_admin_referer( 'dipt_crear_paginas' ) ) {
		$cambio = function_exists( 'dipt_guardar_interruptor_ciudades' ) && dipt_guardar_interruptor_ciudades( ! empty( $_POST['dipt_ciudades'] ) );
		if ( isset( $_POST['dipt_crear'] ) ) $creadas = dipt_crear_paginas();
	}
	$n_ciudades = function_exists( 'dipt_ciudades' ) ? count( dipt_ciudades( true ) ) : 0;
	?>
	<div class="wrap">
		<h1>Páginas Dryicepack</h1>
		<p>El diseño y los textos de estas páginas están en el tema. Aquí solo se comprueba que cada dirección exista en WordPress.</p>
		<?php if ( $cambio ) : ?>
			<div class="notice notice-info"><p><?php echo dipt_ciudades_activas() ? 'Páginas de ciudades activadas: ya se enlazan desde su provincia y desde /zonas/.' : 'Páginas de ciudades desactivadas: ya no se enlazan y sus direcciones redirigen a su provincia.'; ?></p></div>
		<?php endif; ?>
		<?php if ( null !== $creadas ) : ?>
			<div class="notice notice-success"><p><?php echo $creadas ? esc_html( 'Creadas: ' . implode( ', ', $creadas ) ) : 'No faltaba ninguna.'; ?></p></div>
		<?php endif; ?>
		<table class="widefat striped" style="max-width:980px">
			<thead><tr><th>Página</th><th>Castellano</th><th>Català</th><th>English</th></tr></thead>
			<tbody>
			<?php foreach ( dipt_paginas() as $clave => $p ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $clave ); ?></strong></td>
					<?php foreach ( array( 'es', 'ca', 'en' ) as $i ) : ?>
						<td>
							<?php
							$ruta = $p[ $i ] ?? null;
							if ( null === $ruta ) {
								echo '—';
							} elseif ( '' === $ruta ) {
								echo 'Portada ✓';
							} elseif ( 'producto' === $clave && 'es' === $i ) {
								echo '/producto/hielo-seco/ (WooCommerce)';
							} else {
								$existe = get_page_by_path( $ruta );
								echo $existe ? '<a href="' . esc_url( get_permalink( $existe ) ) . '">/' . esc_html( $ruta ) . '/</a> ✓' : '<span style="color:#b32d2e">/' . esc_html( $ruta ) . '/ falta</span>';
							}
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<form method="post" style="margin-top:1.5rem">
			<?php wp_nonce_field( 'dipt_crear_paginas' ); ?>
			<p><label><input type="checkbox" name="dipt_ciudades" value="1"<?php checked( dipt_ciudades_activas() ); ?>> Crear también las páginas de ciudades (<?php echo (int) $n_ciudades; ?> ciudades de más de 50.000 habitantes)</label><br>
			<span class="description">Encendido de serie: las ciudades se enlazan desde su provincia y desde /zonas/, y «Crear las que faltan» crea sus páginas. Si lo apagas, dejan de enlazarse y cada dirección de ciudad redirige a su provincia.</span></p>
			<button class="button button-primary" name="dipt_crear" value="1">Crear las que faltan</button>
			<button class="button" name="dipt_guardar" value="1">Guardar sin crear</button>
		</form>

		<?php $antiguas = dipt_paginas_antiguas(); ?>
		<h2 style="margin-top:2.5rem">Páginas antiguas que sobran</h2>
		<?php if ( null !== $a_papelera ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $a_papelera ? count( $a_papelera ) . ' páginas enviadas a la papelera. Se pueden recuperar durante 30 días en Páginas → Papelera.' : 'No se ha enviado ninguna.' ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! $antiguas ) : ?>
			<p>No queda ninguna: todas las páginas de WordPress las usa el tema, WooCommerce o la tienda.</p>
		<?php else : ?>
			<p>Estas páginas de WordPress no las usa el tema ni la tienda: son restos de versiones anteriores (páginas vacías, duplicados, la página de Divi de «Programar suministro», borradores viejos). Revísalas y pulsa el botón: van a la <strong>papelera</strong>, donde se pueden recuperar durante 30 días. Sus direcciones antiguas siguen redirigiendo a la página nueva que corresponde.</p>
			<form method="post">
				<?php wp_nonce_field( 'dipt_papelera' ); ?>
				<table class="widefat striped" style="max-width:980px">
					<thead><tr><td class="check-column"></td><th>Página</th><th>Dirección</th><th>Estado</th><th>Modificada</th><th>Redirige a</th></tr></thead>
					<tbody>
					<?php
					$sobrantes = function_exists( 'dip_paginas_sobrantes' ) ? dip_paginas_sobrantes() : array();
					foreach ( $antiguas as $p ) :
						$ruta    = get_page_uri( $p );
						$destino = ! $p->post_parent ? ( $sobrantes[ $p->post_name ] ?? '' ) : '';
						?>
						<tr>
							<th scope="row" class="check-column"><input type="checkbox" name="dipt_antiguas[]" value="<?php echo (int) $p->ID; ?>" checked aria-label="<?php echo esc_attr( 'Mandar a la papelera: ' . $p->post_title ); ?>"></th>
							<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ?: '(sin título)' ); ?></a></td>
							<td><?php echo 'publish' === $p->post_status ? '<a href="' . esc_url( get_permalink( $p ) ) . '">/' . esc_html( $ruta ) . '/</a>' : '/' . esc_html( $ruta ) . '/'; ?></td>
							<td><?php echo esc_html( get_post_status_object( $p->post_status )->label ?? $p->post_status ); ?></td>
							<td><?php echo esc_html( get_the_modified_date( 'j/n/Y', $p ) ); ?></td>
							<td><?php echo $destino ? esc_html( $destino ) : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p><button class="button button-primary" name="dipt_papelera" value="1" onclick="return confirm('¿Mandar a la papelera las páginas marcadas? Se pueden recuperar durante 30 días.');">Mandar a la papelera las marcadas (<?php echo count( $antiguas ); ?>)</button></p>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
