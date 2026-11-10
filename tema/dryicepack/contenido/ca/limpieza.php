<?php
/*
 * Gel sec per a neteja criogènica · català · vostè (10/11/2026)
 * Mateixa pàgina que contenido/es/limpieza.php (els pendents són els mateixos). A Catalunya, «limpieza hielo seco» +130 %.
 * No ataca «neteja criogènica» a seques: és el servei d'INDUNOVA (indunova.es).
 *
 * [PENDIENTE: consum típic d'una màquina en kg per hora (dada d'INDUNOVA): la pregunta del consum no dona xifra.]
 * [PENDIENTE: el pèl·let surt acabat de fer el mateix dia? Si es confirma, afegir-ho a «Com arriba» i a la meta.]
 */
return array(
	'menu' => 'Gel sec per a neteja criogènica',
	'seo'  => array(
		'titulo'      => 'Gel sec per a neteja criogènica: 3 mm | DryIcePack',
		'descripcion' => 'Pèl·lets de gel sec de 3 mm per a màquines de neteja criogènica, en caixa d\'EPS de 40 mm. Demani abans de les 12:00 i arriben l\'endemà abans de les 14:00.',
	),
	'migas' => array( 'Inici', 'Usos', 'Neteja criogènica' ),
	'hero'  => array(
		'titulo' => 'Gel sec per a|neteja criogènica:|<em>pèl·lets de 3 mm.</em>',
		'texto'  => 'Per a empreses amb màquina pròpia, contractistes de neteja industrial, tallers i detailing. Demani abans de les 12:00 i arriba l\'endemà abans de les 14:00, en caixa d\'EPS de 40 mm.',
		'boton'  => 'Demanar pèl·lets',
		'enlace' => 'Subministrament programat',
		'alt'    => 'Neteja criogènica d\'un engranatge amb una pistola de raig de gel sec',
		'placa'  => array( array( 'Calibre', '3 mm' ), array( 'Temperatura', '−78,5 °C' ), array( 'En xocar', 'passa a gas' ) ),
	),
	'calibre' => array(
		'titulo' => 'Per què pèl·lets|<em>de 3 mm?</em>',
		'texto'  => array(
			'<strong>És el calibre habitual de les màquines de neteja criogènica.</strong> El pèl·let entra per la tremuja, l\'aire a pressió el llança per la pistola i, en xocar, passa a gas: la brutícia es desprèn i el gel sec no deixa aigua ni residu.',
			'S\'utilitza per netejar motlles, motors, línies de producció alimentària, maquinària d\'impremta, compartiments del motor i baixos de cotxe. Comprovi al manual de la seva màquina quin calibre admet; si té dubtes, digui\'ns el model.',
		),
		'no'     => array( '16 mm', 'Per a la màquina, demani 3 mm. Els nuggets de 16 mm estan pensats per mantenir el fred durant el transport.' ),
		'foto'   => 'Pèl·lets de gel sec de 3 mm a la seva caixa d\'EPS, vistos des de dalt',
		'dibujo' => 'Dibuix de tres pèl·lets de gel sec amb la cota del diàmetre: 3 mm',
		'cota'   => 'Ø 3 mm',
	),
	'llega' => array(
		'titulo' => 'Demani avui,|<em>netegi demà.</em>',
		'texto'  => 'De dilluns a dijous, abans de les 12:00: surt aquell dia i arriba l\'endemà abans de les 14:00. O triï en pagar el dia de la neteja.',
		'hitos'  => array(
			array( '12:00', '', 'Demani abans', 'Triï pèl·lets de 3 mm i els quilos.' ),
			array( '40', 'mm', 'Surt aquell dia', 'En caixa d\'EPS de 40 mm, amb la tapa sense precintar.' ),
			array( '14:00', '', 'Arriba l\'endemà abans', 'A la seva nau, al taller o a l\'obra, a la península.' ),
		),
		'notas'  => array(
			array( 'Dissabte', 'Abans de les 12:00, segons la zona: +{sabado_envio}, IVA inclòs.' ),
			array( 'Un altre dia', 'Qualsevol dels propers 90 amb repartiment a la seva zona. Festius i dissabtes, a <a href="' . esc_url( dipt_url( 'envios' ) ) . '">enviaments i terminis d\'entrega</a>.' ),
			array( 'Recollida', 'A la nau de Mataró, de dilluns a dissabte (el dissabte, +{sabado_recogida}). Només en efectiu i amb l\'import exacte.' ),
		),
	),
	'kilos' => array(
		'titulo'  => 'Quants quilos|<em>per comanda?</em>',
		'texto'   => 'Els packs de la botiga, en una caixa o en diverses: fins a 250 kg per comanda. El mateix preu en 3 i 16 mm, amb la caixa d\'EPS inclosa.',
		'kg'      => 'kg',
		'mas_iva' => '+ IVA',
		'con_iva' => '%s amb IVA',
		'kilo'    => 'el quilo + IVA',
		'pedir'   => 'Demanar en pèl·lets de 3 mm:',
		'envio'   => 'Més l\'enviament, segons el pes: el veu abans de pagar. Si el recull a Mataró, sense enviament.',
		'limites' => array(
			array( '150 kg', 'Més de 150 kg fora de la província de Barcelona: l\'enviament es prepara a mida i surt més car. Escrigui\'ns per <a href="' . esc_url( dipt_whatsapp_url( 'Hola, necessito més de 150 kg de pèl·lets de 3 mm per a neteja criogènica. Codi postal:' ) ) . '">WhatsApp</a>, truqui\'ns al <a href="' . esc_attr( dipt_contacto()['telefono_href'] ) . '">' . esc_html( dipt_contacto()['telefono'] ) . '</a> o escrigui a <a href="mailto:' . esc_attr( dipt_contacto()['email'] ) . '">' . esc_html( dipt_contacto()['email'] ) . '</a>.' ),
			array( '250 kg', 'Màxim per comanda en línia. Més? Li donem preu i dia, normalment el mateix dia feiner.' ),
		),
	),
	'franja' => array( 'Sense aigua', 'Sense productes químics', 'El CO₂ passa a gas' ),
	'suministro' => array(
		'titulo'     => 'Subministrament programat|<em>per a la seva màquina.</em>',
		'texto'      => 'Si neteja cada setmana, cada quinze dies o cada mes, li portem els pèl·lets el dia acordat, sense haver-los de demanar cada vegada.',
		'ritmo_t'    => 'Ritme d\'entrega',
		'ritmos'     => array( 1 => 'Setmanal', 2 => 'Quinzenal', 4 => 'Mensual' ),
		'dias'       => array( 'Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds' ),
		'cal_alt'    => 'Exemple de calendari amb entrega %s, els dimecres.',
		'cal_nota'   => 'Exemple: entrega els dimecres. El dia l\'acordem amb vostè.',
		'condiciones' => array(
			array( 'Contracte de subministrament', 'Quilos, ritme i preu, per escrit.' ),
			array( 'Domiciliació SEPA', 'Si pot ser; si no, transferència.' ),
			array( 'Una factura al mes', 'A 30 dies, amb totes les comandes del mes.' ),
			array( 'Millor preu', 'Descompte sobre tarifa segons el volum i la freqüència.' ),
		),
		'boton'      => 'Obrir compte',
		'enlace'     => 'Com funciona el compte d\'empresa',
	),
	'indunova' => array(
		'titulo' => 'Amb màquina|<em>o sense?</em>',
		'ramas'  => array(
			array( 'Té màquina?', 'Li servim els pèl·lets.', 'De 3 mm, en caixa d\'EPS de 40 mm, per al dia que neteja.' ),
			array( 'No té màquina?', 'INDUNOVA fa el servei.', 'L\'empresa de la qual forma part DryIcePack neteja a les seves instal·lacions, normalment sense desmuntar la maquinària. També lloga i ven màquines.' ),
		),
		'enlace' => 'Servei de neteja criogènica d\'INDUNOVA',
		'url'    => 'https://indunova.es/servicio-de-limpieza-criogenica/',
		'alt'    => 'Operari amb ulleres i guants tèrmics carregant pèl·lets de gel sec amb una pala',
	),
	'seguridad' => array(
		'titulo' => 'Seguretat en la|<em>neteja criogènica.</em>',
		'texto'  => 'En netejar, tot el gel sec passa a gas. El CO₂ pesa més que l\'aire: en un lloc tancat s\'acumula a baix i desplaça l\'oxigen.',
		'reglas' => array(
			array( 'ventilar', 'Ventili l\'espai', 'Portes obertes o extracció. Mai en fosses, soterranis ni cambres tancades sense ventilar.' ),
			array( 'guante', 'Guants tèrmics en carregar la tremuja', 'A −78,5 °C crema la pell: amb pala o pinces, mai amb la mà.' ),
			array( 'gafas', 'Ulleres i protecció auditiva', 'I la resta de l\'equip que indiqui el fabricant de la seva màquina.' ),
			array( 'hermetico', 'Res hermètic', 'Els pèl·lets, a la seva caixa amb la tapa sense precintar: en un recipient tancat, la pressió el pot rebentar.' ),
			array( 'furgoneta', 'A la furgoneta, a la zona de càrrega', 'Separada de la cabina i ventilant. En cotxe, al maleter.' ),
			array( 'nino', 'Lluny de nens i mascotes', 'I no es menja ni es beu.' ),
		),
		'corte'  => array( 'co2' => 'CO₂', 'puerta' => 'Porta oberta', 'nave' => 'Nau, en secció' ),
		'enlace' => 'Normes de seguretat i fitxa de dades del gel sec',
	),
	'faq' => array(
		'titulo' => 'Preguntes de|<em>neteja criogènica</em>',
		'lista'  => array(
			array( 'Els pèl·lets de 3 mm serveixen per a la meva màquina?', 'És el calibre habitual de les màquines de neteja criogènica. Tot i així, comprovi al manual de la seva quin calibre admet. Si té dubtes, digui\'ns el model per WhatsApp o per telèfon.' ),
			array( 'Quant gel sec gasta una màquina per hora?', 'Depèn de la màquina, de la pressió de treball i del que netegi: el consum l\'indica el fabricant. Digui\'ns la seva màquina i quantes hores netejarà, i l\'ajudem a calcular la comanda.' ),
			array( 'El puc demanar per al dia de la neteja?', 'Sí. En pagar tria qualsevol dia dels propers 90 amb repartiment a la seva zona, de dimarts a dissabte (el dissabte, segons la zona i amb un suplement de {sabado_envio}). Demani\'l per a aquell dia: el gel sec se sublima amb les hores.' ),
			array( 'Quant aguanten els pèl·lets a la caixa?', 'Depèn de la temperatura i de les vegades que s\'obri. Els pèl·lets de 3 mm se sublimen abans que els nuggets: guardi\'ls a la seva caixa, amb la tapa sense precintar, i no l\'obri fins que carregui la tremuja.' ),
			array( 'Poden servir més de 150 kg?', 'Al web, fins a 250 kg per comanda. Fora de la província de Barcelona, a partir de 150 kg l\'enviament es prepara a mida: escrigui\'ns per WhatsApp, truqui\'ns o enviï\'ns un correu. Més de 250 kg? Li donem preu i dia, normalment el mateix dia feiner.' ),
			array( 'Em fan factura completa?', 'Sí. En pagar, marqui «Compro per a una empresa o autònom» i escrigui la raó social i el NIF. Amb <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">compte d\'empresa</a>, una sola factura al mes, a 30 dies.' ),
			array( 'Vostès fan la neteja?', 'DryIcePack ven el gel sec. La neteja la fa INDUNOVA, l\'empresa de la qual forma part DryIcePack, a les seves instal·lacions: <a href="https://indunova.es/servicio-de-limpieza-criogenica/">servei de neteja criogènica d\'INDUNOVA</a>.' ),
			array( 'I per refredar peces o muntar per contracció?', 'És un altre ús, amb els mateixos pèl·lets de 3 mm: ho expliquem a <a href="' . esc_url( dipt_url( 'industria' ) ) . '">gel sec industrial per refredar peces i bateries</a>.' ),
		),
	),
	'final' => array(
		'titulo' => 'Neteja demà?|<em>Demani abans de les 12:00.</em>',
		'corte'  => array( 'Demani abans de les 12:00 i arriba el %s abans de les 14:00', 'Falten %s per demanar avui', 'Si demana ara, arriba el %s abans de les 14:00' ),
	),
);
