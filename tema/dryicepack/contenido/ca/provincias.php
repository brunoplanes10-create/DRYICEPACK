<?php
/*
 * Províncies i ciutats de Catalunya (plantillas/provincia.php) · català.
 * Primer l'empresa (de vostè, com les pàgines B2B); el lliurament, els preus i les preguntes de la compra en línia, de tu.
 * Marcadors: els mateixos que contenido/es/provincias.php (inc/lugares.php, dipt_lugar_texto).
 */
return array(
	'marcadores' => array(
		'y_prov'       => ' i {provincia}',
		'y_toda'       => ' i a tota {provincia}',
		'y_toda_su'    => ' i a tota la província',
		'y_toda_de'    => ' i de tota {provincia}',
		'y_toda_de_su' => ' i de tota la província',
	),
	// S'usa el primer que hi cap: títol ≤ 60 caràcters i descripció de 140 a 155
	'seo' => array(
		'titulos' => array(
			'Gel sec a {nombre}{y_prov}: proveïdor per a empreses | DryIcePack',
			'Gel sec a {nombre}{y_prov}: proveïdor per a empreses',
			'Gel sec a {nombre}: proveïdor per a empreses',
			'Gel sec a {nombre}: per a empreses',
			'Gel sec a {nombre}',
		),
		'descripciones' => array(
			'Gel sec a {nombre}{y_prov} per a empreses: subministrament programat i entrega l\'endemà abans de les 14:00 si demana abans de les 12:00. Demani pressupost.',
			'Gel sec a {nombre}{y_prov}: subministrament programat i, amb comanda abans de les 12:00, entrega l\'endemà abans de les 14:00. Demani pressupost.',
			'Gel sec a {nombre}: subministrament programat per a empreses i entrega l\'endemà abans de les 14:00 si demana abans de les 12:00. Demani preu.',
			'Gel sec a {nombre}: subministrament programat i, amb comanda abans de les 12:00, entrega l\'endemà abans de les 14:00. Demani preu.',
			'Gel sec a {nombre}: subministrament programat per a empreses i entrega abans de les 14:00. Demani pressupost.',
		),
	),
	'migas' => array( 'Inici', 'Zones' ),
	// L1 · Títol: primer el negoci, amb la compra puntual a la vista
	'hero' => array(
		'titulo' => 'Proveïdor de gel sec|<em>a {nombre}{y_toda}.</em>',
		'texto'  => array(
			'provincia' => 'Subministrament programat per a empreses i compra puntual en línia, des de {pk} kg. Amb comanda abans de les 12:00, de dilluns a dijous, arriba a {nombre} l\'endemà abans de les 14:00, en caixa EPS.',
			'ciudad'    => 'A {nombre} ({provincia}): subministrament programat per a empreses i compra puntual en línia, des de {pk} kg. Amb comanda abans de les 12:00, de dilluns a dijous, arriba l\'endemà abans de les 14:00, en caixa EPS.',
		),
		'empresa' => 'Demanar pressupost',
		'compra'  => 'Comprar en línia',
		'datos'   => array(
			'entrega'   => 'Propera entrega a {nombre}',
			'pack'      => '10 kg amb enviament, IVA inclòs',
			'cps'       => 'Codis postals',
			'provincia' => 'Província',
		),
	),
	// L2 · Empreses (vostè)
	'empresa' => array(
		'titulo' => 'Subministrament per a empreses|<em>de {nombre}{y_toda_de}.</em>',
		'texto'  => 'Transport en fred, hostaleria, laboratoris i indústria: digui\'ns quants quilos necessita i cada quant, i li responem amb el seu preu, normalment el mateix dia laborable.',
		'boton'  => 'Demanar pressupost',
		'filas'  => array(
			array( 'Subministrament programat', 'Setmanal, quinzenal o mensual, amb millor preu que la compra puntual. Li arriba el dia acordat abans de les 14:00.' ),
			array( 'Contracte de subministrament', 'Signem un contracte de subministrament de gel sec i, si pot ser, amb domiciliació SEPA.' ),
			array( 'Una factura al mes', 'Totes les comandes del mes en una sola factura, a 30 dies.' ),
			array( 'Caixes de fins a 30 kg', 'Cada caixa porta fins a 30 kg, en pèl·lets de 3 mm o nuggets de 16 mm.' ),
		),
		'medida'    => array( 'Més de 150 kg per comanda', 'Fora de la província de Barcelona no s\'envia pel web: es prepara a mida i amb un altre preu. Escrigui\'ns per WhatsApp, telèfon o correu electrònic.' ),
		'barcelona' => array( 'Més de 250 kg per comanda', 'Li donem preu i dia a mida. Escrigui\'ns per WhatsApp, telèfon o correu electrònic.' ),
	),
	// L3 · Sectors, en l'ordre de prioritat del negoci
	'usos' => array(
		'titulo' => 'Gel sec per sectors|<em>a {nombre}.</em>',
	),
	// Paràgraf d'usos quan el lloc no té introducció pròpia: sense «per a» davant d'article (es diria «per al»)
	'intro_auto' => array(
		'aperturas' => array(
			'Des de Mataró enviem gel sec a {nombre} per a usos molt diferents: {lista}.',
			'A {nombre} pot rebre gel sec per a aquests usos: {lista}.',
			'El gel sec que enviem a {nombre} serveix per a usos molt diferents: {lista}.',
		),
		'sectores' => array(
			'transporte'   => array( 'el transport en fred de congelats', 'la cadena de fred en el repartiment de congelats' ),
			'hosteleria'   => array( 'còctels i plats amb fum en hostaleria', 'els còctels amb fum de l\'hostaleria' ),
			'laboratorios' => array( 'laboratoris que conserven mostres a −78,5 °C', 'l\'enviament de mostres de laboratori a −78,5 °C' ),
			'industria'    => array( 'el refredament de peces i bateries a la indústria', 'el refredament de peces en assajos industrials' ),
			'eventos'      => array( 'festes i esdeveniments amb boira arran de terra', 'la boira arran de terra en esdeveniments' ),
		),
		'cierres' => array(
			'grande'  => 'Per a empreses amb consum fix, subministrament programat setmanal, quinzenal o mensual.',
			'pequena' => 'Per a una comanda puntual, des de {pk} kg i amb l\'enviament calculat abans de pagar.',
		),
	),
	// L4 · Lliurament i preus de la compra en línia (tu)
	'entrega' => array(
		'titulo'  => 'Quan arriba a {nombre}?|<em>I quant costa.</em>',
		'tarjeta' => 'Posat a {nombre} · IVA inclòs',
		'pedido'  => array( 'Comanda abans de les 12:00', 'Repartiment de dimarts a divendres: diumenge i dilluns no n\'hi ha.' ),
		'sabado'  => array( 'Dissabte', 'Abans de les 12:00, segons la zona: ho comprovem abans d\'enviar. Suplement de {sabado_envio} (IVA inclòs). Demana\'l abans de les 12:00 de divendres.' ),
		'mrw'     => array( 'Dies sense repartiment', 'MRW no reparteix {lista}. En pagar només et surten els dies amb repartiment.' ),
		'mrw_dia' => array( 'provincia' => 'el {dia} a tota la província', 'local' => 'el {dia} a {nombre}' ),
		'pack'    => '{precio} ({kilo}/kg) + enviament {envio} = {total}',
		'tarifa'  => array( 'Com es calcula l\'enviament', 'Amb la tarifa de la missatgeria, igual a tota la península: el pes del gel més 1 kg per caixa. El veus al total abans de pagar.' ),
		'recogida' => array( 'Recollida a Mataró', 'A {km} km de {nombre} (uns {min} min en cotxe, sense trànsit), de dilluns a dissabte. Preu rodó amb IVA inclòs: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}. El dissabte, +{sabado_recogida} (IVA inclòs).{sabado_libre} Tu tries el dia i et confirmem l\'hora. En efectiu i amb l\'import exacte.' ),
		'municipios' => array( 'També enviem a', '{lista} i a la resta de la província (codis postals {cp_resto}).' ),
		'resto'      => array( 'També enviem a', 'Tota la província de {provincia} (codis postals {cp_resto}).' ),
		'cps'        => array( 'Codis postals de {nombre}', '{lista}.' ),
	),
	// L5 · Seguretat: els sis avisos comuns (contenido/ca/comun.php) i l'enllaç a la pàgina completa
	'seguridad' => array(
		'titulo' => 'Abans d\'obrir la caixa:|<em>sis regles.</em>',
	),
	// L6 · A prop d'aquí
	'cerca'  => 'A prop de {nombre}',
	'todas'  => 'Totes les zones d\'entrega',
	// L7 · Preguntes reals (FAQPage)
	'faq' => array(
		'donde'     => array( 'On es pot comprar gel sec a {nombre}?', 'En línia, en aquest web: tries pèl·lets de 3 mm o nuggets de 16 mm, des de {pk} kg, i arriba a {nombre} en caixa EPS abans de les 14:00 del dia que triïs (si demanes abans de les 12:00, de dilluns a dijous, l\'endemà). Per a empreses, també amb subministrament programat i factura mensual.' ),
		'donde_cat' => array( 'On es pot comprar gel sec a {nombre}?', 'En línia, en aquest web: tries pèl·lets de 3 mm o nuggets de 16 mm, des de {pk} kg, i arriba a {nombre} en caixa EPS abans de les 14:00 del dia que triïs (si demanes abans de les 12:00, de dilluns a dijous, l\'endemà). O recull-lo a la nostra nau de Mataró, a {km} km, de dilluns a dissabte.' ),
		'empresa'   => array( 'Com el demana una empresa de {nombre}?', 'Amb un compte d\'empresa: contracte de subministrament, comandes programades (setmanals, quinzenals o mensuals) i una sola factura al mes, a 30 dies. Digui\'ns quants quilos i cada quant, i li responem amb el seu preu, normalment el mateix dia laborable.' ),
		'tarda'     => array( 'Quant triga a arribar el gel sec a {nombre}?', 'Si demanes abans de les 12:00 de dilluns a dijous, arriba a {nombre} l\'endemà abans de les 14:00. Si demanes divendres, arriba dimarts, o dissabte abans de les 12:00 si el teu codi postal té repartiment en dissabte (amb suplement). Diumenge i dilluns no hi ha repartiment. En pagar només veus els dies amb repartiment a la teva zona.{mrw}' ),
		'mrw'       => ' Propers dies sense repartiment de MRW a {nombre}: {lista}.',
		'envio'     => array( 'Quant costa l\'enviament a {nombre}?', 'El mateix que a qualsevol punt de la península, segons el pes (IVA inclòs): {lista}. Es calcula amb el pes del gel més 1 kg per caixa i el veus al total abans de pagar.' ),
		'envio_kg'  => '{kg} kg, {envio}',
		'sabado'    => array( 'Feu entregues en dissabte a {nombre}?', 'Segons la zona. MRW no publica quins codis postals tenen repartiment en dissabte: ho comprovem abans d\'enviar. Si n\'hi ha, arriba abans de les 12:00, amb un suplement de {sabado_envio} (IVA inclòs). Per rebre\'l dissabte, demana abans de les 12:00 de divendres.' ),
		'medida'    => array( 'Puc demanar més de 150 kg?', 'Sí, però no pel web. Fora de la província de Barcelona, les comandes de més de 150 kg es preparen a mida i amb un altre preu: escriu-nos per WhatsApp ({wa}), truca\'ns al {tel} o envia\'ns un correu a {email} i et diem preu i dia.' ),
		'medida_bcn' => array( 'Puc demanar més de 150 kg?', 'Sí. A la província de Barcelona pots demanar fins a 250 kg pel web. Per a més de 250 kg, escriu-nos per WhatsApp ({wa}), truca\'ns al {tel} o envia\'ns un correu a {email} i et donem preu i dia.' ),
		'recogida'  => array( 'El puc recollir a Mataró si soc a {nombre}?', 'Sí. La nostra nau és a Mataró, a {km} km de {nombre} (uns {min} min en cotxe sense trànsit). Tries el dia en demanar, de dilluns a dissabte, i et confirmem l\'hora per telèfon o WhatsApp. Preu rodó amb IVA inclòs, el mateix al web i a la nau: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}. El dissabte, +{sabado_recogida} (IVA inclòs).{sabado_libre} Es paga en efectiu i amb l\'import exacte: no tenim canvi. Al cotxe, porta\'l al maleter i ventilant.' ),
	),
	'cierre' => array(
		'titulo'     => 'Gel sec a {nombre},|<em>abans de les 14:00.</em>',
		'secundario' => 'Subministrament per a empreses',
	),
);
