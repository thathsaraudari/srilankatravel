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
