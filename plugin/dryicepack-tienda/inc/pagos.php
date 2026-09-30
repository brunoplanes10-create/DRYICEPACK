<?php
/**
 * Formas de pago.
 * - Con envío: tarjeta (por defecto) y Apple Pay / Google Pay de WooPayments. El efectivo no se ofrece.
 * - Recogida en Mataró: solo efectivo al recoger (método "contra reembolso" de WooCommerce, renombrado).
 * - Cuentas de empresa: además, "Factura mensual" (se sirve sin esperar el pago y se factura a fin de mes).
 * Nada de transferencias en la tienda: con un producto que sale al día siguiente no se puede esperar a comprobarlas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', static function () {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	class DIP_Pago_Factura_Mensual extends WC_Payment_Gateway {
		public function __construct() {
			$this->id                 = 'dip_factura_mensual';
			$this->method_title       = 'Factura mensual (cuentas de empresa)';
			$this->method_description = 'Solo la ven los clientes con cuenta de empresa activada en su perfil. El pedido pasa a "Procesando" sin cobro y se incluye en el resumen de facturación del mes.';
			$this->has_fields         = false;
			$this->supports           = array( 'products' );
			$this->init_form_fields();
			$this->init_settings();
			$this->enabled     = $this->get_option( 'enabled', 'yes' );
			$this->title       = dip_textos_tienda()['factura_mensual'];
			$this->description = dip_textos_tienda()['factura_mensual_d'];
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array( 'title' => 'Activar', 'type' => 'checkbox', 'label' => 'Ofrecer factura mensual a las cuentas de empresa', 'default' => 'yes' ),
			);
		}

		public function is_available() {
			return 'yes' === $this->enabled && is_user_logged_in() && function_exists( 'dip_es_cuenta_empresa' ) && dip_es_cuenta_empresa( get_current_user_id() );
		}

		public function process_payment( $pedido_id ) {
			$pedido = wc_get_order( $pedido_id );
			$pedido->update_meta_data( '_dip_factura_mensual', 1 );
			$pedido->update_status( 'processing', 'Cuenta de empresa: se factura a fin de mes.' );
			wc_maybe_reduce_stock_levels( $pedido_id );
			WC()->cart->empty_cart();
			return array( 'result' => 'success', 'redirect' => $this->get_return_url( $pedido ) );
		}
	}

	add_filter( 'woocommerce_payment_gateways', static function ( $pasarelas ) {
		$pasarelas[] = 'DIP_Pago_Factura_Mensual';
		return $pasarelas;
	} );
}, 30 );

add_filter( 'woocommerce_available_payment_gateways', static function ( $pasarelas ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ! is_array( $pasarelas ) ) return $pasarelas;
	$t = dip_textos_tienda();

	if ( dip_eligio_recogida() ) {
		$quedan = array();
		if ( isset( $pasarelas['dip_factura_mensual'] ) ) $quedan['dip_factura_mensual'] = $pasarelas['dip_factura_mensual'];
		if ( isset( $pasarelas['cod'] ) ) {
			$pasarelas['cod']->title       = $t['efectivo'];
			$pasarelas['cod']->description = $t['efectivo_desc'];
			$quedan['cod']                 = $pasarelas['cod'];
		}
		return $quedan ?: $pasarelas; // si el efectivo no está activado, no se deja el pedido sin forma de pago
	}

	unset( $pasarelas['cod'] );

	// Tarjeta primero (WooPayments o Stripe), factura mensual después, el resto detrás.
	$orden = array( 'woocommerce_payments', 'stripe', 'dip_factura_mensual' );
	uksort( $pasarelas, static function ( $a, $b ) use ( $orden ) {
		$pa = array_search( $a, $orden, true );
		$pb = array_search( $b, $orden, true );
		return ( false === $pa ? 99 : $pa ) <=> ( false === $pb ? 99 : $pb );
	} );
	return $pasarelas;
}, 9999 );
