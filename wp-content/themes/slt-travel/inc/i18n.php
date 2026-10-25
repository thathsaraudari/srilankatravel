<?php
if (!defined('ABSPATH')) exit;

function slt_supported_languages(): array {
    return [
        'fr'=>['label'=>'Français','short'=>'FR'],
        'en'=>['label'=>'English','short'=>'EN'],
        'de'=>['label'=>'Deutsch','short'=>'DE'],
        'nl'=>['label'=>'Nederlands','short'=>'NL'],
        'es'=>['label'=>'Español','short'=>'ES'],
    ];
}

function slt_current_language(): string {
    static $resolved=null;
    if ($resolved!==null) return $resolved;
    $supported=array_keys(slt_supported_languages());

    $requested=isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : '';
    if (in_array($requested,$supported,true)) return $resolved=$requested;

    $saved=isset($_COOKIE['slt_lang']) ? sanitize_key(wp_unslash($_COOKIE['slt_lang'])) : '';
    if (in_array($saved,$supported,true)) return $resolved=$saved;

    $browser=strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE']??''));
    if ($browser!=='') {
        foreach (preg_split('/[,;]/',$browser)?:[] as $part) {
            $candidate=substr(trim($part),0,2);
            if (in_array($candidate,$supported,true)) return $resolved=$candidate;
        }
    }
    return $resolved='fr';
}

add_action('init',function(): void {
    if (!isset($_GET['lang'])) return;
    $lang=sanitize_key(wp_unslash($_GET['lang']));
    if (!array_key_exists($lang,slt_supported_languages())) return;
    setcookie('slt_lang',$lang,[
        'expires'=>time()+YEAR_IN_SECONDS,
        'path'=>COOKIEPATH ?: '/',
        'domain'=>COOKIE_DOMAIN ?: '',
        'secure'=>is_ssl(),
        'httponly'=>false,
        'samesite'=>'Lax',
    ]);
    $_COOKIE['slt_lang']=$lang;
},1);

function slt_language_switch_url(string $lang): string {
    $url=remove_query_arg('lang');
    return add_query_arg('lang',$lang,$url);
}

function slt_ui_dictionary(): array {
    return [
        'en'=>[
            'menu'=>'Menu','nav.home'=>'Home','nav.tours'=>'Tours','nav.services'=>'Services','nav.about'=>'About','nav.faq'=>'FAQ','nav.contact'=>'Contact','nav.cta'=>'Plan my trip',
            'footer.description'=>'Private, tailor-made trips in Sri Lanka, carefully prepared from Europe and coordinated with local partners.','footer.discover'=>'Discover','footer.tours'=>'Our tours','footer.how'=>'How it works','footer.info'=>'Information','footer.payment'=>'Secure payment','footer.terms'=>'Terms & conditions','footer.privacy'=>'Privacy','footer.trip'=>'Your trip','footer.idea'=>'Already have dates or an itinerary in mind?','footer.talk'=>'Discuss my trip','footer.demo'=>'Demo website',
            'home.hero.eyebrow'=>'Private trips in Sri Lanka','home.hero.title'=>'Discover Sri Lanka, your way.','home.hero.text'=>'Culture, safaris, tea-covered mountains and tropical beaches — brought together in private itineraries you can personalise.','home.hero.tours'=>'Explore our tours','home.hero.custom'=>'Plan my trip',
            'home.trust.private'=>'Private journeys','home.trust.private_sub'=>'At your own pace','home.trust.europe'=>'European experience','home.trust.europe_sub'=>'Traveller service from France','home.trust.local'=>'Local partners','home.trust.local_sub'=>'Coordination in Sri Lanka','home.trust.tailor'=>'Tailor-made','home.trust.tailor_sub'=>'Flexible itineraries',
            'home.routes.eyebrow'=>'Choose an itinerary','home.routes.title'=>'Journeys made to be fully experienced','home.routes.text'=>'Start with one of our tours and adapt the stops, hotels and experiences to your wishes.',
            'home.services.eyebrow'=>'Our services','home.services.title'=>'A trip organised from start to finish','home.services.text'=>'We combine preparation in Europe with local coordination to make your stay simple and smooth.','home.services.private'=>'Private tours','home.services.private_text'=>'Complete itineraries combining culture, nature, safari, mountains and beach.','home.services.custom'=>'Tailor-made trips','home.services.custom_text'=>'Dates, pace, hotels and experiences adapted to your plans.','home.services.transport'=>'Transport & driver','home.services.transport_text'=>'Airport transfers and private travel organised with our local partners.','home.services.hotels'=>'Hotels & experiences','home.services.hotels_text'=>'Accommodation and activities selected to fit your itinerary.',
            'home.why.eyebrow'=>'Why choose us','home.why.title'=>'A traveller experience built between France and Sri Lanka.','home.why.text'=>'Our experience serving international travellers in France taught us the importance of punctuality, clear communication and reliable service. In Sri Lanka, we apply the same standards with local partners.','home.why.link'=>'Discover our story →','home.why.1'=>'A clear contact before departure','home.why.2'=>'Private and flexible itineraries','home.why.3'=>'Coordination with local partners','home.why.4'=>'Prices and services confirmed before payment',
            'home.how.eyebrow'=>'How it works','home.how.title'=>'From your idea to departure.','home.how.text'=>'Choose a tour or tell us about your ideal journey. We then refine the itinerary and services before confirmation.','home.how.button'=>'See the steps','home.how.1'=>'Choose a tour or share your wishes','home.how.2'=>'We adapt the itinerary and check availability','home.how.3'=>'You receive the confirmed price and services','home.how.4'=>'You book and prepare for departure',
            'archive.eyebrow'=>'Sri Lanka tours','archive.title'=>'Find your journey','archive.text'=>'Private itineraries designed as a starting point and personalised around your dates, pace and interests.','archive.empty'=>'No tours have been published yet.',
            'tour.private'=>'Private Sri Lanka tour','tour.days'=>'days','tour.nights'=>'nights','tour.overview'=>'Overview','tour.highlights'=>'Highlights','tour.special'=>'What makes this journey special','tour.daybyday'=>'Day by day','tour.itinerary'=>'Your itinerary','tour.day'=>'Day','tour.hotel'=>'Hotel','tour.meals'=>'Meals','tour.breakfast'=>'Breakfast','tour.lunch'=>'Lunch','tour.dinner'=>'Dinner','tour.included'=>'Included','tour.whats_included'=>"What's included",'tour.know'=>'Good to know','tour.not_included'=>'Not included',
            'booking.title'=>'Book this tour','booking.success'=>'Your demo booking has been recorded.','booking.demo'=>'Demo mode — no real payment will be charged.','booking.name'=>'Name','booking.email'=>'Email','booking.date'=>'Departure date','booking.adults'=>'Adults','booking.children'=>'Children','booking.single'=>'Single rooms','booking.single_help'=>'Supplement applied per single room.','booking.phone'=>'Phone','booking.total'=>'Total price','booking.calc'=>'Enter your travellers to calculate the price.','booking.deposit'=>'Deposit at booking','booking.season'=>'High-season supplement included.','booking.payment'=>'Preferred payment method','booking.wero_help'=>'European bank payment, if available','booking.card'=>'Bank card','booking.card_help'=>'CB / Visa / Mastercard','booking.apple_help'=>'If available on your device','booking.ideal_help'=>'For Dutch customers','booking.sepa'=>'SEPA transfer','booking.privacy'=>'I agree that my information may be used to process this booking request.','booking.submit'=>'Create my booking (demo)','booking.secure'=>'Mollie payment will be connected separately after this booking flow is validated.',
            'price.request'=>'Price on request','price.from'=>'From','price.person'=>' / person','price.couple'=>' / couple','price.trip'=>' / trip','card.view'=>'View tour →',
            'js.confirm'=>'Price to be confirmed','js.final'=>'The final price will be confirmed before booking.','js.no_rate'=>'No rate is configured for this group size.','js.adult'=>'adult','js.child'=>'child','js.single'=>'single room','js.season'=>'High-season supplement of %s%% included.',
        ],
        'de'=>[
            'menu'=>'Menü','nav.home'=>'Startseite','nav.tours'=>'Rundreisen','nav.services'=>'Leistungen','nav.about'=>'Über uns','nav.faq'=>'FAQ','nav.contact'=>'Kontakt','nav.cta'=>'Reise planen',
            'footer.description'=>'Private und maßgeschneiderte Sri-Lanka-Reisen, sorgfältig in Europa vorbereitet und mit lokalen Partnern koordiniert.','footer.discover'=>'Entdecken','footer.tours'=>'Unsere Reisen','footer.how'=>'So funktioniert es','footer.info'=>'Informationen','footer.payment'=>'Sichere Zahlung','footer.terms'=>'AGB','footer.privacy'=>'Datenschutz','footer.trip'=>'Ihre Reise','footer.idea'=>'Sie haben bereits Reisedaten oder eine Route im Kopf?','footer.talk'=>'Reise besprechen','footer.demo'=>'Demo-Website',
            'home.hero.eyebrow'=>'Private Reisen in Sri Lanka','home.hero.title'=>'Entdecken Sie Sri Lanka auf Ihre Art.','home.hero.text'=>'Kultur, Safaris, Teeberge und tropische Strände — kombiniert in privaten, individuell anpassbaren Reiserouten.','home.hero.tours'=>'Rundreisen entdecken','home.hero.custom'=>'Reise planen',
            'home.trust.private'=>'Private Reisen','home.trust.private_sub'=>'In Ihrem Tempo','home.trust.europe'=>'Erfahrung in Europa','home.trust.europe_sub'=>'Reiseservice aus Frankreich','home.trust.local'=>'Lokale Partner','home.trust.local_sub'=>'Organisation in Sri Lanka','home.trust.tailor'=>'Maßgeschneidert','home.trust.tailor_sub'=>'Flexible Routen',
            'home.routes.eyebrow'=>'Route auswählen','home.routes.title'=>'Reisen, die man wirklich erlebt','home.routes.text'=>'Starten Sie mit einer unserer Rundreisen und passen Sie Etappen, Hotels und Erlebnisse an.',
            'home.services.eyebrow'=>'Unsere Leistungen','home.services.title'=>'Ihre Reise von Anfang bis Ende organisiert','home.services.text'=>'Wir verbinden Vorbereitung in Europa mit lokaler Koordination für eine einfache und reibungslose Reise.','home.services.private'=>'Private Rundreisen','home.services.private_text'=>'Komplette Routen mit Kultur, Natur, Safari, Bergen und Strand.','home.services.custom'=>'Maßgeschneiderte Reisen','home.services.custom_text'=>'Reisedaten, Tempo, Hotels und Erlebnisse passend zu Ihren Wünschen.','home.services.transport'=>'Transport & Fahrer','home.services.transport_text'=>'Flughafentransfers und private Fahrten mit unseren lokalen Partnern.','home.services.hotels'=>'Hotels & Erlebnisse','home.services.hotels_text'=>'Unterkünfte und Aktivitäten passend zu Ihrer Route.',
            'home.why.eyebrow'=>'Warum wir','home.why.title'=>'Reiseerfahrung zwischen Frankreich und Sri Lanka.','home.why.text'=>'Unsere Erfahrung mit internationalen Reisenden in Frankreich hat uns gezeigt, wie wichtig Pünktlichkeit, klare Kommunikation und zuverlässiger Service sind. In Sri Lanka arbeiten wir nach denselben Standards mit lokalen Partnern.','home.why.link'=>'Unsere Geschichte →','home.why.1'=>'Klare Ansprechperson vor der Abreise','home.why.2'=>'Private und flexible Routen','home.why.3'=>'Koordination mit lokalen Partnern','home.why.4'=>'Preis und Leistungen vor Zahlung bestätigt',
            'home.how.eyebrow'=>'So funktioniert es','home.how.title'=>'Von der Idee bis zur Abreise.','home.how.text'=>'Wählen Sie eine Reise oder beschreiben Sie Ihre Wunschreise. Danach stimmen wir Route und Leistungen vor der Bestätigung ab.','home.how.button'=>'Schritte ansehen','home.how.1'=>'Reise wählen oder Wünsche mitteilen','home.how.2'=>'Wir passen die Route an und prüfen Verfügbarkeit','home.how.3'=>'Sie erhalten bestätigte Preise und Leistungen','home.how.4'=>'Sie buchen und bereiten die Reise vor',
            'archive.eyebrow'=>'Sri-Lanka-Rundreisen','archive.title'=>'Finden Sie Ihre Reise','archive.text'=>'Private Reiserouten als Ausgangspunkt, angepasst an Ihre Termine, Ihr Tempo und Ihre Wünsche.','archive.empty'=>'Noch keine Reisen veröffentlicht.',
            'tour.private'=>'Private Sri-Lanka-Rundreise','tour.days'=>'Tage','tour.nights'=>'Nächte','tour.overview'=>'Überblick','tour.highlights'=>'Höhepunkte','tour.special'=>'Was diese Reise besonders macht','tour.daybyday'=>'Tag für Tag','tour.itinerary'=>'Ihre Reiseroute','tour.day'=>'Tag','tour.hotel'=>'Hotel','tour.meals'=>'Mahlzeiten','tour.breakfast'=>'Frühstück','tour.lunch'=>'Mittagessen','tour.dinner'=>'Abendessen','tour.included'=>'Inklusive','tour.whats_included'=>'Enthaltene Leistungen','tour.know'=>'Gut zu wissen','tour.not_included'=>'Nicht enthalten',
            'booking.title'=>'Diese Reise buchen','booking.success'=>'Ihre Demo-Buchung wurde gespeichert.','booking.demo'=>'Demo-Modus — es wird keine echte Zahlung belastet.','booking.name'=>'Name','booking.email'=>'E-Mail','booking.date'=>'Abreisedatum','booking.adults'=>'Erwachsene','booking.children'=>'Kinder','booking.single'=>'Einzelzimmer','booking.single_help'=>'Aufpreis pro Einzelzimmer.','booking.phone'=>'Telefon','booking.total'=>'Gesamtpreis','booking.calc'=>'Reisende eingeben, um den Preis zu berechnen.','booking.deposit'=>'Anzahlung bei Buchung','booking.season'=>'Hochsaisonzuschlag enthalten.','booking.payment'=>'Gewünschte Zahlungsart','booking.wero_help'=>'Europäische Bankzahlung, falls verfügbar','booking.card'=>'Bankkarte','booking.card_help'=>'CB / Visa / Mastercard','booking.apple_help'=>'Falls auf Ihrem Gerät verfügbar','booking.ideal_help'=>'Für niederländische Kunden','booking.sepa'=>'SEPA-Überweisung','booking.privacy'=>'Ich stimme zu, dass meine Daten zur Bearbeitung dieser Buchungsanfrage verwendet werden.','booking.submit'=>'Buchung erstellen (Demo)','booking.secure'=>'Die Mollie-Zahlung wird nach Validierung dieses Buchungsablaufs separat verbunden.',
            'price.request'=>'Preis auf Anfrage','price.from'=>'Ab','price.person'=>' / Person','price.couple'=>' / Paar','price.trip'=>' / Reise','card.view'=>'Reise ansehen →',
            'js.confirm'=>'Preis wird bestätigt','js.final'=>'Der Endpreis wird vor der Buchung bestätigt.','js.no_rate'=>'Für diese Gruppengröße ist kein Preis hinterlegt.','js.adult'=>'Erwachsener','js.child'=>'Kind','js.single'=>'Einzelzimmer','js.season'=>'Hochsaisonzuschlag von %s%% enthalten.',
        ],
        'nl'=>[
            'menu'=>'Menu','nav.home'=>'Home','nav.tours'=>'Rondreizen','nav.services'=>'Diensten','nav.about'=>'Over ons','nav.faq'=>'FAQ','nav.contact'=>'Contact','nav.cta'=>'Plan mijn reis',
            'footer.description'=>'Privé- en maatwerkreizen in Sri Lanka, zorgvuldig voorbereid vanuit Europa en gecoördineerd met lokale partners.','footer.discover'=>'Ontdek','footer.tours'=>'Onze rondreizen','footer.how'=>'Hoe het werkt','footer.info'=>'Informatie','footer.payment'=>'Veilig betalen','footer.terms'=>'Voorwaarden','footer.privacy'=>'Privacy','footer.trip'=>'Uw reis','footer.idea'=>'Heeft u al data of een route in gedachten?','footer.talk'=>'Bespreek mijn reis','footer.demo'=>'Demo-website',
            'home.hero.eyebrow'=>'Privéreizen in Sri Lanka','home.hero.title'=>'Ontdek Sri Lanka op uw manier.','home.hero.text'=>'Cultuur, safari’s, theebergen en tropische stranden — samengebracht in privéroutes die u kunt aanpassen.','home.hero.tours'=>'Bekijk de rondreizen','home.hero.custom'=>'Plan mijn reis',
            'home.trust.private'=>'Privéreizen','home.trust.private_sub'=>'In uw eigen tempo','home.trust.europe'=>'Ervaring in Europa','home.trust.europe_sub'=>'Reisservice vanuit Frankrijk','home.trust.local'=>'Lokale partners','home.trust.local_sub'=>'Organisatie in Sri Lanka','home.trust.tailor'=>'Op maat','home.trust.tailor_sub'=>'Flexibele routes',
            'home.routes.eyebrow'=>'Kies een route','home.routes.title'=>'Reizen om volop te beleven','home.routes.text'=>'Begin met een van onze rondreizen en pas stops, hotels en ervaringen aan uw wensen aan.',
            'home.services.eyebrow'=>'Onze diensten','home.services.title'=>'Een reis van begin tot eind georganiseerd','home.services.text'=>'We combineren voorbereiding in Europa met lokale coördinatie voor een eenvoudige en soepele reis.','home.services.private'=>'Privérondreizen','home.services.private_text'=>'Complete routes met cultuur, natuur, safari, bergen en strand.','home.services.custom'=>'Maatwerkreizen','home.services.custom_text'=>'Data, tempo, hotels en ervaringen afgestemd op uw plannen.','home.services.transport'=>'Vervoer & chauffeur','home.services.transport_text'=>'Luchthaventransfers en privévervoer met onze lokale partners.','home.services.hotels'=>'Hotels & ervaringen','home.services.hotels_text'=>'Accommodaties en activiteiten passend bij uw route.',
            'home.why.eyebrow'=>'Waarom voor ons kiezen','home.why.title'=>'Reiservaring opgebouwd tussen Frankrijk en Sri Lanka.','home.why.text'=>'Onze ervaring met internationale reizigers in Frankrijk leerde ons het belang van stiptheid, duidelijke communicatie en betrouwbare service. In Sri Lanka werken we met lokale partners volgens dezelfde standaard.','home.why.link'=>'Ontdek ons verhaal →','home.why.1'=>'Duidelijk aanspreekpunt voor vertrek','home.why.2'=>'Privé en flexibele routes','home.why.3'=>'Coördinatie met lokale partners','home.why.4'=>'Prijs en diensten bevestigd voor betaling',
            'home.how.eyebrow'=>'Hoe het werkt','home.how.title'=>'Van uw idee tot vertrek.','home.how.text'=>'Kies een rondreis of vertel ons over uw ideale reis. Daarna verfijnen we route en diensten voor bevestiging.','home.how.button'=>'Bekijk de stappen','home.how.1'=>'Kies een reis of deel uw wensen','home.how.2'=>'Wij passen de route aan en controleren beschikbaarheid','home.how.3'=>'U ontvangt de bevestigde prijs en diensten','home.how.4'=>'U boekt en bereidt uw vertrek voor',
            'archive.eyebrow'=>'Sri Lanka-rondreizen','archive.title'=>'Vind uw reis','archive.text'=>'Privéroutes als vertrekpunt, aanpasbaar aan uw data, tempo en interesses.','archive.empty'=>'Er zijn nog geen rondreizen gepubliceerd.',
            'tour.private'=>'Privérondreis Sri Lanka','tour.days'=>'dagen','tour.nights'=>'nachten','tour.overview'=>'Overzicht','tour.highlights'=>'Hoogtepunten','tour.special'=>'Wat deze reis bijzonder maakt','tour.daybyday'=>'Dag voor dag','tour.itinerary'=>'Uw route','tour.day'=>'Dag','tour.hotel'=>'Hotel','tour.meals'=>'Maaltijden','tour.breakfast'=>'Ontbijt','tour.lunch'=>'Lunch','tour.dinner'=>'Diner','tour.included'=>'Inbegrepen','tour.whats_included'=>'Wat is inbegrepen','tour.know'=>'Goed om te weten','tour.not_included'=>'Niet inbegrepen',
            'booking.title'=>'Boek deze reis','booking.success'=>'Uw demo-boeking is opgeslagen.','booking.demo'=>'Demomodus — er wordt geen echte betaling afgeschreven.','booking.name'=>'Naam','booking.email'=>'E-mail','booking.date'=>'Vertrekdatum','booking.adults'=>'Volwassenen','booking.children'=>'Kinderen','booking.single'=>'Eenpersoonskamers','booking.single_help'=>'Toeslag per eenpersoonskamer.','booking.phone'=>'Telefoon','booking.total'=>'Totale prijs','booking.calc'=>'Vul de reizigers in om de prijs te berekenen.','booking.deposit'=>'Aanbetaling bij boeking','booking.season'=>'Hoogseizoentoeslag inbegrepen.','booking.payment'=>'Gewenste betaalmethode','booking.wero_help'=>'Europese bankbetaling, indien beschikbaar','booking.card'=>'Betaalkaart','booking.card_help'=>'CB / Visa / Mastercard','booking.apple_help'=>'Indien beschikbaar op uw apparaat','booking.ideal_help'=>'Voor Nederlandse klanten','booking.sepa'=>'SEPA-overschrijving','booking.privacy'=>'Ik ga ermee akkoord dat mijn gegevens worden gebruikt om deze boekingsaanvraag te verwerken.','booking.submit'=>'Mijn boeking maken (demo)','booking.secure'=>'Mollie-betaling wordt apart aangesloten nadat deze boekingsflow is gevalideerd.',
            'price.request'=>'Prijs op aanvraag','price.from'=>'Vanaf','price.person'=>' / persoon','price.couple'=>' / koppel','price.trip'=>' / reis','card.view'=>'Bekijk reis →',
            'js.confirm'=>'Prijs te bevestigen','js.final'=>'De definitieve prijs wordt vóór boeking bevestigd.','js.no_rate'=>'Voor deze groepsgrootte is geen tarief ingesteld.','js.adult'=>'volwassene','js.child'=>'kind','js.single'=>'eenpersoonskamer','js.season'=>'Hoogseizoentoeslag van %s%% inbegrepen.',
        ],
        'es'=>[
            'menu'=>'Menú','nav.home'=>'Inicio','nav.tours'=>'Circuitos','nav.services'=>'Servicios','nav.about'=>'Quiénes somos','nav.faq'=>'FAQ','nav.contact'=>'Contacto','nav.cta'=>'Planear mi viaje',
            'footer.description'=>'Viajes privados y a medida en Sri Lanka, preparados cuidadosamente desde Europa y coordinados con socios locales.','footer.discover'=>'Descubrir','footer.tours'=>'Nuestros circuitos','footer.how'=>'Cómo funciona','footer.info'=>'Información','footer.payment'=>'Pago seguro','footer.terms'=>'Condiciones','footer.privacy'=>'Privacidad','footer.trip'=>'Tu viaje','footer.idea'=>'¿Ya tienes fechas o un itinerario en mente?','footer.talk'=>'Hablar de mi viaje','footer.demo'=>'Sitio de demostración',
            'home.hero.eyebrow'=>'Viajes privados en Sri Lanka','home.hero.title'=>'Descubre Sri Lanka a tu manera.','home.hero.text'=>'Cultura, safaris, montañas de té y playas tropicales — reunidos en itinerarios privados que puedes personalizar.','home.hero.tours'=>'Descubrir los circuitos','home.hero.custom'=>'Planear mi viaje',
            'home.trust.private'=>'Viajes privados','home.trust.private_sub'=>'A tu ritmo','home.trust.europe'=>'Experiencia en Europa','home.trust.europe_sub'=>'Servicio al viajero desde Francia','home.trust.local'=>'Socios locales','home.trust.local_sub'=>'Organización en Sri Lanka','home.trust.tailor'=>'A medida','home.trust.tailor_sub'=>'Itinerarios flexibles',
            'home.routes.eyebrow'=>'Elige un itinerario','home.routes.title'=>'Viajes para vivir plenamente','home.routes.text'=>'Parte de uno de nuestros circuitos y adapta etapas, hoteles y experiencias a tus deseos.',
            'home.services.eyebrow'=>'Nuestros servicios','home.services.title'=>'Un viaje organizado de principio a fin','home.services.text'=>'Combinamos preparación en Europa y coordinación local para que tu estancia sea sencilla y fluida.','home.services.private'=>'Circuitos privados','home.services.private_text'=>'Itinerarios completos con cultura, naturaleza, safari, montañas y playa.','home.services.custom'=>'Viajes a medida','home.services.custom_text'=>'Fechas, ritmo, hoteles y experiencias adaptados a tu proyecto.','home.services.transport'=>'Transporte y chófer','home.services.transport_text'=>'Traslados al aeropuerto y desplazamientos privados con nuestros socios locales.','home.services.hotels'=>'Hoteles y experiencias','home.services.hotels_text'=>'Alojamientos y actividades seleccionados para tu itinerario.',
            'home.why.eyebrow'=>'Por qué elegirnos','home.why.title'=>'Una experiencia de viaje construida entre Francia y Sri Lanka.','home.why.text'=>'Nuestra experiencia con viajeros internacionales en Francia nos enseñó la importancia de la puntualidad, la comunicación clara y un servicio fiable. En Sri Lanka aplicamos el mismo nivel con socios locales.','home.why.link'=>'Conoce nuestra historia →','home.why.1'=>'Un contacto claro antes de salir','home.why.2'=>'Itinerarios privados y flexibles','home.why.3'=>'Coordinación con socios locales','home.why.4'=>'Precio y servicios confirmados antes del pago',
            'home.how.eyebrow'=>'Cómo funciona','home.how.title'=>'De tu idea a la salida.','home.how.text'=>'Elige un circuito o cuéntanos tu viaje ideal. Después ajustamos el itinerario y los servicios antes de confirmar.','home.how.button'=>'Ver los pasos','home.how.1'=>'Elige un circuito o comparte tus deseos','home.how.2'=>'Adaptamos el itinerario y comprobamos disponibilidad','home.how.3'=>'Recibes el precio y los servicios confirmados','home.how.4'=>'Reservas y preparas tu salida',
            'archive.eyebrow'=>'Circuitos por Sri Lanka','archive.title'=>'Encuentra tu viaje','archive.text'=>'Itinerarios privados como punto de partida, personalizables según fechas, ritmo y preferencias.','archive.empty'=>'Todavía no hay circuitos publicados.',
            'tour.private'=>'Circuito privado por Sri Lanka','tour.days'=>'días','tour.nights'=>'noches','tour.overview'=>'Resumen','tour.highlights'=>'Lo mejor','tour.special'=>'Lo que hace especial este viaje','tour.daybyday'=>'Día a día','tour.itinerary'=>'Tu itinerario','tour.day'=>'Día','tour.hotel'=>'Hotel','tour.meals'=>'Comidas','tour.breakfast'=>'Desayuno','tour.lunch'=>'Almuerzo','tour.dinner'=>'Cena','tour.included'=>'Incluido','tour.whats_included'=>'Qué está incluido','tour.know'=>'A tener en cuenta','tour.not_included'=>'No incluido',
            'booking.title'=>'Reservar este circuito','booking.success'=>'Tu reserva de demostración se ha guardado.','booking.demo'=>'Modo demostración — no se realizará ningún cargo real.','booking.name'=>'Nombre','booking.email'=>'Email','booking.date'=>'Fecha de salida','booking.adults'=>'Adultos','booking.children'=>'Niños','booking.single'=>'Habitaciones individuales','booking.single_help'=>'Suplemento por habitación individual.','booking.phone'=>'Teléfono','booking.total'=>'Precio total','booking.calc'=>'Indica los viajeros para calcular el precio.','booking.deposit'=>'Depósito al reservar','booking.season'=>'Suplemento de temporada alta incluido.','booking.payment'=>'Método de pago preferido','booking.wero_help'=>'Pago bancario europeo, si está disponible','booking.card'=>'Tarjeta bancaria','booking.card_help'=>'CB / Visa / Mastercard','booking.apple_help'=>'Si está disponible en tu dispositivo','booking.ideal_help'=>'Para clientes neerlandeses','booking.sepa'=>'Transferencia SEPA','booking.privacy'=>'Acepto que mis datos se utilicen para gestionar esta solicitud de reserva.','booking.submit'=>'Crear mi reserva (demo)','booking.secure'=>'El pago con Mollie se conectará por separado cuando se valide este proceso de reserva.',
            'price.request'=>'Precio bajo petición','price.from'=>'Desde','price.person'=>' / persona','price.couple'=>' / pareja','price.trip'=>' / viaje','card.view'=>'Ver circuito →',
            'js.confirm'=>'Precio por confirmar','js.final'=>'El precio final se confirmará antes de reservar.','js.no_rate'=>'No hay una tarifa configurada para este tamaño de grupo.','js.adult'=>'adulto','js.child'=>'niño','js.single'=>'habitación individual','js.season'=>'Suplemento de temporada alta del %s%% incluido.',
        ],
    ];
}

function slt_t(string $key,string $fallback=''): string {
    $lang=slt_current_language();
    if ($lang==='fr') return $fallback!=='' ? $fallback : $key;
    $dict=slt_ui_dictionary();
    return (string)($dict[$lang][$key]??($fallback!==''?$fallback:$key));
}

function slt_content_translations(): array {
    static $data=null;
    if ($data!==null) return $data;
    $base_path=get_template_directory().'/inc/translations.php';
    $detail_path=get_template_directory().'/inc/tour-translations-detailed.php';
    $base=file_exists($base_path) ? (array)require $base_path : [];
    $detail=file_exists($detail_path) ? (array)require $detail_path : [];
    $data=array_replace_recursive($base,$detail);
    return $data;
}

function slt_translated_post_value(int $post_id,string $field,$fallback) {
    $lang=slt_current_language();
    if ($lang==='fr') return $fallback;
    $post=get_post($post_id);
    if (!$post) return $fallback;
    $group=$post->post_type==='slt_tour'?'tours':($post->post_type==='page'?'pages':'');
    if ($group==='') return $fallback;
    $stored=get_post_meta($post_id,'_slt_i18n',true);
    if(is_array($stored) && isset($stored[$lang]) && array_key_exists($field,$stored[$lang])) return $stored[$lang][$field];
    $data=slt_content_translations();
    return $data[$group][$post->post_name][$lang][$field]??$fallback;
}

function slt_translate_tour_field(string $field,int $post_id,$fallback) {
    if (!in_array($field,['short_tagline','highlights','itinerary','included','excluded'],true)) return $fallback;
    $translated=slt_translated_post_value($post_id,$field,$fallback);
    if(is_array($fallback) && is_array($translated) && in_array($field,['highlights','itinerary','included','excluded'],true)){
        $merged=[];
        foreach($fallback as $i=>$row){
            $base=is_array($row)?$row:[];
            $overlay=isset($translated[$i])&&is_array($translated[$i])?$translated[$i]:[];
            $merged[]=array_merge($base,$overlay);
        }
        return $merged;
    }
    return $translated;
}

add_filter('language_attributes',function(string $output): string {
    return preg_replace('/lang=(["\']).*?\1/','lang="'.esc_attr(slt_current_language()).'"',$output) ?: $output;
});

add_filter('the_title',function(string $title,int $post_id): string {
    if (is_admin() || slt_current_language()==='fr' || !$post_id) return $title;
    return (string)slt_translated_post_value($post_id,'title',$title);
},20,2);

add_filter('the_content',function(string $content): string {
    if (is_admin() || slt_current_language()==='fr' || !is_singular(['page','slt_tour'])) return $content;
    return (string)slt_translated_post_value(get_the_ID(),'content',$content);
},20);

add_filter('wp_nav_menu_objects',function(array $items): array {
    if (slt_current_language()==='fr') return $items;
    $map=[
        'Accueil'=>'nav.home','Circuits'=>'nav.tours','Nos services'=>'nav.services',
        'À propos'=>'nav.about','Pourquoi nous'=>'home.why.eyebrow','FAQ'=>'nav.faq','Contact'=>'nav.contact'
    ];
    foreach($items as $item){
        if(isset($map[$item->title]))$item->title=slt_t($map[$item->title],$item->title);
    }
    return $items;
});

function slt_language_options_html(): string {
    $current=slt_current_language();
    $html='';
    foreach(slt_supported_languages() as $code=>$meta){
        $html.='<option value="'.esc_url(slt_language_switch_url($code)).'" '.selected($current,$code,false).'>'.esc_html($meta['short']).' — '.esc_html($meta['label']).'</option>';
    }
    return $html;
}
