<?php
/**
 * Inicio · "Cadena de frío"
 * Foco: comprar hoy (calculadora con precio, envío y día de entrega reales) o programar el suministro
 * (clientes recurrentes y de volumen). Cada sección tiene algo vivo: caja EPS que respira, reloj de corte,
 * rutas desde Mataró, vapor que cae, pellets que subliman.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$c    = dipt_contacto();
$sum  = home_url( '/programar-suministro-de-hielo-seco/' );
$apps = home_url( '/aplicaciones-del-hielo-seco/' );
$mapa = json_decode( (string) file_get_contents( DIPT_DIR . '/partes/mapa-datos.json' ), true );

$sectores = array(
	array( 'icono' => 'camion', 'titulo' => 'Transporte y cadena de frío', 'texto' => 'Congelados, farmacia y última milla: frío seco que no moja la carga. Nuggets de 16 mm para trayectos largos y suministro programado para tus rutas.', 'url' => $apps . '#apps-alimentacion', 'img' => '/wp-content/uploads/2025/11/ChatGPT-Image-21-nov-2025-13_08_51-1.png.webp', 'alt' => 'Cajas con hielo seco para transporte refrigerado' ),
	array( 'icono' => 'copa', 'titulo' => 'Hostelería, ocio y eventos', 'texto' => 'Niebla baja, coctelería con doble recipiente y catering.', 'url' => $apps . '#apps-ocio', 'img' => '/wp-content/uploads/2025/11/ChatGPT-Image-25-nov-2025-10_02_21-1.png.webp', 'alt' => 'Efecto de niebla con hielo seco en un evento' ),
	array( 'icono' => 'matraz', 'titulo' => 'Laboratorio, pharma y docencia', 'texto' => 'Muestras a −78,5 °C y prácticas en el aula.', 'url' => $apps . '#apps-salud', 'img' => '/wp-content/uploads/2025/11/ChatGPT-Image-23-nov-2025-14_51_32-1.png.webp', 'alt' => 'Hielo seco para laboratorio y farmacia' ),
	array( 'icono' => 'fabrica', 'titulo' => 'Industria', 'texto' => 'Limpieza criogénica y control de temperatura en procesos.', 'url' => $apps . '#apps-industria', 'img' => '/wp-content/uploads/2025/11/ChatGPT-Image-24-nov-2025-09_11_36-1-1.png.webp', 'alt' => 'Hielo seco en procesos industriales' ),
	array( 'icono' => 'casa', 'titulo' => 'Particulares y fiestas', 'texto' => 'Halloween, bodas, cumpleaños o una avería del congelador.', 'url' => dipt_producto_url(), 'img' => '/wp-content/uploads/2025/11/ChatGPT-Image-25-nov-2025-15_03_04-1.png.webp', 'alt' => 'Hielo seco para fiestas en casa' ),
);

$faq = array(
	array( 'q' => '¿Qué diferencia hay entre el hielo seco y el hielo de agua?', 'a' => 'El hielo de agua se funde y deja agua. El hielo seco es CO₂ sólido a −78,5 °C y sublima: pasa de sólido a gas sin mojar nada. Por eso enfría más y no deja humedad, algo clave en alimentación y farmacia.' ),
	array( 'q' => '¿Qué formato elijo: 3 mm o 16 mm?', 'a' => 'Pellets de 3 mm para enfriar rápido, procesos, niebla y coctelería. Nuggets de 16 mm cuando tiene que durar más: envíos y cadena de frío. Mismo precio en los dos.' ),
	array( 'q' => '¿Cuánto dura el hielo seco dentro de la caja EPS?', 'a' => 'Depende de la cantidad, del formato y de la temperatura. En su caja EPS con la tapa puesta, lo habitual es entre 24 y 48 horas. Cuanto más hielo seco hay en la caja, más aguanta.' ),
	array( 'q' => '¿Cuándo llega mi pedido?', 'a' => 'Si lo confirmas antes de las 12:00 de un día laborable, sale ese mismo día y lo recibes al día siguiente por la mañana en la España peninsular. No entregamos en domingo ni en lunes; el sábado, según zona y con suplemento.' ),
	array( 'q' => '¿Puedo programar entregas fijas?', 'a' => 'Sí. Con el suministro programado eliges la frecuencia (semanal, quincenal o mensual) y la cantidad, con precio preferente por volumen y prioridad de reparto. Ajustamos cantidades cuando cambie tu consumo.' ),
	array( 'q' => '¿Puedo recogerlo en vuestra nave?', 'a' => 'Sí, en Camí Ca La Madrona 19 D, Mataró (Barcelona), de lunes a viernes de 9:00 a 18:00 avisando antes. La recogida es gratis y se paga en efectivo al recoger.' ),
);
?>

<!-- 1 · PORTADA -->
<section class="portada sobre-oscuro" aria-labelledby="portada-titulo">
	<div class="portada__rejilla" aria-hidden="true"></div>
	<?php echo dipt_niebla( 'niebla--portada', 2 ); // phpcs:ignore ?>
	<div class="contenedor portada__in">
		<div class="portada__texto">
			<p class="antetitulo">Hielo seco · Mataró → toda la península</p>
			<h1 id="portada-titulo">Hielo seco con entrega en 24 h en toda la península</h1>
			<p class="portada__entrada">Pide un pack hoy o programa tu suministro semanal, quincenal o mensual con precio preferente. Lo preparamos en nuestra nave de Mataró y sale el mismo día si lo confirmas antes de las 12:00.</p>
			<div class="botonera">
				<?php echo dipt_boton( 'Comprar hielo seco', '#calcula', 'senal' ); // phpcs:ignore ?>
				<?php echo dipt_boton( 'Programar suministro', '#suministro', 'linea-clara' ); // phpcs:ignore ?>
			</div>
			<?php echo dipt_reloj_corte( 'reloj-corte--portada' ); // phpcs:ignore ?>
		</div>
		<div class="portada__escena">
			<?php echo dipt_caja_eps( 'caja3d--portada' ); // phpcs:ignore ?>
			<?php echo dipt_etiqueta( array( 'clase' => 'etiqueta--portada' ) ); // phpcs:ignore ?>
			<p class="portada__dato portada__dato--temp" aria-hidden="true"><b data-cuenta="-78.5" data-desde="20" data-decimales="1">−78,5</b> °C</p>
		</div>
	</div>
	<?php
	$cinta = array( 'pellet' => 'Pellets de 3 mm', 'nugget' => 'Nuggets de 16 mm', 'caja' => 'Caja EPS de 40 mm incluida', 'reloj' => 'Corte a las 12:00', 'camion' => 'Hasta 250 kg por pedido', 'pin' => 'Recogida gratis en Mataró', 'termometro' => '−78,5 °C' );
	?>
	<div class="cinta" aria-label="Datos clave">
		<div class="cinta__pista">
			<?php for ( $copia = 0; $copia < 2; $copia++ ) : // segunda copia para el bucle continuo, oculta a lectores de pantalla ?>
				<ul class="cinta__lista"<?php echo $copia ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $cinta as $ico => $txt ) : ?>
						<li><?php echo dipt_icono( $ico ); // phpcs:ignore ?><?php echo esc_html( $txt ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endfor; ?>
		</div>
	</div>
</section>

<!-- 2 · PARA QUÉ LO NECESITAS -->
<section class="seccion seccion--escarcha sectores" aria-labelledby="sectores-titulo">
	<?php echo dipt_niebla( 'niebla--suave', 1 ); // phpcs:ignore ?>
	<div class="contenedor">
		<header class="cabecera-seccion" data-condensa>
			<p class="antetitulo">Tu sector</p>
			<h2 id="sectores-titulo">¿Para qué lo necesitas?</h2>
			<p>Cada uso pide un formato, una cantidad y un ritmo distintos. Elige el tuyo y te contamos cómo lo trabajamos.</p>
		</header>
		<div class="sectores__rejilla" data-condensa="grupo">
			<?php foreach ( $sectores as $i => $s ) : ?>
				<a class="sector<?php echo 0 === $i ? ' sector--destacado' : ''; ?>" href="<?php echo esc_url( $s['url'] ); ?>" data-inclina>
					<span class="sector__foto"><img src="<?php echo esc_url( dipt_media( $s['img'] ) ); ?>" alt="<?php echo esc_attr( $s['alt'] ); ?>" width="800" height="600" loading="lazy" decoding="async"></span>
					<span class="sector__cuerpo">
						<span class="sector__icono"><?php echo dipt_icono( $s['icono'] ); // phpcs:ignore ?></span>
						<span class="sector__titulo"><?php echo esc_html( $s['titulo'] ); ?></span>
						<span class="sector__texto"><?php echo esc_html( $s['texto'] ); ?></span>
						<span class="sector__ir">Ver cómo lo trabajamos <?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 3 · CALCULADORA -->
<section class="seccion calcula" id="calcula" aria-labelledby="calcula-titulo">
	<div class="contenedor calcula__in">
		<div class="calcula__panel" data-calculadora>
			<header class="cabecera-seccion" data-condensa>
				<p class="antetitulo">Compra en un minuto</p>
				<h2 id="calcula-titulo">Elige formato y cantidad</h2>
				<p>Precio, envío y día de entrega al momento. Sin registrarte.</p>
			</header>

			<fieldset class="calcula__grupo">
				<legend>Formato</legend>
				<div class="formatos">
					<label class="formato">
						<input type="radio" name="dipt-formato" value="3mm" checked>
						<span class="formato__foto"><img src="<?php echo esc_url( dipt_img( 'formato-3mm-480.webp' ) ); ?>" alt="" width="480" height="599" loading="lazy"></span>
						<span class="formato__txt"><b>Pellets de 3 mm</b><small>Enfrían rápido: niebla, coctelería y procesos</small></span>
					</label>
					<label class="formato">
						<input type="radio" name="dipt-formato" value="16mm">
						<span class="formato__foto"><img src="<?php echo esc_url( dipt_img( 'formato-16mm-480.webp' ) ); ?>" alt="" width="480" height="600" loading="lazy"></span>
						<span class="formato__txt"><b>Nuggets de 16 mm</b><small>Duran más: envíos y cadena de frío</small></span>
					</label>
				</div>
			</fieldset>

			<fieldset class="calcula__grupo">
				<legend>Kilos por caja</legend>
				<div class="kilos" role="radiogroup">
					<?php foreach ( array( 3, 10, 15, 20 ) as $i => $kg ) : ?>
						<label class="kilo"><input type="radio" name="dipt-kg" value="<?php echo (int) $kg; ?>"<?php checked( 1 === $i ); ?>><span><b class="num"><?php echo (int) $kg; ?></b> kg</span></label>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<fieldset class="calcula__grupo calcula__grupo--fila">
				<legend>Cajas</legend>
				<div class="cajas">
					<button type="button" class="cajas__btn" data-menos aria-label="Una caja menos">−</button>
					<output class="cajas__num num" data-cajas aria-live="polite">1</output>
					<button type="button" class="cajas__btn" data-mas aria-label="Una caja más">+</button>
				</div>
				<p class="nota calcula__max">Hasta 250 kg por pedido. ¿Más? <a href="<?php echo esc_url( $sum ); ?>">Te lo programamos</a>.</p>
			</fieldset>
		</div>

		<aside class="calcula__resumen" aria-live="polite">
			<div class="medidor" data-medidor aria-hidden="true">
				<div class="medidor__caja"><span class="medidor__carga" data-carga></span><i class="medidor__lluvia"></i></div>
				<p class="medidor__kg"><b class="num" data-total-kg>10</b> kg de hielo seco</p>
			</div>
			<?php echo dipt_etiqueta( array( 'clase' => 'etiqueta--calcula', 'peso' => '10 kg', 'formato' => '3 mm' ) ); // phpcs:ignore ?>
			<dl class="cuenta">
				<div><dt>Hielo seco</dt><dd class="num" data-precio>—</dd></div>
				<div><dt>Envío <small data-facturable></small></dt><dd class="num" data-envio>—</dd></div>
				<div class="cuenta__total"><dt>Total <small>IVA incluido</small></dt><dd class="num" data-total>—</dd></div>
			</dl>
			<a class="btn btn--senal calcula__comprar" href="<?php echo esc_url( dipt_producto_url() ); ?>" data-comprar data-iman><span>Añadir al carrito</span><span class="btn__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></a>
			<p class="nota calcula__nota">Recogida en Mataró: gratis. El envío se calcula sobre el peso del hielo más 1 kg por caja.</p>
		</aside>
	</div>
</section>

<!-- 4 · SUMINISTRO PROGRAMADO -->
<section class="seccion seccion--noche suministro" id="suministro" aria-labelledby="suministro-titulo">
	<?php echo dipt_niebla( '', 2 ); // phpcs:ignore ?>
	<div class="contenedor suministro__in">
		<div class="suministro__texto" data-condensa>
			<p class="antetitulo">Clientes recurrentes</p>
			<h2 id="suministro-titulo">¿Lo necesitas cada semana? Programa tu suministro</h2>
			<p class="suministro__entrada">Eliges frecuencia y cantidad y nos olvidamos de pedidos sueltos. Precio preferente por volumen y frecuencia, y prioridad de reparto.</p>
			<ul class="lista-check">
				<li><?php echo dipt_icono( 'euro' ); // phpcs:ignore ?>Mejor precio cuanto más y más a menudo</li>
				<li><?php echo dipt_icono( 'repetir' ); // phpcs:ignore ?>Ajustamos cantidad y frecuencia cuando cambie tu consumo</li>
				<li><?php echo dipt_icono( 'camion' ); // phpcs:ignore ?>Prioridad de reparto y picos planificados</li>
				<li><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?>Una persona de contacto: <?php echo esc_html( $c['telefono'] ); ?></li>
			</ul>
		</div>

		<div class="planificador" data-planificador data-condensa>
			<p class="planificador__titulo">Planifica tu suministro</p>
			<div class="planificador__fila" role="radiogroup" aria-label="Frecuencia">
				<label class="chip"><input type="radio" name="dipt-frec" value="semanal" checked><span>Semanal</span></label>
				<label class="chip"><input type="radio" name="dipt-frec" value="quincenal"><span>Quincenal</span></label>
				<label class="chip"><input type="radio" name="dipt-frec" value="mensual"><span>Mensual</span></label>
			</div>
			<label class="planificador__rango">
				<span>Kilos por entrega <b class="num" data-plan-kg>40</b> kg</span>
				<input type="range" min="10" max="250" step="5" value="40" data-plan-rango>
			</label>
			<div class="planificador__fila" role="radiogroup" aria-label="Formato">
				<label class="chip"><input type="radio" name="dipt-plan-formato" value="16mm" checked><span>16 mm</span></label>
				<label class="chip"><input type="radio" name="dipt-plan-formato" value="3mm"><span>3 mm</span></label>
			</div>
			<div class="calendario" data-calendario aria-hidden="true"></div>
			<p class="planificador__total"><b class="num" data-plan-mes>173</b> kg al mes aprox. · <span data-plan-entregas>4–5</span> entregas</p>
			<a class="btn btn--senal btn--bloque" href="<?php echo esc_url( $sum ); ?>" data-plan-enlace data-iman><span>Pedir mi tarifa</span><span class="btn__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></a>
			<p class="nota">Te respondemos en horario laboral con una propuesta para tu caso.</p>
		</div>
	</div>
</section>

<!-- 5 · ASÍ LLEGA TU PEDIDO -->
<section class="seccion seccion--escarcha ruta" aria-labelledby="ruta-titulo">
	<div class="contenedor ruta__in">
		<div class="ruta__texto">
			<header class="cabecera-seccion" data-condensa>
				<p class="antetitulo">De Mataró a tu puerta</p>
				<h2 id="ruta-titulo">Así llega tu pedido</h2>
			</header>
			<ol class="linea-dia" data-condensa="grupo">
				<li><span class="linea-dia__hora">Antes de las 12:00</span><b>Confirmas el pedido</b><span>Online o por teléfono.</span></li>
				<li><span class="linea-dia__hora">El mismo día</span><b>Lo preparamos en Mataró</b><span>En caja EPS de 40 mm, lista para usar.</span></li>
				<li><span class="linea-dia__hora">Sale con MRW</span><b>Va de camino</b><span>Península, días laborables.</span></li>
				<li><span class="linea-dia__hora">Al día siguiente</span><b>Lo recibes por la mañana</b><span>Sin entregas en domingo ni lunes. Sábado según zona.</span></li>
			</ol>
			<p class="nota"><a href="<?php echo esc_url( home_url( '/envios-y-plazos/' ) ); ?>">Envíos y plazos</a> · <a href="<?php echo esc_url( home_url( '/envios-y-plazos/#recogida-almacen' ) ); ?>">Recogida en Mataró</a></p>
		</div>
		<figure class="mapa" data-mapa aria-label="Rutas de entrega desde Mataró a toda la península">
			<svg viewBox="0 0 <?php echo (int) $mapa['ancho']; ?> <?php echo (int) $mapa['alto']; ?>" role="img" aria-hidden="true">
				<path class="mapa__pais mapa__pais--pt" d="<?php echo esc_attr( $mapa['portugal'] ); ?>"/>
				<path class="mapa__pais" d="<?php echo esc_attr( $mapa['espana'] ); ?>"/>
				<?php
				[ $ox, $oy ] = $mapa['ciudades']['mataro'];
				$i = 0;
				foreach ( $mapa['ciudades'] as $nombre => $p ) :
					if ( in_array( $nombre, array( 'mataro', 'barcelona' ), true ) ) continue;
					[ $x, $y ] = $p;
					$cx = ( $ox + $x ) / 2 + ( $oy - $y ) * 0.18;
					$cy = ( $oy + $y ) / 2 - abs( $ox - $x ) * 0.12;
					$d  = sprintf( 'M%.1f %.1fQ%.1f %.1f %.1f %.1f', $ox, $oy, $cx, $cy, $x, $y );
					$dur = 3.6 + ( $i % 4 ) * 0.7;
					?>
					<path id="ruta-<?php echo esc_attr( $nombre ); ?>" class="mapa__ruta" d="<?php echo esc_attr( $d ); ?>"/>
					<circle class="mapa__destino" cx="<?php echo esc_attr( $x ); ?>" cy="<?php echo esc_attr( $y ); ?>" r="7" style="--i:<?php echo (int) $i; ?>"/>
					<circle class="mapa__camion" r="6"><animateMotion dur="<?php echo esc_attr( $dur ); ?>s" begin="<?php echo esc_attr( -$i * 0.9 ); ?>s" repeatCount="indefinite" keyPoints="0;1" keyTimes="0;1" calcMode="spline" keySplines=".77 0 .175 1"><mpath href="#ruta-<?php echo esc_attr( $nombre ); ?>"/></animateMotion></circle>
					<?php
					$i++;
				endforeach;
				?>
				<circle class="mapa__origen-onda" cx="<?php echo esc_attr( $ox ); ?>" cy="<?php echo esc_attr( $oy ); ?>" r="14"/>
				<circle class="mapa__origen" cx="<?php echo esc_attr( $ox ); ?>" cy="<?php echo esc_attr( $oy ); ?>" r="10"/>
			</svg>
			<figcaption class="mapa__rotulo"><b>Mataró</b> · nave y recogida</figcaption>
		</figure>
	</div>
</section>

<!-- 6 · QUÉ ES -->
<section class="seccion quees" aria-labelledby="quees-titulo">
	<div class="contenedor quees__in">
		<div class="sublima" aria-hidden="true">
			<div class="sublima__pellets"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
			<div class="sublima__gas"><i></i><i></i><i></i><i></i></div>
			<p class="sublima__rotulo">Sólido <?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?> gas · sin líquido</p>
		</div>
		<div class="quees__texto" data-condensa>
			<p class="antetitulo">El producto</p>
			<h2 id="quees-titulo">Qué es el hielo seco</h2>
			<p class="quees__entrada">Es CO₂ sólido a −78,5 °C. No se derrite: <strong>sublima</strong>, pasa directamente a gas. Enfría mucho más que el hielo de agua y no deja ni una gota.</p>
			<dl class="fichas">
				<div><dt>Temperatura</dt><dd class="num">−78,5 °C</dd></div>
				<div><dt>Formatos</dt><dd>3 mm · 16 mm</dd></div>
				<div><dt>Residuo</dt><dd>Ninguno</dd></div>
			</dl>
			<?php echo dipt_boton( 'Guía: qué es y para qué sirve', home_url( '/que-es-el-hielo-seco/' ), 'linea' ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- 7 · SEGURIDAD -->
<section class="seccion seccion--noche seguridad-mini" aria-labelledby="seg-titulo">
	<div class="contenedor">
		<header class="cabecera-seccion" data-condensa>
			<p class="antetitulo">Seguridad</p>
			<h2 id="seg-titulo">Tres reglas antes de abrir la caja</h2>
		</header>
		<ol class="reglas" data-condensa="grupo">
			<li><span class="reglas__icono reglas__icono--guante"><?php echo dipt_icono( 'guante' ); // phpcs:ignore ?></span><b>Guantes térmicos y pinzas</b><span>Nunca en contacto con la piel: a −78,5 °C quema.</span></li>
			<li><span class="reglas__icono reglas__icono--aire"><?php echo dipt_icono( 'ventilar' ); // phpcs:ignore ?></span><b>Espacio ventilado</b><span>El CO₂ desplaza el oxígeno y se acumula a ras de suelo.</span></li>
			<li><span class="reglas__icono reglas__icono--abierto"><?php echo dipt_icono( 'abierto' ); // phpcs:ignore ?></span><b>Nunca en un recipiente cerrado</b><span>La presión puede reventarlo. Tampoco en el congelador.</span></li>
		</ol>
		<p class="seguridad-mini__pie"><?php echo dipt_icono( 'no-beber' ); // phpcs:ignore ?><span><b>No se ingiere.</b> En coctelería, siempre con doble recipiente. Fuera del alcance de niños y mascotas.</span> <a href="<?php echo esc_url( home_url( '/seguridad-del-hielo-seco/' ) ); ?>">Guía completa de seguridad</a></p>
	</div>
</section>

<!-- 8 · PREGUNTAS -->
<section class="seccion preguntas" aria-labelledby="faq-titulo">
	<div class="contenedor preguntas__in">
		<header class="cabecera-seccion" data-condensa>
			<p class="antetitulo">Preguntas frecuentes</p>
			<h2 id="faq-titulo">Lo que nos preguntan antes de pedir</h2>
			<p>¿Otra duda? Llámanos al <a href="<?php echo esc_url( $c['telefono_href'] ); ?>"><?php echo esc_html( $c['telefono'] ); ?></a> o escríbenos por <a href="<?php echo esc_url( $c['whatsapp_href'] ); ?>" rel="noopener">WhatsApp</a>.</p>
		</header>
		<?php echo dipt_faq( $faq, 'faq-inicio' ); // phpcs:ignore ?>
	</div>
</section>

<!-- 9 · CIERRE -->
<section class="seccion seccion--noche cierre sobre-oscuro" aria-labelledby="cierre-titulo">
	<?php echo dipt_niebla( 'niebla--cierre', 2 ); // phpcs:ignore ?>
	<div class="contenedor cierre__in" data-condensa>
		<h2 id="cierre-titulo">¿Lo necesitas para mañana?</h2>
		<?php echo dipt_reloj_corte( 'reloj-corte--cierre' ); // phpcs:ignore ?>
		<div class="botonera">
			<?php echo dipt_boton( 'Comprar hielo seco', '#calcula', 'senal' ); // phpcs:ignore ?>
			<?php echo dipt_boton( 'Programar suministro', '#suministro', 'linea-clara' ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php
get_footer();
