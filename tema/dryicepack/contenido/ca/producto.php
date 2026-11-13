<?php
/* Comprar (fitxa de producte) · català */
return array(
	'menu' => 'Comprar gel sec',
	'seo'  => array(
		'titulo'      => 'Comprar gel sec: {pk}, 10, 15 i 20 kg | DryIcePack',
		'descripcion' => 'Compra gel sec des de {pk} kg, en 3 mm o 16 mm, amb l\'enviament ja inclòs al total. Demana abans de les 12:00 i arriba l\'endemà abans de les 14:00.',
	),
	'migas'   => array( 'Inici', 'Comprar gel sec' ),
	'titulo'  => 'Comprar gel sec sense sorpreses amb l\'enviament',
	'sub'     => 'Demana abans de les 12:00, de dilluns a dijous, i arriba l\'endemà abans de les 14:00. O tria el dia que vulguis. El total ja inclou l\'enviament.',
	'galeria' => array(
		'3mm'  => array( array( 'caja-3mm', 'Caixa de gel sec en pèl·lets de 3 mm oberta, amb vapor' ), array( 'cenital-3mm', 'Pèl·lets de gel sec de 3 mm vistos des de dalt' ), array( 'caja-3mm-lateral', 'Caixa EPS amb pèl·lets de 3 mm i la tapa recolzada' ) ),
		'16mm' => array( array( 'caja-16mm-niebla', 'Caixa de gel sec en nuggets de 16 mm oberta, amb vapor' ), array( 'cenital-16mm', 'Nuggets de gel sec de 16 mm vistos des de dalt' ), array( 'cenital-caja-nuggets', 'Caixa EPS oberta amb nuggets de 16 mm, vista zenital' ) ),
	),
	'formato' => 'Format',
	'formatos' => array(
		'3mm'  => array( 'Pèl·lets de 3 mm', 'Refreda més de pressa. Més boira.' ),
		'16mm' => array( 'Nuggets de 16 mm', 'Dura més. Transport i mostres.' ),
	),
	'kilos'     => 'Quilos per caixa',
	'cajas'     => 'Caixes',
	'menos'     => 'Una caixa menys',
	'mas'       => 'Una caixa més',
	'cp'        => 'El teu codi postal',
	'cp_ph'     => '08301',
	// Línia sota el camp: ajuda i estat (una línia curta)
	'cp_ayuda'     => 'Per veure quin dia t\'arriba',
	'cp_buscando'  => 'Buscant la teva zona…',
	'cp_correcto'  => 'Codi postal correcte',
	'cp_fuera'     => 'Fora de la península',
	'cp_medida'    => 'Enviament a mida',
	'cp_no_valido' => 'Codi postal no vàlid',
	'cp_corto'     => 'Falten xifres: són 5',
	// Línia d'entrega amb el codi postal (%1$s = "08301 · Barcelona", %2$s = data); "medida" acaba amb un enllaç a WhatsApp; "fuera", no (no s'hi envia)
	'llega_cp_ok'     => '%1$s — arriba el %2$s abans de les 14:00',
	'llega_cp_sabado' => 'O el %1$s abans de les 13:00, segons la zona, amb preu de dissabte', // sense %2$s des de l'11/11/2026: el preu de dissabte depèn de la destinació i els quilos
	'llega_cp_fuera'  => 'només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.',
	'llega_cp_medida' => 'més de 150 kg fora de la província de Barcelona: demana-ho per',
	'llega_cp_error'  => 'El codi postal %s no existeix a Espanya. Revisa les 5 xifres.',
	'llega'     => 'Arriba el %s abans de les 14:00',
	'llega_hoy' => 'Si demanes abans de les 12:00, arriba el %s abans de les 14:00',
	'fuera'     => 'Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.',
	'precio'    => 'Gel sec',
	'envio'     => 'Enviament',
	'total'     => 'Total',
	'iva'       => 'IVA inclòs',
	'boton'     => 'Fer la comanda',
	'recogida'  => 'O recull-lo a Mataró, de dilluns a dissabte: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}, IVA inclòs. El dissabte, +{sabado_recogida}.{sabado_libre} En efectiu i amb l\'import exacte.',
	'recoge_ahorro' => 'O recull-lo a Mataró: %1$s en efectiu, t\'estalvies %2$s. De dilluns a dissabte; el dissabte, +{sabado_recogida}. Amb l\'import exacte.',
	'corte'     => array( 'antes' => 'Per rebre\'l el %s, demana abans de les 12:00.', 'quedan' => 'Queden %s.', 'despues' => '' ),
	'puente'    => array(
		'marca'   => 'Festiu',
		'dias'    => array( 'el %s', 'el %1$s i el %2$s', 'del %1$s al %2$s' ),
		'antes'   => '%1$s no hi ha repartiment. Per tenir-lo abans, demana\'l el %2$s abans de les 12:00 i arriba el %3$s. La recollida a Mataró sí que obre.',
		'hoy'     => '%1$s no hi ha repartiment. Per tenir-lo abans, demana\'l avui abans de les 12:00 i arriba el %3$s. La recollida a Mataró sí que obre.',
		'despues' => '%1$s no hi ha repartiment. La propera entrega és el %3$s. La recollida a Mataró sí que obre.',
	),
	'max'       => 'Fins a 250 kg per comanda. En necessites més? Escriu-nos i et donem preu i dia.',
	'medida'    => 'Més de 150 kg fora de la província de Barcelona: aquest enviament es prepara a mida. Escriu-nos per',
	'confianza' => array( 'Targeta, Apple Pay o Google Pay', 'Factura per a empreses', 'Sense crear cap compte' ),
	// Informació de seguretat del producte al costat del botó de compra (Reglament (UE) 2023/988, art. 19)
	'gpsr' => array(
		'titulo' => 'Fabricant i seguretat del producte',
		'filas'  => array(
			array( 'Fabricant', 'INDUNOVA IMS S.L. (marca DryIcePack) · Ronda Alfonso X El Sabio 10, 2n 1a, 08301 Mataró · info@dryicepack.es' ),
			array( 'Producte', 'Diòxid de carboni sòlid (gel sec), UN 1845, en pèl·lets de 3 mm o nuggets de 16 mm, en caixa EPS.' ),
			array( 'Advertiments', 'A −78,5 °C crema la pell: guants tèrmics o pinces. Només en llocs ventilats: el CO₂ desplaça l\'oxigen. Mai en un recipient tancat. No es menja ni es beu. Fora de l\'abast de nens i mascotes. En cotxe, al maleter i ventilant.' ),
		),
		'enlace' => 'Normes de seguretat i fitxa de dades',
	),
	'caja' => array(
		'titulo' => 'Així arriba|<em>a la teva porta.</em>',
		'capas'  => array(
			array( 'Tapa d\'EPS', 'Encaixa sense tancar hermèticament: el gas surt a poc a poc.' ),
			array( 'Gel sec', 'Pèl·lets de 3 mm o nuggets de 16 mm, a −78,5 °C.' ),
			array( 'Caixa d\'EPS de 40 mm', 'Parets gruixudes de poliestirè: aïllen i fan que el gel duri més.' ),
		),
	),
	'comparar' => array(
		'titulo' => '3 mm o 16 mm?|<em>Arrossega</em> i compara.',
		'izq'    => array( '3 mm', 'Més superfície: refreda abans i fa més boira. Per a còctels, efectes i processos.' ),
		'der'    => array( '16 mm', 'Més massa per peça: aguanta més hores a la caixa. Per a transport, mostres i cadena de fred.' ),
		'rango'  => 'Llisca per comparar 3 mm i 16 mm',
	),
	'cuanto' => array(
		'titulo' => 'Quant|<em>en necessito?</em>',
		'texto'  => 'Per fer boira, aquestes quantitats et serviran de referència. És per a transport o mostres? Digues-nos quantes hores i quin volum, i t\'ho calculem.',
		'casos'  => array(
			array( '{pk} kg', 'A casa', 'Un calder, una ponxera amb doble recipient, una festa petita.' ),
			array( '10 kg', 'Festa gran', 'Boira arran de terra en diverses tandes durant la nit.' ),
			array( '15–20 kg', 'Bar o esdeveniment', 'Servei llarg, escenari o diverses barres.' ),
		),
		'calcula' => 'Pregunta\'ns per WhatsApp',
		'seguridad' => 'Abans de fer-lo servir, mira les sis normes de seguretat',
	),
	'tarifa' => array(
		'titulo' => 'Quant costa|<em>l\'enviament?</em>',
		'texto'  => 'La tarifa de la missatgeria per a l\'entrega abans de les 14:00, la mateixa a tota la península (a Mataró, amb poc pes, una mica menys). Es calcula amb el pes del gel més 1 kg per caixa, i la veus abans de pagar. L\'entrega abans de les 12:00 o de les 10:00, i la del dissabte, tenen el seu preu segons la destinació i els quilos: el veus en triar el dia i l\'hora.',
		'filas'  => array( array( 'Fins a 2 kg', 8.53 ), array( 'Fins a 5 kg', 10.32 ), array( 'Fins a 10 kg', 13.19 ), array( 'Cada kg de més', 1.12 ) ),
		// 11/11/2026: sense fila de dissabte. Ja no és un suplement fix: depèn de la destinació i els quilos (es veu en triar el dia)
		'extra'  => array( array( 'Recollida a Mataró, sense enviament', 0 ) ),
		'nota'   => 'Preus amb IVA inclòs. La recollida es paga en efectiu i amb l\'import exacte.',
		'gratis' => '0 €', // fila de la recollida al taló d'enviament: no hi ha cost d'enviament (el gel es paga a preu de recollida)
		'enlace' => 'Terminis d\'entrega, festius i recollida',
		'zonas'  => 'Enviament a tota la península',
	),
	'faq' => array(
		'titulo'   => 'Abans de pagar',
		'empresas' => 'Compte d\'empresa amb factura mensual',
		'lista'    => array(
			array( 'Quan arriba?', 'Si demanes abans de les 12:00 de dilluns a dijous, surt aquell mateix dia i arriba l\'endemà abans de les 14:00. Si demanes divendres abans de les 12:00, arriba dissabte abans de les 13:00 (segons la zona, amb preu de dissabte) o dimarts. No repartim ni diumenge ni dilluns. El dia el tries en pagar.' ),
			array( 'Puc demanar per a un altre dia?', 'Sí: en pagar tries qualsevol dia dels pròxims 90 amb repartiment a la teva zona, de dimarts a dissabte (el dissabte, segons la zona i amb preu de dissabte). Demana\'l per al dia que el faràs servir: el gel sec es va sublimant amb les hores.' ),
			// Mesura pròpia del 10–11/11/2026. [PENDIENTE: actualitzar la frase de 3 kg quan el paquet petit sigui de 5 kg.]
			array( 'Arriba el mateix pes que demano?', 'No del tot: el gel sec se sublima des que es prepara, també amb la caixa tancada. Ho hem mesurat a la nostra nau, amb les caixes tancades: en 29 hores, la caixa petita va perdre 1,8 kg, la gran (de 15 i 20 kg) 4,1 kg i la de 30 kg 5,7 kg. Si en demanes 3 kg, t\'arriben uns 2 kg. Amb calor o obrint la caixa es gasta abans. Demana\'l per al dia que el faràs servir i, si el necessites just, tria una caixa més gran.' ),
			array( 'I si necessito factura?', 'Si ets particular i la comanda és de fins a 400 € (IVA inclòs), t\'arriba la factura simplificada per correu electrònic des d\'info@dryicepack.es, sense demanar-la. Si compres per a una empresa o com a autònom, marca la casella en pagar i posa-hi la raó social i el NIF: reps la factura completa. Si te n\'oblides, t\'enviem un enllaç per demanar-la.' ),
			array( 'El puc tornar?', 'El gel sec se sublima des que surt, així que no té dret de desistiment. Sí que té garantia legal: si arriba malament, te\'l reposem o et tornem els diners, sense cost. Avisa\'ns el mateix dia, amb fotos si pots, i te\'l reposem abans.' ),
			array( 'Com el guardo quan el rebo?', 'Amb guants tèrmics, mai amb la mà. A la seva caixa, amb la tapa posada però sense precintar, en un lloc ventilat i fora de l\'abast de nens i mascotes. Mai al congelador ni en un recipient hermètic: la pressió el pot rebentar. No es menja ni es beu. I si el portes en cotxe, al maleter i ventilant.' ),
		),
	),
	'barra' => 'Total',
	// Redisseny 14/10/2026 (només presentació)
	'galeria_t'     => 'Fotos del format triat',
	'galeria_macro' => array(
		'3mm'  => array( 'pellets-ia', 'Pèl·lets de gel sec de 3 mm, de prop' ),
		'16mm' => array( 'nuggets-ia', 'Nuggets de gel sec de 16 mm, de prop' ),
	),
	'caja_foto'     => array(
		'3mm'  => 'Caixa EPS amb pèl·lets de 3 mm i la tapa recolzada',
		'16mm' => 'Caixa EPS amb nuggets de 16 mm i la tapa recolzada',
	),
);
