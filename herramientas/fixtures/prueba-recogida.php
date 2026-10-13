<?php
/**
 * Pruebas en el WordPress de prueba (decisiones del 03/10/2026):
 *  1. Pack pequeño: 3 kg mientras quede stock; sin stock, el de 5 kg (comprable y visible), aviso por email una sola vez.
 *  2. Recogida: total exacto en efectivo con cualquier cantidad, sábado +10,00 € (8,26 + 1,74), 31/10/2026 sin
 *     suplemento y vuelta al precio de la web con el envío (9,70 € + IVA también el 31/10).
 *  3. Factura simplificada de un pedido de recogida real: 2 × 10 kg un sábado = 120,00 + 10,00 = 130,00 €.
 *  4. GET /wp-json/dip/v1/envio (app INDUNOVA Control): campos, tipos, días de MRW y sin datos personales.
 *
 * herramientas/probar-recogida.mjs copia este archivo en la carpeta del plugin (zz-prueba-recogida.php), lo llama y
 * lo borra siempre: no puede quedarse allí porque iría al .zip.
 * Deja todo como estaba (también si algo falla): stock de las variaciones de 3 kg, aviso del pack pequeño, carrito
 * y pedido de prueba (se borra). No envía emails (pre_wp_mail los captura) y no gasta números de la serie W.
 */
define( 'DOING_AJAX', true ); // el suplemento de sábado solo se calcula en el checkout o por AJAX
require dirname( __DIR__, 3 ) . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
if ( ! function_exists( 'wc_load_cart' ) || ! function_exists( 'dip_pack_pequeno' ) ) {
	exit( "FALLO: falta WooCommerce o el plugin dryicepack-tienda\n0 comprobaciones, 1 fallos\n" );
}

/* ---------- Herramientas de la prueba ---------- */
$fallos  = 0;
$pruebas = 0;
$ok      = static function ( $cond, $msg, $detalle = null ) use ( &$fallos, &$pruebas ) {
	$pruebas++;
	if ( $cond ) return true;
	$fallos++;
	echo 'FALLO: ' . $msg . ( null === $detalle ? '' : ' → ' . ( is_string( $detalle ) ? $detalle : wp_json_encode( $detalle, JSON_UNESCAPED_UNICODE ) ) ) . "\n";
	return false;
};
$casi = static fn( $a, $b ) => abs( (float) $a - (float) $b ) < 0.001;

// Emails: se capturan y no se envían (wp_mail devuelve true, como si hubiera salido)
$GLOBALS['dip_prueba_correos'] = array();
add_filter( 'pre_wp_mail', static function ( $nulo, $atts ) {
	$GLOBALS['dip_prueba_correos'][] = $atts;
	return true;
}, 999, 2 );

// Lo que hay que deshacer al final, pase lo que pase (también con un error fatal: register_shutdown_function)
$GLOBALS['dip_prueba_limpiar'] = array();
$limpiar = static function () {
	while ( $f = array_pop( $GLOBALS['dip_prueba_limpiar'] ) ) {
		try {
			$f();
		} catch ( Throwable $e ) {
			echo 'FALLO: al dejar el servidor como estaba: ' . $e->getMessage() . "\n";
		}
	}
};
register_shutdown_function( static function () use ( $limpiar ) {
	$e = error_get_last();
	if ( $e && in_array( $e['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ), true ) ) {
		echo 'FALLO: error fatal de PHP: ' . $e['message'] . ' en ' . basename( $e['file'] ) . ':' . $e['line'] . "\n";
	}
	$limpiar();
} );

$hoy = ( new DateTimeImmutable( 'today', dip_zona_horaria() ) )->format( 'Y-m-d' );

try {
	wc_load_cart();
	$c = WC()->customer;
	$c->set_billing_country( 'ES' ); $c->set_shipping_country( 'ES' );
	$c->set_billing_postcode( '08013' ); $c->set_shipping_postcode( '08013' );
	$c->set_billing_city( 'Barcelona' ); $c->set_shipping_city( 'Barcelona' );
	$c->set_billing_state( 'B' ); $c->set_shipping_state( 'B' );

	/* =========================================================
	 * 1) Pack pequeño (inc/pack-pequeno.php)
	 * ========================================================= */
	echo "== Pack pequeño\n";
	$aviso_antes = get_option( 'dip_pack_pequeno_aviso', null );
	$restaurar_aviso = static function () use ( $aviso_antes ) {
		if ( null === $aviso_antes ) delete_option( 'dip_pack_pequeno_aviso' );
		else update_option( 'dip_pack_pequeno_aviso', $aviso_antes, false );
	};
	$GLOBALS['dip_prueba_limpiar'][] = $restaurar_aviso;

	// Los mismos objetos que guarda dip_variaciones_de_kg(): así su memoria ve los cambios de stock
	$tres  = array_values( dip_variaciones_de_kg( 3 ) );
	$cinco = array_values( dip_variaciones_de_kg( 5 ) );
	$ok( 2 === count( $tres ), 'hay dos variaciones de 3 kg (3 y 16 mm)', count( $tres ) );
	$ok( 2 === count( $cinco ), 'hay dos variaciones de 5 kg (3 y 16 mm)', count( $cinco ) );
	$ok( 'auto' === dip_ajuste( 'pack_pequeno' ), 'Ajustes → Pack pequeño en «Automático»', dip_ajuste( 'pack_pequeno' ) );

	$original = array();
	foreach ( $tres as $v ) {
		$original[ $v->get_id() ] = array(
			'manage'     => $v->get_manage_stock( 'edit' ),
			'qty'        => $v->get_stock_quantity( 'edit' ),
			'status'     => $v->get_stock_status( 'edit' ),
			'backorders' => $v->get_backorders( 'edit' ),
			'low'        => $v->get_low_stock_amount( 'edit' ),
		);
	}
	echo 'Stock del 3 kg al empezar: ' . wp_json_encode( $original ) . "\n";
	// Con el stock gestionado, WooCommerce recalcula el estado con la cantidad: se quita la gestión y manda el estado
	$poner = static function ( $v, $estado ) {
		if ( $v->get_manage_stock( 'edit' ) ) $v->set_manage_stock( false );
		$v->set_stock_status( $estado );
		$v->save();
	};
	$restaurado = false;
	$restaurar_stock = static function () use ( $tres, $original, &$restaurado ) {
		if ( $restaurado ) return;
		$restaurado = true;
		foreach ( $tres as $v ) {
			$o = $original[ $v->get_id() ];
			$v->set_manage_stock( $o['manage'] );
			if ( $o['manage'] ) {
				$v->set_stock_quantity( $o['qty'] );
				$v->set_backorders( $o['backorders'] );
				$v->set_low_stock_amount( $o['low'] );
			}
			$v->set_stock_status( $o['status'] );
			$v->save();
		}
		dip_pack_pequeno( true );
	};
	$GLOBALS['dip_prueba_limpiar'][] = $restaurar_stock;

	$correos_pack = static fn() => array_values( array_filter( $GLOBALS['dip_prueba_correos'], static fn( $m ) => false !== strpos( (string) ( $m['subject'] ?? '' ), 'pack de 3 kg' ) ) );
	$kg_formulario = static function () use ( $tres ) {
		$padre = wc_get_product( $tres[0]->get_parent_id() );
		$kgs   = array();
		foreach ( $padre->get_available_variations() as $d ) $kgs[] = (string) ( 0 + dip_kg_producto( wc_get_product( $d['variation_id'] ) ) );
		return array_values( array_unique( $kgs ) );
	};
	$se_vende = static fn( $v ) => $v->is_purchasable() && $v->variation_is_visible();
	$escondida = static fn( $v ) => ! $v->is_purchasable() && ! $v->variation_is_visible();
	$tema = function_exists( 'dipt_marcadores' ) && function_exists( 'dipt_pk' );
	if ( ! $tema ) echo "(el tema dryicepack no está activo: no se prueban dipt_pk() ni los marcadores)\n";

	// Punto de partida: las dos variaciones de 3 kg con stock
	foreach ( $tres as $v ) {
		if ( ! $v->is_in_stock() ) $poner( $v, 'instock' );
	}
	$GLOBALS['dip_prueba_correos'] = array();
	$ok( 3 === dip_pack_pequeno( true ), 'con stock de 3 kg se vende el de 3 kg', dip_pack_pequeno() );
	foreach ( $tres as $v ) $ok( $se_vende( $v ), 'con stock de 3 kg: la variación ' . $v->get_id() . ' (3 kg) se puede comprar y se ve' );
	foreach ( $cinco as $v ) $ok( $escondida( $v ), 'con stock de 3 kg: la variación ' . $v->get_id() . ' (5 kg) ni se compra ni se ve' );
	$f = $kg_formulario();
	$ok( in_array( '3', $f, true ) && ! in_array( '5', $f, true ), 'ficha con stock de 3 kg: sale el 3 kg y no el 5 kg', $f );
	$rec = dip_precios_recogida();
	$ok( array( 3, 10, 15, 20 ) === array_keys( $rec ) && array( 35.0, 60.0, 90.0, 115.0 ) === array_values( $rec ), 'dip_precios_recogida() = 3 kg 35 · 10 kg 60 · 15 kg 90 · 20 kg 115 (IVA incluido)', $rec );
	if ( $tema ) {
		$ok( 3 === dipt_pk(), 'dipt_pk() = 3', dipt_pk() );
		$esperados = array(
			array( '{pk}', 'es', '3' ),
			array( '{recogida:pk}', 'es', '35 €' ),
			array( '{recogida:10}', 'es', '60 €' ),
			array( '{recogida:20}', 'es', '115 €' ),
			array( '{recogida:10}', 'en', '€60' ),
			array( '{precio:10}', 'es', '64,49 €' ),
			array( '{sabado_recogida}', 'es', '10 €' ),
			array( '{sabado_envio}', 'es', '11,74 €' ),
		);
		if ( '2026-10-31' >= $hoy && in_array( '2026-10-31', dip_sabados_recogida_sin_suplemento(), true ) ) {
			$esperados[] = array( 'Recogida en sábado: +{sabado_recogida} (IVA incluido).{sabado_libre}', 'es', 'Recogida en sábado: +10 € (IVA incluido). Salvo el 31 de octubre: sin suplemento.' );
			$esperados[] = array( '{sabado_libre}', 'ca', " Excepte el 31 d'octubre: sense suplement." );
			$esperados[] = array( '{sabado_libre}', 'en', ' Except 31 October: no surcharge.' );
		}
		foreach ( $esperados as $e ) {
			$valor = dipt_marcadores( $e[0], $e[1] );
			$ok( $e[2] === $valor, "marcador {$e[0]} ({$e[1]})", $valor );
		}
	}

	// Se acaba una variación de 3 kg: sigue el 3 kg y no hay aviso
	$poner( $tres[0], 'outofstock' );
	$ok( ! $tres[0]->is_in_stock(), 'la variación ' . $tres[0]->get_id() . ' queda sin stock' );
	$ok( 3 === dip_pack_pequeno( true ), 'con una variación de 3 kg todavía con stock sigue el de 3 kg', dip_pack_pequeno() );
	$ok( ! $correos_pack(), 'sin aviso mientras queda una variación de 3 kg', $correos_pack() );

	// Se acaba la otra: entra el 5 kg y llega un aviso
	$poner( $tres[1], 'outofstock' );
	$ok( 5 === dip_pack_pequeno( true ), 'sin stock de 3 kg: dip_pack_pequeno(true) = 5', dip_pack_pequeno() );
	foreach ( $cinco as $v ) $ok( $se_vende( $v ), 'sin stock de 3 kg: la variación ' . $v->get_id() . ' (5 kg) se puede comprar y se ve' );
	foreach ( $tres as $v ) $ok( $escondida( $v ), 'sin stock de 3 kg: la variación ' . $v->get_id() . ' (3 kg) ni se compra ni se ve' );
	$f = $kg_formulario();
	$ok( in_array( '5', $f, true ) && ! in_array( '3', $f, true ), 'ficha sin stock de 3 kg: sale el 5 kg y no el 3 kg', $f );
	if ( $tema ) {
		$ok( 5 === dipt_pk(), 'dipt_pk() = 5', dipt_pk() );
		$ok( '5' === dipt_marcadores( '{pk}', 'es' ), 'marcador {pk} con el 5 kg', dipt_marcadores( '{pk}', 'es' ) );
	}
	// {recogida:pk} y {precio:pk} leen listas que se memorizan una vez por petición (dip_precios_recogida() y
	// dipt_variaciones(), que en esta petición ya se calcularon con el 3 kg a la venta y sin el 5 kg). En una visita
	// real el cambio de pack llega en otra petición y salen bien ("40 €", "41,02 €"): aquí se comprueban las piezas
	// que no memorizan, con la misma regla que el marcador.
	$ok( $casi( dip_precio_recogida_producto( $cinco[0] ), 40 ), 'precio de recogida del 5 kg = 40 € (33,90 × 1,21 = 41,02 → múltiplo de 5 a la baja)', dip_precio_recogida_producto( $cinco[0] ) );
	$ok( $casi( round( (float) $cinco[0]->get_price( 'edit' ) * dip_factor_iva(), 2 ), 41.02 ), 'precio de la web del 5 kg con IVA = 41,02 €', $cinco[0]->get_price( 'edit' ) );
	$ok( $casi( dip_precio_recogida_de( 33.90 ), 40 ) && $casi( dip_precio_recogida_de( 29.80 ), 35 ), 'dip_precio_recogida_de(): 33,90 → 40 € y 29,80 → 35 €' );
	$avisos = $correos_pack();
	$ok( 1 === count( $avisos ), 'un email de aviso al acabarse el 3 kg', count( $avisos ) );
	if ( $avisos ) {
		$para = is_array( $avisos[0]['to'] ) ? implode( ',', $avisos[0]['to'] ) : (string) $avisos[0]['to'];
		$ok( dip_ajuste( 'email_avisos' ) === $para, 'el aviso va al correo de avisos', $para );
		$ok( 'Dryicepack: se ha acabado el pack de 3 kg, ya se vende el de 5 kg' === $avisos[0]['subject'], 'asunto del aviso', $avisos[0]['subject'] );
	}
	$ok( (bool) get_option( 'dip_pack_pequeno_aviso' ), 'el aviso queda anotado (dip_pack_pequeno_aviso)' );
	// No se repite: otro aviso de "sin stock" de la misma variación (p. ej. otra venta) no manda nada
	do_action( 'woocommerce_variation_set_stock_status', $tres[1]->get_id(), 'outofstock', $tres[1] );
	do_action( 'woocommerce_variation_set_stock_status', $tres[0]->get_id(), 'outofstock', $tres[0] );
	$ok( 1 === count( $correos_pack() ), 'el aviso no se repite', count( $correos_pack() ) );
	// Si se repone y se vuelve a acabar, vuelve a avisar
	$poner( $tres[0], 'instock' );
	$ok( 3 === dip_pack_pequeno( true ) && false === get_option( 'dip_pack_pequeno_aviso', false ), 'al reponer el 3 kg vuelve el 3 kg y se borra la marca del aviso', dip_pack_pequeno() );
	$poner( $tres[0], 'outofstock' );
	$ok( 2 === count( $correos_pack() ) && 5 === dip_pack_pequeno( true ), 'repuesto y acabado otra vez: segundo aviso y otra vez 5 kg', count( $correos_pack() ) );

	// Un mismo pedido se lleva las últimas cajas de 3 mm y de 16 mm. La venta cambia el stock en objetos nuevos
	// (wc_update_product_stock), así que los que guarda dip_variaciones_de_kg() siguen diciendo "con stock": tiene que avisar igual.
	$poner( $tres[0], 'instock' );
	$poner( $tres[1], 'instock' );
	$antes_venta = array();
	foreach ( $tres as $v ) {
		$f = wc_get_product( $v->get_id() );
		$antes_venta[ $v->get_id() ] = array( $f->get_manage_stock( 'edit' ), $f->get_stock_quantity( 'edit' ), $f->get_stock_status( 'edit' ), $f->get_backorders( 'edit' ) );
		$f->set_manage_stock( true );
		$f->set_stock_quantity( 1 );
		$f->set_backorders( 'no' );
		$f->save();
	}
	$n_avisos = count( $correos_pack() );
	foreach ( $tres as $v ) wc_update_product_stock( wc_get_product( $v->get_id() ), 1, 'decrease' );
	$vendidas = array_map( static fn( $v ) => wc_get_product( $v->get_id() )->get_stock_status(), $tres );
	$ok( array( 'outofstock', 'outofstock' ) === $vendidas, 'venta de las últimas cajas de 3 y 16 mm: las dos quedan sin stock', $vendidas );
	$ok( $n_avisos + 1 === count( $correos_pack() ), 'venta de las últimas cajas de 3 y 16 mm en un mismo pedido: un aviso', count( $correos_pack() ) - $n_avisos );
	// Como antes de esta venta, con objetos leídos de la tienda (los de la memoria tienen datos viejos)
	foreach ( $antes_venta as $id => $o ) {
		$f = wc_get_product( $id );
		$f->set_manage_stock( $o[0] );
		if ( $o[0] ) $f->set_stock_quantity( $o[1] );
		$f->set_backorders( $o[3] );
		$f->set_stock_status( $o[2] );
		$f->save();
	}

	// Todo como estaba
	$restaurar_stock();
	$restaurar_aviso();
	$quedaba = false;
	foreach ( $tres as $v ) {
		$fresca = wc_get_product( $v->get_id() );
		$o      = $original[ $v->get_id() ];
		$ok( $fresca && $o['status'] === $fresca->get_stock_status( 'edit' ) && $o['manage'] === $fresca->get_manage_stock( 'edit' ) && ( ! $o['manage'] || (int) $o['qty'] === (int) $fresca->get_stock_quantity( 'edit' ) ), 'la variación ' . $v->get_id() . ' (3 kg) vuelve a su stock', $fresca ? array( $fresca->get_stock_status( 'edit' ), $fresca->get_manage_stock( 'edit' ), $fresca->get_stock_quantity( 'edit' ) ) : null );
		if ( 'outofstock' !== $o['status'] ) $quedaba = true;
	}
	$ok( ( $quedaba ? 3 : 5 ) === dip_pack_pequeno( true ), 'después de la prueba se vende el mismo pack que antes', dip_pack_pequeno() );
	$ok( get_option( 'dip_pack_pequeno_aviso', null ) === $aviso_antes, 'la marca del aviso queda como estaba' );

	/* =========================================================
	 * 2) Recogida: totales exactos, sábado y vuelta al envío (inc/precios-recogida.php, inc/entrega.php)
	 * ========================================================= */
	echo "== Recogida y carrito\n";
	add_filter( 'woocommerce_variation_is_purchasable', '__return_true', 99 ); // para probar también el pack de 5 kg
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	$padre = wc_get_product( $post->ID );
	$vars  = array();
	foreach ( $padre->get_children() as $id ) {
		$v = wc_get_product( $id );
		if ( '3mm' !== strtolower( str_replace( ' ', '', (string) $v->get_attribute( 'pa_formato' ) ) ) ) continue;
		$vars[ (string) ( 0 + dip_kg_producto( $v ) ) ] = $v;
	}
	ksort( $vars, SORT_NUMERIC );
	$esperado = array( '3' => 35, '5' => 40, '10' => 60, '15' => 90, '20' => 115 );
	echo 'Packs: ' . implode( ', ', array_map( static fn( $k, $v ) => $k . ' kg ' . $v->get_price(), array_keys( $vars ), $vars ) ) . "\n";
	foreach ( $esperado as $kg => $p ) {
		$ok( isset( $vars[ $kg ] ), "hay pack de $kg kg (3 mm)" );
		if ( isset( $vars[ $kg ] ) ) $ok( $casi( dip_precio_recogida_producto( $vars[ $kg ] ), $p ), "precio de recogida de $kg kg = $p €", dip_precio_recogida_producto( $vars[ $kg ] ) );
	}

	// Identificadores reales de los métodos de envío del servidor
	WC()->cart->empty_cart();
	WC()->cart->add_to_cart( $vars['10']->get_parent_id(), 1, $vars['10']->get_id() );
	WC()->cart->calculate_totals();
	$rate_envio = $rate_recogida = '';
	foreach ( array_keys( (array) ( WC()->shipping()->get_packages()[0]['rates'] ?? array() ) ) as $rid ) {
		if ( dip_es_recogida( $rid ) ) $rate_recogida = $rate_recogida ?: $rid;
		else $rate_envio = $rate_envio ?: $rid;
	}
	echo "Métodos: $rate_envio / $rate_recogida\n";
	$ok( '' !== $rate_envio && '' !== $rate_recogida, 'hay método de envío y de recogida' );

	// Fechas reales del calendario (la prueba no caduca): entre semana, primer sábado con suplemento y 31/10/2026
	$destino  = array( 'cp' => '08013', 'poblacion' => 'Barcelona' );
	$r_semana = $r_sabado = $e_semana = $e_sabado = '';
	foreach ( dip_fechas_disponibles( 'recogida', 0 ) as $f ) {
		if ( ! $f['sabado'] && ! $r_semana ) $r_semana = $f['fecha'];
		if ( $f['sabado'] && ! $r_sabado && ! in_array( $f['fecha'], dip_sabados_recogida_sin_suplemento(), true ) ) $r_sabado = $f['fecha'];
	}
	foreach ( dip_fechas_disponibles( 'envio', 0, null, $destino ) as $f ) {
		if ( ! $f['sabado'] && ! $e_semana ) $e_semana = $f['fecha'];
		if ( $f['sabado'] && ! $e_sabado ) $e_sabado = $f['fecha'];
	}
	$r_libre = ( dip_fecha_es_valida( '2026-10-31', 'recogida' ) && in_array( '2026-10-31', dip_sabados_recogida_sin_suplemento(), true ) ) ? '2026-10-31' : '';
	if ( dip_fecha_es_valida( '2026-10-31', 'envio', $destino ) ) $e_sabado = '2026-10-31';
	echo "Fechas: recogida $r_semana, sábado $r_sabado" . ( $r_libre ? ", sin suplemento $r_libre" : '' ) . "; envío $e_semana, sábado $e_sabado\n";
	$ok( $r_semana && $r_sabado && $e_semana && $e_sabado, 'el calendario da fechas de recogida y de envío, entre semana y en sábado' );

	$carrito = static function ( array $lineas, $metodo, $fecha ) use ( &$vars, $rate_envio, $rate_recogida ) {
		WC()->cart->empty_cart();
		WC()->session->set( 'chosen_shipping_methods', array( 'recogida' === $metodo ? $rate_recogida : $rate_envio ) );
		WC()->session->set( 'dip_fecha', $fecha );
		foreach ( $lineas as $kg => $n ) WC()->cart->add_to_cart( $vars[ $kg ]->get_parent_id(), $n, $vars[ $kg ]->get_id() );
		WC()->cart->calculate_totals();
		return WC()->cart;
	};

	// Cada pack, de 1 caja al máximo de 250 kg
	foreach ( $vars as $kg => $v ) {
		if ( ! isset( $esperado[ $kg ] ) ) continue;
		for ( $n = 1; $n * (float) $kg <= 250; $n++ ) {
			$cart  = $carrito( array( $kg => $n ), 'recogida', $r_semana );
			$total = (float) $cart->get_total( 'edit' );
			$ok( $casi( $total, $esperado[ $kg ] * $n ), "$n × $kg kg en recogida", array( $total, $esperado[ $kg ] * $n ) );
			$ok( abs( (float) $cart->get_cart_contents_total() + (float) $cart->get_cart_contents_tax() - $total ) < 0.006, "$n × $kg kg en recogida: base + IVA = total" );
		}
	}
	// Carritos mezclados al azar (semilla fija)
	mt_srand( 20261003 );
	for ( $i = 0; $i < 150; $i++ ) {
		$lineas = array();
		$kgs    = 0;
		$esp    = 0;
		foreach ( array_keys( $esperado ) as $kg ) {
			if ( ! isset( $vars[ $kg ] ) || mt_rand( 0, 1 ) ) continue;
			$n = mt_rand( 1, 4 );
			if ( $kgs + $n * $kg > 250 ) continue;
			$lineas[ $kg ] = $n;
			$kgs          += $n * $kg;
			$esp          += $n * $esperado[ $kg ];
		}
		if ( ! $lineas ) continue;
		$total = (float) $carrito( $lineas, 'recogida', $r_semana )->get_total( 'edit' );
		$ok( $casi( $total, $esp ), 'mezcla ' . wp_json_encode( $lineas ) . ' en recogida', array( $total, $esp ) );
	}
	// Sábado de recogida: +10,00 € justos (8,26 + 1,74)
	$cart = $carrito( array( '10' => 2 ), 'recogida', $r_sabado );
	$fees = array_values( $cart->get_fees() );
	$ok( $casi( $cart->get_total( 'edit' ), 130 ), "recogida el sábado $r_sabado, 2 × 10 kg = 130,00 €", $cart->get_total( 'edit' ) );
	$ok( 1 === count( $fees ) && 'dip-sabado-recogida' === $fees[0]->id && $casi( $fees[0]->total, 8.26 ) && $casi( $fees[0]->tax, 1.74 ), 'suplemento de recogida: 8,26 € + 1,74 €', array_map( static fn( $f ) => array( $f->id, $f->total, $f->tax ), $fees ) );
	// 31/10/2026: recogida sin suplemento
	if ( $r_libre ) {
		$cart = $carrito( array( '10' => 2 ), 'recogida', $r_libre );
		$ok( $casi( $cart->get_total( 'edit' ), 120 ) && ! $cart->get_fees(), 'recogida el sábado 31/10/2026: 120,00 €, sin suplemento', $cart->get_total( 'edit' ) );
	}
	// Envío: precio de la web y suplemento de 9,70 € + IVA (también el 31/10)
	$cart = $carrito( array( '10' => 1 ), 'envio', $e_semana );
	$ok( $casi( $cart->get_subtotal(), 53.30 ), 'envío 10 kg: subtotal 53,30 € sin IVA', $cart->get_subtotal() );
	$cart = $carrito( array( '10' => 1 ), 'envio', $e_sabado );
	$fees = array_values( $cart->get_fees() );
	$ok( 1 === count( $fees ) && $casi( $fees[0]->total, 9.70 ) && $casi( $fees[0]->tax, 2.04 ), "envío el sábado $e_sabado: 9,70 € + 2,04 € de IVA", array_map( static fn( $f ) => array( $f->id, $f->total, $f->tax ), $fees ) );
	// Recogida y después envío en la misma petición: vuelve al precio de la web
	$cart = $carrito( array( '20' => 1 ), 'recogida', $r_semana );
	WC()->session->set( 'chosen_shipping_methods', array( $rate_envio ) );
	$cart->calculate_totals();
	$ok( $casi( $cart->get_subtotal(), 98.60 ), 'de recogida a envío: subtotal 98,60 €', $cart->get_subtotal() );
	// Más de 150 kg y el cliente cambia a un CP de fuera de la provincia de Barcelona con el envío elegido: solo queda la
	// recogida y WooCommerce cambia el método DENTRO del cálculo, después de poner los precios. El total tiene que salir
	// ya a precio de recogida (10 × 20 kg = 1.150,00 €), no a precio de la web (1.193,06 €).
	$cart = $carrito( array( '20' => 10 ), 'envio', $e_semana );
	$ok( ! dip_eligio_recogida() && $casi( $cart->get_subtotal(), 986 ), '200 kg a Barcelona con envío: precio de la web', $cart->get_subtotal() );
	foreach ( array( 'billing', 'shipping' ) as $tipo ) {
		$c->{"set_{$tipo}_postcode"}( '28001' );
		$c->{"set_{$tipo}_city"}( 'Madrid' );
		$c->{"set_{$tipo}_state"}( 'M' );
	}
	$cart->calculate_totals();
	$ok( dip_eligio_recogida(), '200 kg a Madrid: WooCommerce deja solo la recogida', WC()->session->get( 'chosen_shipping_methods' ) );
	$ok( $casi( $cart->get_total( 'edit' ), 1150 ), '200 kg a Madrid: el total sale ya a precio de recogida (1.150,00 €)', $cart->get_total( 'edit' ) );
	foreach ( array( 'billing', 'shipping' ) as $tipo ) {
		$c->{"set_{$tipo}_postcode"}( '08013' );
		$c->{"set_{$tipo}_city"}( 'Barcelona' );
		$c->{"set_{$tipo}_state"}( 'B' );
	}

	/* =========================================================
	 * 3) Factura simplificada de un pedido de recogida (inc/factura-simplificada.php)
	 * ========================================================= */
	echo "== Factura simplificada de un pedido de recogida\n";
	$serie        = dip_fs_prefijo() . dip_fs_ahora()->format( 'Y' );
	$numero_antes = dip_fs_ultimo_numero( $serie );
	$cart         = $carrito( array( '10' => 2 ), 'recogida', $r_sabado );
	$ok( $casi( $cart->get_total( 'edit' ), 130 ), 'carrito del pedido: 2 × 10 kg el sábado = 130,00 €', $cart->get_total( 'edit' ) );

	// Pedido real con las líneas que deja el checkout (WC_Checkout::set_data_from_cart, la misma función que usa el checkout)
	$pedido = wc_create_order( array( 'status' => 'pending', 'created_via' => 'prueba-recogida' ) );
	$pid    = $pedido->get_id();
	$GLOBALS['dip_prueba_limpiar'][] = static function () use ( $pid ) {
		$p = wc_get_order( $pid );
		if ( $p ) $p->delete( true );
	};
	WC()->checkout()->set_data_from_cart( $pedido );
	$pedido->set_billing_first_name( 'Laura García Pons' );
	$pedido->set_billing_email( 'prueba@example.com' );
	$pedido->set_billing_phone( '600123456' );
	$pedido->set_billing_address_1( 'Carrer de Mallorca 123' );
	$pedido->set_billing_postcode( '08013' );
	$pedido->set_billing_city( 'Barcelona' );
	$pedido->set_billing_state( 'B' );
	$pedido->set_billing_country( 'ES' );
	$pedido->set_payment_method( 'cod' );
	$pedido->set_payment_method_title( dip_textos_tienda( 'es' )['efectivo'] );
	dip_guardar_entrega( $pedido, $r_sabado, 'recogida' );
	if ( function_exists( 'dip_guardar_resumen_pedido' ) ) dip_guardar_resumen_pedido( $pedido );
	$pedido->update_meta_data( '_dip_idioma', 'es' );
	$pedido->update_meta_data( '_dip_empresa', 0 );
	$pedido->save();

	$pedido = wc_get_order( $pid ); // leído de la base de datos
	$ok( $casi( $pedido->get_total(), 130 ), 'total del pedido: 130,00 €', $pedido->get_total() );
	$cuotas = array_values( $pedido->get_items( 'fee' ) );
	$ok( 1 === count( $cuotas ) && $casi( $cuotas[0]->get_total(), 8.26 ) && $casi( $cuotas[0]->get_total_tax(), 1.74 ) && '10.00' === (string) $cuotas[0]->get_meta( '_dip_con_iva' ), 'suplemento en el pedido: 8,26 € + 1,74 € con _dip_con_iva = 10.00', $cuotas ? array( $cuotas[0]->get_total(), $cuotas[0]->get_total_tax(), $cuotas[0]->get_meta( '_dip_con_iva' ) ) : null );
	$hielo = array_values( $pedido->get_items() );
	$ok( 1 === count( $hielo ) && 2 === (int) $hielo[0]->get_quantity() && $casi( round( (float) $hielo[0]->get_total() + (float) $hielo[0]->get_total_tax(), 2 ), 120 ), 'línea del hielo en el pedido: 2 × 10 kg = 120,00 € IVA incluido', $hielo ? array( $hielo[0]->get_total(), $hielo[0]->get_total_tax() ) : null );
	$ok( 'recogida' === $pedido->get_meta( '_dip_metodo_entrega' ) && $r_sabado === $pedido->get_meta( '_dip_fecha_entrega' ), 'pedido de recogida con su fecha' );

	$lineas = dip_fs_lineas( $pedido );
	$ok( is_array( $lineas ), 'dip_fs_lineas() devuelve las líneas (no null)' );
	$lineas = (array) $lineas;
	$tipos  = array_column( $lineas, 'tipo' );
	$ok( array( 'hielo', 'sabado_recogida' ) === $tipos, 'líneas de la factura: hielo y sábado de recogida (la recogida gratis no es línea)', $tipos );
	if ( array( 'hielo', 'sabado_recogida' ) === $tipos ) {
		$ok( $casi( $lineas[0]['importe'], 120 ) && 2 === $lineas[0]['cantidad'] && $casi( $lineas[0]['kg'], 10 ) && 3 === $lineas[0]['mm'], 'línea del hielo: 2 × 10 kg de 3 mm, 120,00 €', $lineas[0] );
		$ok( $casi( $lineas[1]['importe'], 10 ), 'línea del sábado de recogida: 10,00 €', $lineas[1] );
	}
	$ok( $casi( round( array_sum( array_column( $lineas, 'importe' ) ), 2 ), 130 ), 'las líneas suman 130,00 €', array_sum( array_column( $lineas, 'importe' ) ) );
	list( $tipo, $base, $cuota, $total ) = dip_fs_desglose( $pedido );
	$ok( $casi( $tipo, 21 ) && $casi( $total, 130 ) && $casi( round( $base + $cuota, 2 ), $total ), 'desglose: IVA 21 %, base + cuota = 130,00 € al céntimo', array( $tipo, $base, $cuota, $total ) );
	$ok( $casi( $cuota, 22.57 ) && $casi( $base, 107.43 ), 'desglose: base 107,43 € + cuota 22,57 € (20,83 del hielo + 1,74 del sábado)', array( $base, $cuota ) );
	$datos = dip_fs_datos_factura( $pedido, dip_fs_ahora() );
	$ok( ! isset( $datos['numero'] ) && 'recogida' === $datos['metodo'] && $casi( $datos['total'], 130 ) && $casi( round( $datos['base'] + $datos['cuota'], 2 ), 130 ) && $datos['lineas'] === $lineas, 'dip_fs_datos_factura(): recogida, 130,00 €, mismas líneas y sin número', array_intersect_key( $datos, array_flip( array( 'metodo', 'base', 'cuota', 'total' ) ) ) );
	$ok( '' === dip_fs_motivo( $pedido, 'previsto' ), 'al recogerlo le tocará la factura simplificada', dip_fs_motivo( $pedido, 'previsto' ) );
	$efectivo = str_replace( "\u{00A0}", ' ', dip_texto_pago_efectivo( $pedido, 'es' ) );
	$ok( 'Pago en efectivo al recoger: 130,00 €. Trae el importe exacto: no tenemos cambio.' === $efectivo, 'texto del efectivo del pedido', $efectivo );
	// "Recalcular" en el escritorio deja también 130,00 € (el suplemento lleva _dip_con_iva)
	$pedido->calculate_totals( true );
	$pedido = wc_get_order( $pid );
	$ok( $casi( $pedido->get_total(), 130 ) && $casi( round( array_sum( array_column( (array) dip_fs_lineas( $pedido ), 'importe' ) ), 2 ), 130 ), 'después de Recalcular: 130,00 €', $pedido->get_total() );
	$ok( dip_fs_ultimo_numero( $serie ) === $numero_antes && '' === (string) $pedido->get_meta( '_dip_fs_numero' ), 'no se ha gastado ningún número de la serie ' . $serie, array( $numero_antes, dip_fs_ultimo_numero( $serie ) ) );
	$pedido->delete( true );
	$ok( ! wc_get_order( $pid ), 'pedido de prueba borrado' );

	WC()->cart->empty_cart();
	remove_filter( 'woocommerce_variation_is_purchasable', '__return_true', 99 );

	/* =========================================================
	 * 4) GET /wp-json/dip/v1/envio (inc/api-app.php)
	 * ========================================================= */
	echo "== Endpoint de la app (GET /wp-json/dip/v1/envio)\n";
	$pedir = static function ( $metodo = 'GET' ) {
		$resp = rest_do_request( new WP_REST_Request( $metodo, '/dip/v1/envio' ) );
		$json = wp_json_encode( rest_get_server()->response_to_data( $resp, false ) );
		return array( $resp, $json, json_decode( (string) $json, true ), json_decode( (string) $json ) );
	};
	$es_fecha = static fn( $s ) => is_string( $s ) && (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $s );
	$es_iso   = static fn( $s ) => is_string( $s ) && (bool) preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $s );
	$es_lista = static fn( $a ) => is_array( $a ) && array_values( $a ) === $a;
	$claves   = static function ( $a, array $esperadas, $donde ) use ( $ok ) {
		$tiene = is_array( $a ) ? array_keys( $a ) : array();
		sort( $tiene );
		sort( $esperadas );
		return $ok( $tiene === $esperadas, "claves de $donde", $tiene );
	};
	// Comprueba un día de mrw.dias: provincias de 2 cifras y poblaciones [{provincia, nombres}]
	$dia_ok = static function ( $fecha, $d ) use ( $ok, $claves, $es_lista ) {
		if ( ! $claves( $d, array( 'provincias', 'poblaciones' ), "mrw.dias[$fecha]" ) ) return;
		$ok( $es_lista( $d['provincias'] ) && ! array_filter( $d['provincias'], static fn( $p ) => ! is_string( $p ) || ! preg_match( '/^\d{2}$/', $p ) ), "mrw.dias[$fecha].provincias: lista de códigos de 2 cifras", $d['provincias'] );
		$ordenadas = $d['provincias'];
		sort( $ordenadas );
		$ok( $ordenadas === $d['provincias'] && array_values( array_unique( $d['provincias'] ) ) === $d['provincias'], "mrw.dias[$fecha].provincias ordenadas y sin repetir" );
		$ok( $es_lista( $d['poblaciones'] ), "mrw.dias[$fecha].poblaciones es una lista" );
		foreach ( (array) $d['poblaciones'] as $i => $p ) {
			$ok( is_array( $p ) && array( 'nombres', 'provincia' ) === ( static function ( $k ) { sort( $k ); return $k; } )( array_keys( (array) $p ) )
				&& is_string( $p['provincia'] ) && preg_match( '/^\d{2}$/', $p['provincia'] )
				&& $es_lista( $p['nombres'] ) && $p['nombres'] && ! array_filter( $p['nombres'], static fn( $n ) => ! is_string( $n ) || '' === $n ),
				"mrw.dias[$fecha].poblaciones[$i]: {provincia: 2 cifras, nombres: [texto]}", $p );
		}
	};

	list( $resp, $json, $d, $o ) = $pedir();
	$ok( 200 === $resp->get_status(), 'responde 200', $resp->get_status() );
	$ok( 'public, max-age=600' === ( $resp->get_headers()['Cache-Control'] ?? '' ), 'Cache-Control: public, max-age=600', $resp->get_headers() );
	$raw = (array) $resp->get_data();
	$claves( $d, array( 'version', 'generado', 'iva', 'tarifa', 'sabado', 'recogida', 'max_kg', 'max_kg_fuera_barcelona', 'reservas_dias', 'corte', 'dias_salida', 'dias_entrega', 'festivos_salida', 'mrw' ), 'la respuesta' );
	$ok( is_int( $d['version'] ?? null ) && $d['version'] >= 1, 'version: entero', $d['version'] ?? null );
	$ok( $es_iso( $d['generado'] ?? null ), 'generado: fecha ISO 8601 con zona', $d['generado'] ?? null );
	$ok( is_float( $raw['iva'] ?? null ) && $casi( $raw['iva'], 0.21 ), 'iva: 0.21 (decimal)', $raw['iva'] ?? null );
	$claves( $d['tarifa'] ?? null, array( 'hasta_2', 'hasta_5', 'hasta_10', 'kg_extra', 'peso_embalaje_por_caja', 'redondeo' ), 'tarifa' );
	foreach ( array( 'hasta_2' => 8.53, 'hasta_5' => 10.32, 'hasta_10' => 13.19, 'kg_extra' => 1.12, 'peso_embalaje_por_caja' => 1.0 ) as $k => $v ) {
		$ok( is_float( $raw['tarifa'][ $k ] ?? null ) && $casi( $raw['tarifa'][ $k ], $v ), "tarifa.$k = $v (decimal)", $raw['tarifa'][ $k ] ?? null );
	}
	$ok( 'al alza' === ( $d['tarifa']['redondeo'] ?? null ), 'tarifa.redondeo = «al alza»' );
	$claves( $d['sabado'] ?? null, array( 'suplemento_sin_iva' ), 'sabado' );
	$ok( is_float( $raw['sabado']['suplemento_sin_iva'] ?? null ) && $casi( $raw['sabado']['suplemento_sin_iva'], 9.70 ), 'sabado.suplemento_sin_iva = 9.70 (envío)' );
	$claves( $d['recogida'] ?? null, array( 'dias', 'tambien_festivos', 'redondeo_con_iva', 'suplemento_sabado_con_iva', 'pago' ), 'recogida' );
	$ok( array( 1, 2, 3, 4, 5, 6 ) === ( $d['recogida']['dias'] ?? null ), 'recogida.dias = lunes a sábado [1..6]', $d['recogida']['dias'] ?? null );
	$ok( true === ( $d['recogida']['tambien_festivos'] ?? null ), 'recogida.tambien_festivos = true' );
	$ok( 5 === ( $d['recogida']['redondeo_con_iva'] ?? null ), 'recogida.redondeo_con_iva = 5 (entero)' );
	$ok( is_float( $raw['recogida']['suplemento_sabado_con_iva'] ?? null ) && $casi( $raw['recogida']['suplemento_sabado_con_iva'], 10 ), 'recogida.suplemento_sabado_con_iva = 10.0 (decimal)' );
	$ok( 'efectivo' === ( $d['recogida']['pago'] ?? null ), 'recogida.pago = «efectivo»' );
	foreach ( array( 'max_kg' => 250, 'max_kg_fuera_barcelona' => 150, 'reservas_dias' => 90 ) as $k => $v ) {
		$ok( $v === ( $d[ $k ] ?? null ), "$k = $v (entero)", $d[ $k ] ?? null );
	}
	$ok( array( 'hora' => 12, 'minuto' => 0, 'zona' => 'Europe/Madrid' ) === ( $d['corte'] ?? null ), 'corte = 12:00 Europe/Madrid', $d['corte'] ?? null );
	$ok( array( 1, 2, 3, 4, 5 ) === ( $d['dias_salida'] ?? null ), 'dias_salida = lunes a viernes [1..5]' );
	$ok( array( 2, 3, 4, 5, 6 ) === ( $d['dias_entrega'] ?? null ), 'dias_entrega = martes a sábado [2..6]' );
	$fs = $d['festivos_salida'] ?? null;
	$ok( $es_lista( $fs ) && ! array_filter( (array) $fs, static fn( $f ) => ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $f ) ), 'festivos_salida: lista de fechas AAAA-MM-DD' );
	$ordenados = (array) $fs;
	sort( $ordenados );
	$ok( $ordenados === $fs && array_values( array_unique( (array) $fs ) ) === $fs, 'festivos_salida ordenados y sin repetir' );
	$manual = array_map( 'strval', array_keys( dip_festivos() ) );
	$ok( ! array_diff( $manual, (array) $fs ), 'festivos_salida lleva la lista manual de Ajustes', array_values( array_diff( $manual, (array) $fs ) ) );
	$claves( $d['mrw'] ?? null, array( 'leido', 'desde', 'hasta', 'umbral_provincia', 'dias' ), 'mrw' );
	$ok( null === ( $d['mrw']['leido'] ?? null ) || $es_iso( $d['mrw']['leido'] ), 'mrw.leido: null o ISO 8601', $d['mrw']['leido'] ?? null );
	$ok( is_float( $raw['mrw']['umbral_provincia'] ?? null ) && $casi( $raw['mrw']['umbral_provincia'], DIP_MRW_PROVINCIA ), 'mrw.umbral_provincia = ' . DIP_MRW_PROVINCIA );
	$ok( isset( $o->mrw->dias ) && is_object( $o->mrw->dias ), 'mrw.dias es un objeto JSON (también vacío)' );
	$guardados = array();
	foreach ( array_keys( (array) ( ( (array) get_option( 'dip_mrw', array() ) )['dias'] ?? array() ) ) as $f ) {
		if ( (string) $f >= $hoy ) $guardados[] = (string) $f;
	}
	sort( $guardados );
	$dias = (array) ( $d['mrw']['dias'] ?? array() );
	$ok( array_keys( $dias ) === $guardados, 'mrw.dias tiene todos los días guardados desde hoy (también los vacíos), en orden', array( count( $dias ), count( $guardados ) ) );
	$ok( ( $guardados ? $guardados[0] : null ) === ( $d['mrw']['desde'] ?? 'x' ) && ( $guardados ? end( $guardados ) : null ) === ( $d['mrw']['hasta'] ?? 'x' ), 'mrw.desde y mrw.hasta: primer y último día', array( $d['mrw']['desde'] ?? null, $d['mrw']['hasta'] ?? null ) );
	echo 'Días de MRW guardados en el servidor de prueba: ' . count( $guardados ) . "\n";
	foreach ( $dias as $fecha => $dia ) $dia_ok( $fecha, $dia );

	// Sin datos personales: ninguna clave de cliente, pedido o contacto, y ningún email en los valores
	$malas = array();
	$arroba = array();
	$recorrer = static function ( $v, $ruta ) use ( &$recorrer, &$malas, &$arroba ) {
		if ( is_array( $v ) ) {
			foreach ( $v as $k => $x ) {
				if ( is_string( $k ) && preg_match( '/(e-?mail|correo|tel[eé]fono|phone|m[oó]vil|whatsapp|direcci|address|billing|shipping|cliente|customer|usuario|user|pedido|order|nif|cif|dni|\bip\b|nombre_|apellido|first_name|last_name)/i', $k ) ) $malas[] = $ruta . '.' . $k;
				$recorrer( $x, $ruta . '.' . $k );
			}
		} elseif ( is_string( $v ) && false !== strpos( $v, '@' ) ) {
			$arroba[] = $ruta;
		}
	};
	$recorrer( $d, '' );
	$ok( ! $malas, 'ninguna clave con datos personales', $malas );
	$ok( ! $arroba, 'ningún email en los valores', $arroba );

	// Solo GET
	list( $resp ) = $pedir( 'POST' );
	$ok( in_array( $resp->get_status(), array( 404, 405 ), true ), 'POST no está permitido', $resp->get_status() );

	// Días de MRW conocidos (sin tocar la base de datos: filtro pre_option_dip_mrw): días vacíos, provincia cerrada
	// entera, población con dip_mrw_nombres(), oficina interna fuera, provincia de fuera de la península fuera y días pasados fuera
	$dia = static fn( $n ) => ( new DateTimeImmutable( 'today', dip_zona_horaria() ) )->modify( ( $n >= 0 ? '+' : '' ) . $n . ' days' )->format( 'Y-m-d' );
	$d1  = $dia( 4 );
	$d2  = $dia( 5 );
	$d3  = $dia( 6 );
	$leido = time() - 3600;
	$falso = array(
		'ok'   => $leido,
		'dias' => array(
			$dia( -1 ) => array( array( 'BARCELONA', 'Mataro' ) ),
			$d2        => array( array( 'ALAVA', 'Najera' ), array( 'ALAVA', 'Laudio/Llodio' ), array( 'BARCELONA', 'Mataro' ), array( 'BARCELONA', 'Logistica Barcelona' ), array( 'BALEARES', 'Palma' ), array( 'VALENCIA', 'Alzira' ) ),
			$d1        => array(),
			$d3        => array(),
		),
	);
	$con_falso = static function () use ( &$falso ) { return $falso; }; // por referencia: más abajo se cambia $falso
	add_filter( 'pre_option_dip_mrw', $con_falso );
	dip_mrw_dias( true );
	list( , $json, $d, $o ) = $pedir();
	remove_filter( 'pre_option_dip_mrw', $con_falso );
	dip_mrw_dias( true );
	$dias = (array) ( $d['mrw']['dias'] ?? array() );
	$ok( array( $d1, $d2, $d3 ) === array_keys( $dias ), 'MRW conocido: los 3 días que vienen, en orden, también los vacíos; el pasado, fuera', array_keys( $dias ) );
	$ok( array( 'provincias' => array(), 'poblaciones' => array() ) === ( $dias[ $d1 ] ?? null ) && array( 'provincias' => array(), 'poblaciones' => array() ) === ( $dias[ $d3 ] ?? null ), 'MRW conocido: día sin cierres con listas vacías' );
	$esperado_d2 = array(
		'provincias'  => array( '01' ),
		'poblaciones' => array(
			array( 'provincia' => '08', 'nombres' => dip_mrw_nombres( 'Mataro' ) ),
			array( 'provincia' => '46', 'nombres' => dip_mrw_nombres( 'Alzira' ) ),
		),
	);
	$ok( $esperado_d2 === ( $dias[ $d2 ] ?? null ), 'MRW conocido: Álava cerrada entera (01), Mataró y Alzira como poblaciones; sin oficina interna ni Baleares', $dias[ $d2 ] ?? null );
	foreach ( $dias as $fecha => $x ) $dia_ok( $fecha, $x );
	$ok( $d1 === ( $d['mrw']['desde'] ?? null ) && $d3 === ( $d['mrw']['hasta'] ?? null ), 'MRW conocido: desde y hasta', array( $d['mrw']['desde'] ?? null, $d['mrw']['hasta'] ?? null ) );
	$ok( dip_api_iso( $leido ) === ( $d['mrw']['leido'] ?? null ), 'MRW conocido: leido con la hora de la lectura', $d['mrw']['leido'] ?? null );
	$ok( in_array( $d2, (array) ( $d['festivos_salida'] ?? array() ), true ) && ! in_array( $d1, (array) ( $d['festivos_salida'] ?? array() ), true ), 'MRW conocido: el día que cierra Mataró está en festivos_salida' );

	$falso = array( 'ok' => $leido, 'dias' => array( $dia( -2 ) => array() ) ); // solo días pasados
	add_filter( 'pre_option_dip_mrw', $con_falso );
	dip_mrw_dias( true );
	list( , $json, $d, $o ) = $pedir();
	remove_filter( 'pre_option_dip_mrw', $con_falso );
	dip_mrw_dias( true );
	$ok( isset( $o->mrw->dias ) && is_object( $o->mrw->dias ) && ! (array) $o->mrw->dias && null === $o->mrw->desde && null === $o->mrw->hasta, 'MRW sin días: "dias": {} y desde/hasta null', $o->mrw ?? null );
} catch ( Throwable $e ) {
	$ok( false, 'excepción: ' . get_class( $e ) . ': ' . $e->getMessage() . ' en ' . basename( $e->getFile() ) . ':' . $e->getLine() );
} finally {
	if ( function_exists( 'WC' ) && WC()->cart ) WC()->cart->empty_cart();
	$limpiar();
}

echo "\n$pruebas comprobaciones, $fallos fallos\n";
