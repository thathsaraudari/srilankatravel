<?php
/**
 * Plugin Name: SLT Bootstrap
 * Description: One-time setup and demo content for the Sri Lanka Travel site.
 * Version: 0.4.0
 */
if (!defined('ABSPATH')) exit;

add_action('init', function (): void {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin='slt-core/slt-core.php';
    if (!class_exists('SLT_Core') && file_exists(WP_PLUGIN_DIR.'/slt-core/slt-core.php')) {
        require_once WP_PLUGIN_DIR.'/slt-core/slt-core.php';
    }
    if (!is_plugin_active($plugin) && class_exists('SLT_Core')) {
        $result=activate_plugin($plugin);
        if (is_wp_error($result)) update_option('slt_bootstrap_error',$result->get_error_message(),false);
    }

    $theme=wp_get_theme('slt-travel');
    if ($theme->exists() && get_stylesheet()!=='slt-travel') switch_theme('slt-travel');

    if (get_option('slt_bootstrap_completed_v4')) return;

    $contact=get_page_by_path('contact');
    if ($contact) {
        $contact_id=$contact->ID;
        wp_update_post([
            'ID'=>$contact_id,
            'post_title'=>'Contact',
            'post_content'=>'<p>Indiquez-nous vos dates, le nombre de voyageurs et ce que vous aimeriez découvrir au Sri Lanka.</p>[slt_enquiry_form tour_id="0"]'
        ]);
    } else {
        $contact_id=wp_insert_post([
            'post_type'=>'page','post_status'=>'publish','post_title'=>'Contact','post_name'=>'contact',
            'post_content'=>'<p>Indiquez-nous vos dates, le nombre de voyageurs et ce que vous aimeriez découvrir au Sri Lanka.</p>[slt_enquiry_form tour_id="0"]'
        ]);
    }

    $hotel_data=[
        'Cinnamon Lodge'=>['location'=>'Habarana','rating'=>'5'],
        "Earl's Regency"=>['location'=>'Kandy','rating'=>'5'],
        'Jetwing St Andrews'=>['location'=>'Nuwara Eliya','rating'=>'5'],
        'Cinnamon Bey'=>['location'=>'Beruwala','rating'=>'5']
    ];
    $hotels=[];
    foreach($hotel_data as $name=>$data){
        $existing=get_page_by_title($name,OBJECT,'slt_hotel');
        $id=$existing?$existing->ID:wp_insert_post(['post_type'=>'slt_hotel','post_status'=>'publish','post_title'=>$name]);
        if(!is_wp_error($id)&&$id){
            update_post_meta($id,'location',$data['location']);
            update_post_meta($id,'star_rating',$data['rating']);
            $hotels[$name]=(int)$id;
        }
    }

    $terms=[];
    foreach(['Habarana','Dambulla','Sigiriya','Kandy','Nuwara Eliya','Beruwala','Balapitiya','Kosgoda'] as $destination){
        $term=term_exists($destination,'slt_destination');
        if(!$term)$term=wp_insert_term($destination,'slt_destination');
        if(!is_wp_error($term))$terms[]=(int)(is_array($term)?$term['term_id']:$term);
    }
    foreach(['Culture','Safari','Pays du thé','Plage'] as $style){
        if(!term_exists($style,'slt_travel_style'))wp_insert_term($style,'slt_travel_style');
    }

    $demo=get_page_by_title('Découverte du Sri Lanka – 7 jours',OBJECT,'slt_tour');
    if(!$demo)$demo=get_page_by_title('Sri Lanka Highlights – 7 Days',OBJECT,'slt_tour');
    if(!$demo){
        $existing_tours=get_posts(['post_type'=>'slt_tour','post_status'=>'any','posts_per_page'=>2]);
        if(count($existing_tours)===1)$demo=$existing_tours[0];
    }

    if($demo){
        $tour_id=$demo->ID;
        wp_update_post([
            'ID'=>$tour_id,
            'post_title'=>'Découverte du Sri Lanka – 7 jours',
            'post_excerpt'=>'Un voyage privé entre patrimoine, safari, montagnes de thé et côte sud-ouest.',
            'post_content'=>'<p>Commencez au cœur du Triangle culturel, poursuivez vers Kandy et les paysages frais de Nuwara Eliya, puis terminez votre voyage au bord de l’océan Indien à Beruwala.</p><p>Cet itinéraire est une base : les dates, les hôtels et les expériences peuvent être adaptés à votre projet.</p>'
        ]);
    } else {
        $tour_id=wp_insert_post([
            'post_type'=>'slt_tour','post_status'=>'publish','post_title'=>'Découverte du Sri Lanka – 7 jours',
            'post_excerpt'=>'Un voyage privé entre patrimoine, safari, montagnes de thé et côte sud-ouest.',
            'post_content'=>'<p>Commencez au cœur du Triangle culturel, poursuivez vers Kandy et les paysages frais de Nuwara Eliya, puis terminez votre voyage au bord de l’océan Indien à Beruwala.</p><p>Cet itinéraire est une base : les dates, les hôtels et les expériences peuvent être adaptés à votre projet.</p>'
        ]);
    }

    if(!is_wp_error($tour_id)&&$tour_id){
        update_post_meta($tour_id,'_slt_seeded_demo',1);
        update_post_meta($tour_id,'duration_days',7);
        update_post_meta($tour_id,'duration_nights',6);
        update_post_meta($tour_id,'price_basis','request');
        update_post_meta($tour_id,'featured',1);
        update_post_meta($tour_id,'short_tagline','Culture, safari, pays du thé et plage dans un même voyage privé.');
        update_post_meta($tour_id,'highlights',[
            ['text'=>'Temple troglodyte de Dambulla'],
            ['text'=>'Forteresse de Sigiriya'],
            ['text'=>'Safari au parc national de Kaudulla'],
            ['text'=>'Spectacle culturel kandyen'],
            ['text'=>'Temple de la Dent'],
            ['text'=>'Ramboda Falls et plantations de thé'],
            ['text'=>'Plage de Beruwala'],
            ['text'=>'Rivière Madu et centre de protection des tortues de Kosgoda']
        ]);
        update_post_meta($tour_id,'itinerary',[
            ['day'=>1,'title'=>'Aéroport → Habarana','description'=>'Arrivée au Sri Lanka et route vers Habarana. Après l’installation à l’hôtel et un moment de repos, visite du temple troglodyte de Dambulla, classé au patrimoine mondial de l’UNESCO. Retour à l’hôtel pour le dîner.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['dinner']],
            ['day'=>2,'title'=>'Sigiriya & safari à Kaudulla','description'=>'Après le petit-déjeuner, visite de la forteresse de Sigiriya, ancienne citadelle royale associée au roi Kasyapa. Retour à l’hôtel pour vous détendre, puis safari en fin de journée au parc national de Kaudulla, particulièrement connu pour ses éléphants.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['breakfast','dinner']],
            ['day'=>3,'title'=>'Habarana → Kandy','description'=>'Départ vers Kandy après le petit-déjeuner. Arrêt dans un jardin d’épices à Matale, puis continuation vers Kandy. Plus tard, découverte de l’artisanat traditionnel et spectacle de danses kandiennes.','hotel_id'=>$hotels["Earl's Regency"]??0,'meals'=>['breakfast','dinner']],
            ['day'=>4,'title'=>'Kandy → Nuwara Eliya','description'=>'Visite du Temple de la Dent à Kandy, puis route vers les montagnes en passant par les chutes de Ramboda. À Nuwara Eliya, découverte de la culture et de la fabrication du thé de Ceylan dans une plantation et une fabrique.','hotel_id'=>$hotels['Jetwing St Andrews']??0,'meals'=>['breakfast','dinner']],
            ['day'=>5,'title'=>'Nuwara Eliya → Beruwala','description'=>'Après le petit-déjeuner, départ des montagnes vers Beruwala sur la côte sud-ouest. Installation à l’hôtel et soirée libre au bord de la plage.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>6,'title'=>'Rivière Madu & Kosgoda','description'=>'Exploration de la rivière Madu et de ses mangroves à Balapitiya, puis visite d’un centre de protection des tortues à Kosgoda. Retour à Beruwala pour la dernière nuit du circuit.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>7,'title'=>'Beruwala → Aéroport','description'=>'Selon l’horaire du vol retour, profitez de l’hôtel avant le départ vers l’aéroport.','hotel_id'=>0,'meals'=>['breakfast']]
        ]);
        update_post_meta($tour_id,'included',[
            ['item'=>'6 nuits dans les hôtels indiqués dans l’itinéraire ou dans des établissements équivalents confirmés'],
            ['item'=>'Les repas indiqués pour chaque journée']
        ]);
        update_post_meta($tour_id,'excluded',[
            ['item'=>'Vols internationaux'],
            ['item'=>'Dépenses personnelles et prestations non confirmées dans le devis final']
        ]);
        wp_set_object_terms($tour_id,$terms,'slt_destination');
        wp_set_object_terms($tour_id,['Culture','Safari','Pays du thé','Plage'],'slt_travel_style');
    }

    $locations=get_theme_mod('nav_menu_locations',[]);
    $menu=wp_get_nav_menu_object('Primary');
    $menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu('Primary');
    if(!is_wp_error($menu_id)){
        $items=wp_get_nav_menu_items($menu_id)?:[];
        if(!$items){
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Accueil','menu-item-url'=>home_url('/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Circuits','menu-item-url'=>get_post_type_archive_link('slt_tour')?:home_url('/tours/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            if(!is_wp_error($contact_id)&&$contact_id)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Contact','menu-item-object-id'=>(int)$contact_id,'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
        } else {
            foreach($items as $item){
                $map=['Home'=>'Accueil','Tours'=>'Circuits'];
                if(isset($map[$item->title]))wp_update_nav_menu_item($menu_id,$item->ID,['menu-item-title'=>$map[$item->title]]);
            }
        }
        $locations['primary']=$menu_id;
        $locations['footer']=$menu_id;
        set_theme_mod('nav_menu_locations',$locations);
    }

    if(!get_option('slt_settings'))update_option('slt_settings',['currency'=>'EUR','deposit_percent'=>30,'payment_mode'=>'enquiry']);
    flush_rewrite_rules(false);
    update_option('slt_bootstrap_completed_v2',1,false);
    update_option('slt_bootstrap_completed_v3',1,false);
    update_option('slt_bootstrap_completed_v4',1,false);
    delete_option('slt_bootstrap_error');
},50);


/**
 * V5: informational pages and navigation only.
 * Kept separate from the original demo bootstrap so this migration can be
 * deployed/rolled back independently of tour and booking logic.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v5_pages')) return;

    $upsert_page = function (string $slug, string $title, string $content): int {
        $page = get_page_by_path($slug);
        $data = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => $content,
        ];
        if ($page) $data['ID'] = $page->ID;
        $id = $page ? wp_update_post($data, true) : wp_insert_post($data, true);
        return is_wp_error($id) ? 0 : (int) $id;
    };

    $pages = [];
    $pages['about'] = $upsert_page('a-propos', 'À propos',
        '<p>Nous créons des voyages privés au Sri Lanka avec une approche simple : des itinéraires clairs, flexibles et pensés pour être faciles à organiser depuis l’Europe.</p>' .
        '<h2>Notre approche</h2><p>Nos circuits associent patrimoine, nature, montagnes et côte sans surcharger les journées. Les itinéraires présentés servent de base et peuvent évoluer selon les disponibilités et les préférences des voyageurs.</p>' .
        '<h2>Une organisation locale</h2><p>Les prestations sur place sont coordonnées avec des partenaires locaux au Sri Lanka afin de construire un voyage cohérent de l’arrivée au départ.</p>'
    );

    $pages['why'] = $upsert_page('pourquoi-nous', 'Pourquoi nous',
        '<h2>Voyages privés</h2><p>Vous voyagez à votre rythme, sans groupe imposé.</p>' .
        '<h2>Itinéraires lisibles</h2><p>Chaque journée présente les étapes importantes, les hôtels et les repas prévus.</p>' .
        '<h2>Flexibilité</h2><p>Les hôtels, certaines activités et le rythme peuvent être adaptés avant la confirmation finale.</p>' .
        '<h2>Paiement européen</h2><p>Le futur checkout sera conçu autour de moyens de paiement familiers en Europe, avec Mollie comme prestataire de paiement.</p>'
    );

    $pages['faq'] = $upsert_page('faq', 'Questions fréquentes',
        '<h2>Les circuits sont-ils privés ?</h2><p>Oui. Les itinéraires sont conçus pour des voyages privés.</p>' .
        '<h2>Puis-je modifier un circuit ?</h2><p>Oui. Les hôtels, étapes et activités peuvent être adaptés selon les disponibilités.</p>' .
        '<h2>Les vols sont-ils inclus ?</h2><p>Sauf indication contraire, les vols internationaux ne sont pas inclus.</p>' .
        '<h2>Comment se passe le paiement ?</h2><p>Le site est actuellement en démonstration. Un parcours de paiement européen sera ajouté progressivement après validation de l’expérience de réservation.</p>'
    );

    $pages['payment'] = $upsert_page('paiement', 'Paiement sécurisé',
        '<p>Le site sera préparé pour un paiement sécurisé via Mollie, avec des moyens de paiement adaptés aux voyageurs européens.</p>' .
        '<h2>Moyens de paiement prévus</h2><p>Wero lorsqu’il est disponible, carte bancaire, Visa, Mastercard, Apple Pay, iDEAL pour les clients néerlandais, PayPal et virement SEPA.</p>' .
        '<p><strong>Mode démonstration :</strong> aucun paiement réel n’est actuellement débité sur ce site.</p>'
    );

    $pages['terms'] = $upsert_page('conditions-generales', 'Conditions générales',
        '<p><strong>Brouillon de démonstration.</strong> Cette page devra être complétée avec les informations légales de l’entreprise, les règles d’annulation, les remboursements et les responsabilités avant toute vente réelle.</p>' .
        '<h2>Réservation</h2><p>Une réservation n’est définitive qu’après confirmation des disponibilités et acceptation du prix final.</p>' .
        '<h2>Prix et acompte</h2><p>Le montant de l’acompte et le solde restant seront indiqués avant le paiement.</p>' .
        '<h2>Modification et annulation</h2><p>Les conditions détaillées seront définies avant l’ouverture commerciale du site.</p>'
    );

    $pages['privacy'] = $upsert_page('politique-confidentialite', 'Politique de confidentialité',
        '<p><strong>Brouillon de démonstration.</strong> L’identité complète du responsable du traitement et les durées de conservation seront ajoutées avant lancement.</p>' .
        '<h2>Données collectées</h2><p>Les demandes de voyage peuvent inclure le nom, l’adresse e-mail, le téléphone, les dates et le nombre de voyageurs.</p>' .
        '<h2>Utilisation</h2><p>Ces informations servent uniquement à répondre aux demandes et organiser les prestations demandées.</p>' .
        '<h2>Paiement</h2><p>Lorsque le paiement sera activé, les données bancaires sensibles seront traitées par le prestataire de paiement et ne seront pas stockées directement sur ce site.</p>'
    );

    $contact = get_page_by_path('contact');
    $contact_id = $contact ? (int) $contact->ID : 0;

    $menu = wp_get_nav_menu_object('Primary');
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu('Primary');
    if (!is_wp_error($menu_id)) {
        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            wp_delete_post($item->ID, true);
        }

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Accueil',
            'menu-item-url' => home_url('/'),
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Circuits',
            'menu-item-url' => get_post_type_archive_link('slt_tour') ?: home_url('/tours/'),
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);

        foreach ([
            ['title' => 'À propos', 'id' => $pages['about']],
            ['title' => 'Pourquoi nous', 'id' => $pages['why']],
            ['title' => 'FAQ', 'id' => $pages['faq']],
            ['title' => 'Contact', 'id' => $contact_id],
        ] as $entry) {
            if (!$entry['id']) continue;
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $entry['title'],
                'menu-item-object-id' => $entry['id'],
                'menu-item-object' => 'page',
                'menu-item-status' => 'publish',
                'menu-item-type' => 'post_type',
            ]);
        }

        $footer = wp_get_nav_menu_object('Footer');
        $footer_id = $footer ? (int) $footer->term_id : wp_create_nav_menu('Footer');
        if (!is_wp_error($footer_id)) {
            foreach (wp_get_nav_menu_items($footer_id) ?: [] as $item) {
                wp_delete_post($item->ID, true);
            }
            foreach ([
                ['title' => 'À propos', 'id' => $pages['about']],
                ['title' => 'FAQ', 'id' => $pages['faq']],
                ['title' => 'Paiement', 'id' => $pages['payment']],
                ['title' => 'Conditions générales', 'id' => $pages['terms']],
                ['title' => 'Confidentialité', 'id' => $pages['privacy']],
                ['title' => 'Contact', 'id' => $contact_id],
            ] as $entry) {
                if (!$entry['id']) continue;
                wp_update_nav_menu_item($footer_id, 0, [
                    'menu-item-title' => $entry['title'],
                    'menu-item-object-id' => $entry['id'],
                    'menu-item-object' => 'page',
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'post_type',
                ]);
            }
        }

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $menu_id;
        if (!empty($footer_id) && !is_wp_error($footer_id)) $locations['footer'] = $footer_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    update_option('slt_bootstrap_completed_v5_pages', 1, false);
}, 60);


/**
 * V6: demo branding only.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v6_brand')) return;
    if (get_option('blogname') === 'My Blog' || trim((string)get_option('blogname')) === '') {
        update_option('blogname', 'Sri Lanka Voyages');
    }
    update_option('blogdescription', 'Voyages privés au Sri Lanka');
    update_option('slt_bootstrap_completed_v6_brand', 1, false);
}, 70);


/**
 * V7: complete public site structure and copy.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v7_complete_site')) return;

    $upsert = function(string $slug,string $title,string $content): int {
        $page=get_page_by_path($slug);
        $data=['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$content];
        if($page)$data['ID']=$page->ID;
        $id=$page?wp_update_post($data,true):wp_insert_post($data,true);
        return is_wp_error($id)?0:(int)$id;
    };

    $pages=[];
    $pages['services']=$upsert('nos-services','Nos services',
        '<p class="lead">Un voyage au Sri Lanka ne se résume pas à une liste d’hôtels. Nous organisons les éléments essentiels autour d’un itinéraire cohérent et adaptable.</p>'.
        '<h2>Circuits privés</h2><p>Des voyages conçus pour votre groupe uniquement, avec chauffeur et étapes organisées selon l’itinéraire confirmé.</p>'.
        '<h2>Voyages sur mesure</h2><p>Vous pouvez partir d’un circuit existant ou nous transmettre vos dates, votre rythme et vos centres d’intérêt afin d’adapter le programme.</p>'.
        '<h2>Transferts & chauffeur</h2><p>Accueil à l’aéroport et déplacements privés au Sri Lanka coordonnés avec des partenaires locaux sélectionnés.</p>'.
        '<h2>Hôtels & hébergements</h2><p>Nous intégrons les hôtels au programme en fonction de la catégorie souhaitée, du parcours et des disponibilités.</p>'.
        '<h2>Excursions & expériences</h2><p>Sites culturels, safaris, plantations de thé, balades en bateau, plages et autres expériences peuvent être ajoutés à votre voyage.</p>'
    );
    $pages['how']=$upsert('comment-ca-marche','Comment ça marche',
        '<p class="lead">Une réservation claire, en quatre étapes.</p>'.
        '<h2>1. Choisissez ou imaginez votre voyage</h2><p>Commencez avec l’un de nos circuits ou décrivez-nous votre projet.</p>'.
        '<h2>2. Personnalisation & disponibilités</h2><p>Nous adaptons le rythme, les hôtels et les expériences puis vérifions les disponibilités auprès de nos partenaires.</p>'.
        '<h2>3. Confirmation du programme et du prix</h2><p>Vous recevez les prestations prévues, le prix final et les conditions applicables avant tout paiement.</p>'.
        '<h2>4. Réservation & préparation</h2><p>Après confirmation, vous réservez selon les modalités indiquées et recevez les informations utiles pour préparer votre départ.</p>'
    );
    $pages['about']=$upsert('a-propos','À propos',
        '<p class="lead">Sri Lanka Voyages est né d’une idée simple : apporter au voyage au Sri Lanka le même niveau d’attention, de communication et de fiabilité que celui attendu par les voyageurs en Europe.</p>'.
        '<h2>Une expérience avec les voyageurs internationaux</h2><p>Notre équipe s’appuie sur une expérience de service aux voyageurs en France, notamment à travers Private Cab Transfert, qui propose des transferts privés, des prises en charge aéroport et des excursions. Cette expérience nous a appris l’importance de la ponctualité, d’une communication claire et d’un accompagnement simple.</p>'.
        '<h2>Une organisation locale au Sri Lanka</h2><p>Pour les circuits au Sri Lanka, les prestations sont organisées avec des partenaires locaux. L’objectif est de réunir transport, hôtels et expériences dans un itinéraire facile à comprendre avant votre départ.</p>'.
        '<h2>Des voyages qui restent personnels</h2><p>Les programmes présentés sur le site sont des bases. Nous pouvons ajuster les étapes, la durée, les hébergements et certaines activités selon vos préférences et les disponibilités.</p>'
    );
    $pages['why']=$upsert('pourquoi-nous','Pourquoi nous choisir',
        '<h2>Voyages privés</h2><p>Votre circuit est organisé pour votre groupe, sans départ collectif imposé.</p>'.
        '<h2>Expérience du service en Europe</h2><p>Notre expérience auprès de voyageurs internationaux en France influence notre façon de communiquer et d’organiser chaque étape.</p>'.
        '<h2>Partenaires locaux</h2><p>Les prestations au Sri Lanka sont coordonnées avec des partenaires locaux afin d’assurer la continuité du voyage.</p>'.
        '<h2>Flexibilité</h2><p>Les itinéraires peuvent être ajustés avant confirmation selon vos dates, votre rythme et vos préférences.</p>'.
        '<h2>Clarté avant paiement</h2><p>Le programme, les prestations et le prix final sont confirmés avant la réservation définitive.</p>'
    );
    $pages['faq']=$upsert('faq','Questions fréquentes',
        '<h2>Les circuits sont-ils privés ?</h2><p>Oui. Les itinéraires sont prévus pour votre groupe uniquement.</p>'.
        '<h2>Puis-je modifier un circuit ?</h2><p>Oui. La durée, certains hôtels, étapes et activités peuvent être adaptés selon les disponibilités.</p>'.
        '<h2>Les vols internationaux sont-ils inclus ?</h2><p>Non, sauf mention explicite dans une proposition personnalisée.</p>'.
        '<h2>Comment le prix est-il calculé ?</h2><p>Il dépend notamment des dates, du nombre de voyageurs, des hôtels, du transport et des activités choisies.</p>'.
        '<h2>Comment se passe le paiement ?</h2><p>Le programme et le prix sont d’abord confirmés. Le site est actuellement en démonstration ; le paiement en ligne sera activé avec un prestataire européen avant l’ouverture commerciale.</p>'.
        '<h2>Qui organise le voyage sur place ?</h2><p>Les prestations au Sri Lanka sont exécutées et coordonnées avec des partenaires locaux selon le programme confirmé.</p>'
    );

    $contact=get_page_by_path('contact');
    $contact_id=$contact?(int)$contact->ID:0;

    $menu=wp_get_nav_menu_object('Primary');
    $menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu('Primary');
    if(!is_wp_error($menu_id)){
        foreach(wp_get_nav_menu_items($menu_id)?:[] as $item)wp_delete_post($item->ID,true);
        $entries=[
            ['Accueil',0,home_url('/')],
            ['Circuits',0,get_post_type_archive_link('slt_tour')?:home_url('/tours/')],
            ['Nos services',$pages['services'],''],
            ['À propos',$pages['about'],''],
            ['FAQ',$pages['faq'],''],
            ['Contact',$contact_id,''],
        ];
        foreach($entries as [$title,$id,$url]){
            if($id)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$title,'menu-item-object-id'=>$id,'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
            elseif($url)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$title,'menu-item-url'=>$url,'menu-item-status'=>'publish','menu-item-type'=>'custom']);
        }
        $locations=get_theme_mod('nav_menu_locations',[]);
        $locations['primary']=$menu_id;
        set_theme_mod('nav_menu_locations',$locations);
    }

    update_option('blogname','Sri Lanka Voyages');
    update_option('blogdescription','Voyages privés et sur mesure au Sri Lanka');
    update_option('slt_bootstrap_completed_v7_complete_site',1,false);
    flush_rewrite_rules(false);
},80);


/**
 * V8: seed a working demo price matrix for the sample tour.
 * These values are examples only and should be replaced with partner rates.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v8_pricing')) return;

    $tour=get_page_by_title('Découverte du Sri Lanka – 7 jours',OBJECT,'slt_tour');
    if($tour){
        $id=(int)$tour->ID;
        update_post_meta($id,'pricing_mode','matrix');
        update_post_meta($id,'price_1',1690);
        update_post_meta($id,'price_2',1290);
        update_post_meta($id,'price_3_4',1150);
        update_post_meta($id,'price_5_6',1050);
        update_post_meta($id,'price_7_plus',990);
        update_post_meta($id,'child_discount_percent',30);
        update_post_meta($id,'single_room_supplement',220);
        update_post_meta($id,'season_start','2026-12-15');
        update_post_meta($id,'season_end','2027-01-15');
        update_post_meta($id,'season_surcharge_percent',15);
        delete_post_meta($id,'deposit_percent_override');
        update_post_meta($id,'price_from',990);
        update_post_meta($id,'price_basis','person');
        update_post_meta($id,'_slt_demo_pricing',1);
    }

    update_option('slt_bootstrap_completed_v8_pricing',1,false);
},90);


/**
 * V9: configure concrete demo selling prices for the sample 7-day tour.
 * Replace with contracted DMC/supplier rates before accepting real payments.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v9_prices')) return;

    $tour=get_page_by_title('Découverte du Sri Lanka – 7 jours',OBJECT,'slt_tour');
    if(!$tour){
        $candidates=get_posts([
            'post_type'=>'slt_tour',
            'post_status'=>'publish',
            'posts_per_page'=>1,
            'orderby'=>'date',
            'order'=>'ASC'
        ]);
        $tour=$candidates[0]??null;
    }

    if($tour){
        $id=(int)$tour->ID;

        // Demo retail prices per traveller, decreasing with group size.
        update_post_meta($id,'pricing_mode','matrix');
        update_post_meta($id,'price_1',1590);
        update_post_meta($id,'price_2',1190);
        update_post_meta($id,'price_3_4',1040);
        update_post_meta($id,'price_5_6',940);
        update_post_meta($id,'price_7_plus',875);

        // Family / room rules.
        update_post_meta($id,'child_discount_percent',25);
        update_post_meta($id,'single_room_supplement',195);

        // Peak season example.
        update_post_meta($id,'season_start','2026-12-15');
        update_post_meta($id,'season_end','2027-01-15');
        update_post_meta($id,'season_surcharge_percent',12);

        // Per-tour deposit override.
        update_post_meta($id,'deposit_percent_override',30);

        // Public card/archive price.
        update_post_meta($id,'price_from',875);
        update_post_meta($id,'price_basis','person');

        // Keep the admin warning visible until real partner rates replace these.
        update_post_meta($id,'_slt_demo_pricing',1);
    }

    update_option('slt_bootstrap_completed_v9_prices',1,false);
},95);


/**
 * V10: build out the demo tour catalogue.
 * All prices are illustrative demo rates until replaced by contracted partner rates.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v10_tour_catalog')) return;

    $term_ids=function(string $taxonomy,array $names): array {
        $ids=[];
        foreach($names as $name){
            $term=term_exists($name,$taxonomy);
            if(!$term)$term=wp_insert_term($name,$taxonomy);
            if(!is_wp_error($term))$ids[]=(int)(is_array($term)?$term['term_id']:$term);
        }
        return $ids;
    };

    $packages=[
        [
            'title'=>'Sri Lanka Classique – 10 jours',
            'slug'=>'sri-lanka-classique-10-jours',
            'days'=>10,'nights'=>9,'from'=>1090,
            'tagline'=>'Le Triangle culturel, Kandy, les plantations de thé, Ella et la côte sud dans un grand classique équilibré.',
            'content'=>'<p>Un premier voyage idéal au Sri Lanka : patrimoine ancien, collines verdoyantes, train panoramique, nature et quelques jours au bord de l’océan Indien.</p><p>L’itinéraire peut être adapté selon votre rythme et les disponibilités.</p>',
            'image'=>'https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=1400&q=82',
            'destinations'=>['Negombo','Sigiriya','Dambulla','Kandy','Nuwara Eliya','Ella','Yala','Galle'],
            'styles'=>['Culture','Nature','Pays du thé','Plage'],
            'pricing'=>[1990,1490,1320,1190,1090,25,235],
            'highlights'=>['Sigiriya et Dambulla','Temple de la Dent à Kandy','Plantations de thé','Train vers Ella','Safari à Yala','Fort de Galle'],
            'itinerary'=>[
                ['Arrivée → Negombo','Accueil à l’aéroport et première nuit près de la côte pour récupérer du voyage.'],
                ['Negombo → Sigiriya','Route vers le Triangle culturel et installation dans la région de Sigiriya.'],
                ['Sigiriya & Dambulla','Ascension de Sigiriya puis découverte des temples troglodytes de Dambulla.'],
                ['Sigiriya → Kandy','Route vers Kandy avec arrêt dans la région de Matale.'],
                ['Kandy','Temple de la Dent, marché local et découverte de la ville autour du lac.'],
                ['Kandy → Nuwara Eliya','Route panoramique via les plantations et fabriques de thé.'],
                ['Nuwara Eliya → Ella','Voyage vers Ella, avec trajet ferroviaire panoramique selon disponibilité.'],
                ['Ella → Yala','Matinée dans les montagnes puis route vers la région de Yala.'],
                ['Yala → Galle','Safari tôt le matin puis route vers la côte et le fort historique de Galle.'],
                ['Galle → Aéroport','Derniers moments sur la côte avant le transfert vers l’aéroport.'],
            ],
        ],
        [
            'title'=>'Sri Lanka en Famille – 12 jours',
            'slug'=>'sri-lanka-en-famille-12-jours',
            'days'=>12,'nights'=>11,'from'=>1220,
            'tagline'=>'Un rythme plus doux, des animaux, des plages et des étapes adaptées aux familles avec enfants.',
            'content'=>'<p>Un circuit pensé pour voyager avec des enfants : moins de longues journées, davantage de pauses et une combinaison d’animaux, de nature, de culture et de plage.</p>',
            'image'=>'https://images.unsplash.com/photo-1586193804147-64d5c02ef9c1?auto=format&fit=crop&w=1400&q=82',
            'destinations'=>['Negombo','Habarana','Sigiriya','Kandy','Nuwara Eliya','Ella','Udawalawe','Bentota'],
            'styles'=>['Famille','Safari','Nature','Plage'],
            'pricing'=>[2290,1690,1490,1340,1220,35,260],
            'highlights'=>['Éléphants sauvages','Rythme adapté aux enfants','Train des montagnes','Safari à Udawalawe','Temps libre à la plage','Activités flexibles'],
            'itinerary'=>[
                ['Arrivée → Negombo','Accueil et installation pour une première journée légère.'],
                ['Negombo → Habarana','Départ vers le centre du pays avec pauses en cours de route.'],
                ['Sigiriya','Visite de Sigiriya à un rythme adapté à la famille.'],
                ['Safari & village','Safari dans un parc de la région et découverte de la campagne.'],
                ['Habarana → Kandy','Route vers Kandy via Matale.'],
                ['Kandy','Visite culturelle et après-midi plus libre.'],
                ['Kandy → Nuwara Eliya','Découverte des plantations de thé et du climat des montagnes.'],
                ['Nuwara Eliya → Ella','Route ou train panoramique selon disponibilité.'],
                ['Ella','Journée détendue autour d’Ella avec promenades faciles.'],
                ['Ella → Udawalawe','Route vers Udawalawe et safari en fin de journée ou le lendemain matin.'],
                ['Udawalawe → Bentota','Départ vers la côte pour profiter de la plage.'],
                ['Bentota → Aéroport','Matinée libre puis transfert à l’aéroport.'],
            ],
        ],
        [
            'title'=>'Safari & Plages – 9 jours',
            'slug'=>'safari-plages-sri-lanka-9-jours',
            'days'=>9,'nights'=>8,'from'=>1030,
            'tagline'=>'Éléphants, léopards, mangroves et plages tropicales pour un voyage centré sur la nature.',
            'content'=>'<p>Un itinéraire pour ceux qui veulent consacrer une grande partie du séjour à la faune sauvage et terminer par la côte sud.</p>',
            'image'=>'https://images.unsplash.com/photo-1566650576880-6740b03eaad1?auto=format&fit=crop&w=1400&q=82',
            'destinations'=>['Habarana','Kaudulla','Kandy','Ella','Yala','Mirissa','Galle'],
            'styles'=>['Safari','Animaux','Nature','Plage'],
            'pricing'=>[1850,1390,1240,1120,1030,25,210],
            'highlights'=>['Safari éléphants','Montagnes d’Ella','Parc national de Yala','Côte de Mirissa','Fort de Galle','Mangroves et nature'],
            'itinerary'=>[
                ['Arrivée → Habarana','Accueil et route vers le centre du pays.'],
                ['Safari à Kaudulla','Matinée libre puis safari à la recherche des troupeaux d’éléphants.'],
                ['Habarana → Kandy','Route vers Kandy et découverte de la ville.'],
                ['Kandy → Ella','Traversée des montagnes et paysages de thé.'],
                ['Ella','Randonnée légère et points de vue autour d’Ella.'],
                ['Ella → Yala','Route vers le sud-est et préparation du safari.'],
                ['Safari à Yala → Mirissa','Safari matinal puis départ vers la côte.'],
                ['Mirissa & Galle','Plage et découverte de Galle selon vos envies.'],
                ['Côte → Aéroport','Temps libre avant le transfert retour.'],
            ],
        ],
        [
            'title'=>'Lune de miel au Sri Lanka – 8 jours',
            'slug'=>'lune-de-miel-sri-lanka-8-jours',
            'days'=>8,'nights'=>7,'from'=>1150,
            'tagline'=>'Hôtels de charme, montagnes, expériences privées et plage pour un voyage romantique.',
            'content'=>'<p>Une semaine romantique mêlant paysages iconiques, hébergements de charme, expériences à deux et fin de séjour au bord de l’océan.</p>',
            'image'=>'https://images.unsplash.com/photo-1589373797397-d19670f47549?auto=format&fit=crop&w=1400&q=82',
            'destinations'=>['Sigiriya','Kandy','Nuwara Eliya','Ella','Bentota'],
            'styles'=>['Lune de miel','Romantique','Pays du thé','Plage'],
            'pricing'=>[1850,1450,1320,1220,1150,20,290],
            'highlights'=>['Coucher de soleil à Sigiriya','Hôtels de charme','Tea country','Train panoramique','Dîner romantique','Plage en fin de séjour'],
            'itinerary'=>[
                ['Arrivée → Sigiriya','Accueil privé et route vers le Triangle culturel.'],
                ['Sigiriya','Découverte de Sigiriya et temps libre dans un cadre tropical.'],
                ['Sigiriya → Kandy','Route vers Kandy avec une étape culturelle.'],
                ['Kandy → Nuwara Eliya','Paysages de montagne, cascades et plantations de thé.'],
                ['Nuwara Eliya → Ella','Trajet panoramique et arrivée à Ella.'],
                ['Ella → Bentota','Route vers la côte pour une fin de séjour relaxante.'],
                ['Bentota','Journée libre entre plage, spa et activités optionnelles.'],
                ['Bentota → Aéroport','Transfert privé selon l’horaire du vol.'],
            ],
        ],
        [
            'title'=>'Grand Tour du Sri Lanka – 14 jours',
            'slug'=>'grand-tour-sri-lanka-14-jours',
            'days'=>14,'nights'=>13,'from'=>1610,
            'tagline'=>'Deux semaines pour explorer le Sri Lanka en profondeur, du Triangle culturel aux montagnes et à la côte.',
            'content'=>'<p>Notre itinéraire le plus complet : anciennes capitales, safaris, Kandy, hautes terres, Ella, parcs nationaux et plusieurs jours sur la côte.</p>',
            'image'=>'https://images.unsplash.com/photo-1598955890270-d77cdb06d2bb?auto=format&fit=crop&w=1400&q=82',
            'destinations'=>['Negombo','Anuradhapura','Sigiriya','Polonnaruwa','Kandy','Nuwara Eliya','Ella','Yala','Mirissa','Galle','Bentota'],
            'styles'=>['Grand tour','Culture','Safari','Nature','Plage'],
            'pricing'=>[2850,2190,1950,1760,1610,25,320],
            'highlights'=>['Anuradhapura','Polonnaruwa','Sigiriya','Kandy','Train des montagnes','Yala','Galle','Plages du sud'],
            'itinerary'=>[
                ['Arrivée → Negombo','Accueil à l’aéroport et première nuit tranquille.'],
                ['Negombo → Anuradhapura','Route vers l’ancienne capitale.'],
                ['Anuradhapura → Sigiriya','Visite des principaux sites puis départ vers Sigiriya.'],
                ['Polonnaruwa','Excursion vers les ruines de Polonnaruwa.'],
                ['Sigiriya','Forteresse de Sigiriya et temps libre dans la région.'],
                ['Sigiriya → Kandy','Route vers Kandy via Matale.'],
                ['Kandy','Temple de la Dent et découverte de la ville.'],
                ['Kandy → Nuwara Eliya','Route des plantations et découverte du thé.'],
                ['Nuwara Eliya → Ella','Traversée panoramique des hautes terres.'],
                ['Ella','Journée consacrée aux paysages et promenades autour d’Ella.'],
                ['Ella → Yala','Route vers le parc national de Yala.'],
                ['Yala → Mirissa','Safari matinal puis route vers la côte sud.'],
                ['Mirissa → Galle → Bentota','Découverte du fort de Galle et continuation le long de la côte.'],
                ['Bentota → Aéroport','Temps libre puis transfert retour.'],
            ],
        ],
    ];

    foreach($packages as $pkg){
        $existing=get_page_by_path($pkg['slug'],OBJECT,'slt_tour');
        $post_data=[
            'post_type'=>'slt_tour','post_status'=>'publish','post_title'=>$pkg['title'],
            'post_name'=>$pkg['slug'],'post_excerpt'=>$pkg['tagline'],'post_content'=>$pkg['content']
        ];
        if($existing)$post_data['ID']=$existing->ID;
        $tour_id=$existing?wp_update_post($post_data,true):wp_insert_post($post_data,true);
        if(is_wp_error($tour_id)||!$tour_id)continue;

        update_post_meta($tour_id,'duration_days',$pkg['days']);
        update_post_meta($tour_id,'duration_nights',$pkg['nights']);
        update_post_meta($tour_id,'short_tagline',$pkg['tagline']);
        update_post_meta($tour_id,'featured',1);
        update_post_meta($tour_id,'demo_image_url',$pkg['image']);
        update_post_meta($tour_id,'pricing_mode','matrix');
        update_post_meta($tour_id,'price_1',$pkg['pricing'][0]);
        update_post_meta($tour_id,'price_2',$pkg['pricing'][1]);
        update_post_meta($tour_id,'price_3_4',$pkg['pricing'][2]);
        update_post_meta($tour_id,'price_5_6',$pkg['pricing'][3]);
        update_post_meta($tour_id,'price_7_plus',$pkg['pricing'][4]);
        update_post_meta($tour_id,'child_discount_percent',$pkg['pricing'][5]);
        update_post_meta($tour_id,'single_room_supplement',$pkg['pricing'][6]);
        update_post_meta($tour_id,'deposit_percent_override',30);
        update_post_meta($tour_id,'season_start','2026-12-15');
        update_post_meta($tour_id,'season_end','2027-01-15');
        update_post_meta($tour_id,'season_surcharge_percent',12);
        update_post_meta($tour_id,'price_from',$pkg['from']);
        update_post_meta($tour_id,'price_basis','person');
        update_post_meta($tour_id,'_slt_demo_pricing',1);

        update_post_meta($tour_id,'highlights',array_map(fn($text)=>['text'=>$text],$pkg['highlights']));
        $itinerary=[];
        foreach($pkg['itinerary'] as $i=>$day){
            $itinerary[]=[
                'day'=>$i+1,'title'=>$day[0],'description'=>$day[1],
                'image_id'=>0,'hotel_id'=>0,
                'meals'=>$i===0?['dinner']:($i===count($pkg['itinerary'])-1?['breakfast']:['breakfast','dinner'])
            ];
        }
        update_post_meta($tour_id,'itinerary',$itinerary);
        update_post_meta($tour_id,'included',[
            ['item'=>'Transport privé selon le programme confirmé'],
            ['item'=>'Hébergements selon la catégorie confirmée'],
            ['item'=>'Les repas indiqués dans l’itinéraire'],
            ['item'=>'Organisation locale des étapes et activités confirmées'],
        ]);
        update_post_meta($tour_id,'excluded',[
            ['item'=>'Vols internationaux'],
            ['item'=>'Visa et assurance voyage'],
            ['item'=>'Dépenses personnelles et activités non confirmées'],
        ]);

        wp_set_object_terms($tour_id,$term_ids('slt_destination',$pkg['destinations']),'slt_destination');
        wp_set_object_terms($tour_id,$term_ids('slt_travel_style',$pkg['styles']),'slt_travel_style');
    }

    update_option('slt_bootstrap_completed_v10_tour_catalog',1,false);
    flush_rewrite_rules(false);
},100);
