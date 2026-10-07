<?php
/**
 * Plugin Name: SLT Bootstrap
 * Description: One-time setup and demo content for the Sri Lanka Travel site.
 * Version: 0.2.1
 */
if (!defined('ABSPATH')) exit;

add_action('init', function (): void {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin='slt-core/slt-core.php';
    if (!is_plugin_active($plugin)) {
        $result=activate_plugin($plugin);
        if (is_wp_error($result)) update_option('slt_bootstrap_error',$result->get_error_message(),false);
        return;
    }

    $theme=wp_get_theme('slt-travel');
    if ($theme->exists() && get_stylesheet()!=='slt-travel') {
        switch_theme('slt-travel');
        return;
    }

    if (get_option('slt_bootstrap_completed_v2')) return;

    $contact=get_page_by_path('contact');
    $contact_id=$contact?$contact->ID:wp_insert_post([
        'post_type'=>'page','post_status'=>'publish','post_title'=>'Contact','post_name'=>'contact',
        'post_content'=>'<p>Tell us your preferred dates, number of travellers and what you would like to experience in Sri Lanka.</p>[slt_enquiry_form tour_id="0"]'
    ]);

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
            update_post_meta($id,'location',$data['location']);update_post_meta($id,'star_rating',$data['rating']);$hotels[$name]=(int)$id;
        }
    }

    $terms=[];
    foreach(['Habarana','Dambulla','Sigiriya','Kandy','Nuwara Eliya','Beruwala','Balapitiya','Kosgoda'] as $destination){
        $term=term_exists($destination,'slt_destination');
        if(!$term)$term=wp_insert_term($destination,'slt_destination');
        if(!is_wp_error($term))$terms[]=(int)(is_array($term)?$term['term_id']:$term);
    }
    foreach(['Culture','Wildlife','Tea Country','Beach'] as $style){if(!term_exists($style,'slt_travel_style'))wp_insert_term($style,'slt_travel_style');}

    $existing_tours=get_posts(['post_type'=>'slt_tour','post_status'=>'any','posts_per_page'=>1,'fields'=>'ids']);
    if(!$existing_tours){
        $tour_id=wp_insert_post([
            'post_type'=>'slt_tour','post_status'=>'publish','post_title'=>'Sri Lanka Highlights – 7 Days',
            'post_excerpt'=>'A private journey through Sri Lanka’s cultural heart, hill country, wildlife and southwest coast.',
            'post_content'=>'<p>Begin among the ancient landscapes of Sri Lanka’s Cultural Triangle, continue through Kandy and the cool tea country of Nuwara Eliya, then finish beside the Indian Ocean in Beruwala.</p><p>This sample itinerary can be customised around travel dates, preferred hotels and interests.</p>'
        ]);
        if(!is_wp_error($tour_id)){
            update_post_meta($tour_id,'duration_days',7);update_post_meta($tour_id,'duration_nights',6);
            update_post_meta($tour_id,'price_basis','request');update_post_meta($tour_id,'featured',1);
            update_post_meta($tour_id,'short_tagline','Culture, wildlife, tea country and beach in one private Sri Lanka journey.');
            update_post_meta($tour_id,'highlights',[
                ['text'=>'Dambulla Cave Temple'],['text'=>'Sigiriya Rock Fortress'],['text'=>'Kaudulla National Park safari'],
                ['text'=>'Kandyan cultural experience'],['text'=>'Temple of the Sacred Tooth'],['text'=>'Ramboda Falls and tea country'],
                ['text'=>'Beruwala beach'],['text'=>'Madu River and Kosgoda turtle hatchery']
            ]);
            update_post_meta($tour_id,'itinerary',[
                ['day'=>1,'title'=>'Airport → Habarana','description'=>'Arrive in Sri Lanka and travel to Habarana. After check-in and time to relax, visit the historic Dambulla Cave Temple, a UNESCO World Heritage Site. Return to the hotel for dinner.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['dinner']],
                ['day'=>2,'title'=>'Sigiriya & Kaudulla Safari','description'=>'After breakfast, visit Sigiriya Rock Fortress, the royal citadel associated with King Kasyapa. Return to the hotel to relax, then head to Kaudulla National Park for an evening wildlife safari, especially known for elephants.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['breakfast','dinner']],
                ['day'=>3,'title'=>'Habarana → Kandy','description'=>'Travel towards Kandy after breakfast. Stop at a spice garden in Matale, then continue to Kandy. Later, discover traditional crafts and enjoy a Kandyan cultural dance performance.','hotel_id'=>$hotels["Earl's Regency"]??0,'meals'=>['breakfast','dinner']],
                ['day'=>4,'title'=>'Kandy → Nuwara Eliya','description'=>'Visit the Temple of the Sacred Tooth in Kandy, then continue into Sri Lanka’s hill country via Ramboda Falls. In Nuwara Eliya, discover how Ceylon tea is cultivated and produced with a plantation and factory experience.','hotel_id'=>$hotels['Jetwing St Andrews']??0,'meals'=>['breakfast','dinner']],
                ['day'=>5,'title'=>'Nuwara Eliya → Beruwala','description'=>'After breakfast, leave the hill country and travel towards Beruwala on the southwest coast. Check in and enjoy a relaxed evening by the beach.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
                ['day'=>6,'title'=>'Madu River & Kosgoda','description'=>'Explore the Madu River and its mangrove environment in Balapitiya, then visit a turtle hatchery in Kosgoda to learn about turtle conservation. Return to Beruwala for the final night of the journey.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
                ['day'=>7,'title'=>'Beruwala → Airport','description'=>'Depending on the departure flight time, relax at the hotel before checking out and travelling to the airport.','hotel_id'=>0,'meals'=>['breakfast']]
            ]);
            update_post_meta($tour_id,'included',[
                ['item'=>'6 nights at the hotels listed in the itinerary or confirmed alternatives'],
                ['item'=>'Meals shown for each itinerary day']
            ]);
            update_post_meta($tour_id,'excluded',[
                ['item'=>'International flights'],
                ['item'=>'Personal expenses and services not confirmed in the final quotation']
            ]);
            wp_set_object_terms($tour_id,$terms,'slt_destination');
            wp_set_object_terms($tour_id,['Culture','Wildlife','Tea Country','Beach'],'slt_travel_style');
        }
    }

    $locations=get_theme_mod('nav_menu_locations',[]);
    $menu=wp_get_nav_menu_object('Primary');$menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu('Primary');
    if(!is_wp_error($menu_id)){
        $items=wp_get_nav_menu_items($menu_id)?:[];
        if(!$items){
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Home','menu-item-url'=>home_url('/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Tours','menu-item-url'=>get_post_type_archive_link('slt_tour')?:home_url('/tours/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            if(!is_wp_error($contact_id)&&$contact_id)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Contact','menu-item-object-id'=>(int)$contact_id,'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
        }
        $locations['primary']=$menu_id;$locations['footer']=$menu_id;set_theme_mod('nav_menu_locations',$locations);
    }

    if(!get_option('slt_settings')) update_option('slt_settings',['currency'=>'EUR','deposit_percent'=>30,'payment_mode'=>'enquiry']);
    flush_rewrite_rules(false);
    update_option('slt_bootstrap_completed_v2',1,false);
    delete_option('slt_bootstrap_error');
},50);
