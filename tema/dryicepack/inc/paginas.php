<?php
/**
 * Registro de páginas de la web.
 * Cada página tiene una clave, su plantilla (plantillas/*.php) y su dirección en cada idioma.
 * Con esto se decide qué plantilla pintar, se generan las etiquetas hreflang y el selector de idioma,
 * y en Apariencia → Páginas Dryicepack se crean las que falten con un clic.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_paginas() {
	static $paginas = null;
	if ( null !== $paginas ) return $paginas;
	$paginas = array(
		'inicio'       => array( 'plantilla' => 'inicio', 'es' => '', 'ca' => 'ca', 'en' => 'en' ),
		'producto'     => array( 'plantilla' => 'producto', 'es' => 'producto/hielo-seco', 'ca' => 'ca/comprar-gel-sec', 'en' => 'en/buy-dry-ice' ),
		'empresas'     => array( 'plantilla' => 'empresas', 'es' => 'empresas', 'ca' => 'ca/empreses', 'en' => 'en/business' ),
		'aplicaciones' => array( 'plantilla' => 'aplicaciones', 'es' => 'aplicaciones-del-hielo-seco', 'ca' => 'ca/aplicacions', 'en' => 'en/uses' ),
		'hosteleria'   => array( 'plantilla' => 'hosteleria', 'es' => 'hielo-seco-hosteleria', 'ca' => 'ca/gel-sec-hostaleria', 'en' => 'en/dry-ice-for-bars-and-restaurants' ),
		'transporte'   => array( 'plantilla' => 'transporte', 'es' => 'hielo-seco-transporte', 'ca' => 'ca/gel-sec-transport', 'en' => 'en/dry-ice-for-shipping' ),
		'industria'    => array( 'plantilla' => 'industria', 'es' => 'hielo-seco-industria', 'ca' => 'ca/gel-sec-industria', 'en' => 'en/industrial-dry-ice' ),
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
	return $cache[ $clave ] = is_array( $datos ) ? $datos : null;
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
	$c = dipt_contenido( dipt_paginas()[ $clave ]['plantilla'] === 'zona' ? 'zonas' : $clave, $idioma );
	if ( 'zona' === dipt_paginas()[ $clave ]['plantilla'] && function_exists( 'dipt_zonas' ) ) {
		$z = dipt_zonas()[ dipt_paginas()[ $clave ]['zona'] ];
		return sprintf( array( 'es' => 'Hielo seco en %s', 'ca' => 'Gel sec a %s', 'en' => 'Dry ice in %s' )[ $idioma ], $z['nombre'] );
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

function dipt_pantalla_paginas() {
	if ( ! current_user_can( 'edit_pages' ) ) return;
	$creadas = null;
	if ( isset( $_POST['dipt_crear'] ) && check_admin_referer( 'dipt_crear_paginas' ) ) $creadas = dipt_crear_paginas();
	?>
	<div class="wrap">
		<h1>Páginas Dryicepack</h1>
		<p>El diseño y los textos de estas páginas están en el tema. Aquí solo se comprueba que cada dirección exista en WordPress.</p>
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
			<button class="button button-primary" name="dipt_crear" value="1">Crear las que faltan</button>
		</form>
	</div>
	<?php
}
