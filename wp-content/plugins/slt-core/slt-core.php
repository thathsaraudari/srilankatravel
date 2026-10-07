<?php
/**
 * Plugin Name: SLT Core
 * Description: Travel package content model, settings and enquiry handling for the Sri Lanka Travel site.
 * Version: 0.1.1
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) exit;

final class SLT_Core {
    public static function init(): void {
        add_action('init', [__CLASS__, 'content']);
        add_action('acf/init', [__CLASS__, 'acf']);
        add_shortcode('slt_enquiry_form', [__CLASS__, 'form']);
        add_action('admin_post_nopriv_slt_submit_enquiry', [__CLASS__, 'submit']);
        add_action('admin_post_slt_submit_enquiry', [__CLASS__, 'submit']);
    }

    public static function content(): void {
        register_post_type('slt_tour', [
            'labels'=>['name'=>'Tours','singular_name'=>'Tour','add_new_item'=>'Add New Tour','edit_item'=>'Edit Tour'],
            'public'=>true,'show_in_rest'=>true,'has_archive'=>'tours','rewrite'=>['slug'=>'tours'],
            'menu_icon'=>'dashicons-palmtree','supports'=>['title','editor','thumbnail','excerpt','revisions']
        ]);
        register_post_type('slt_hotel', [
            'labels'=>['name'=>'Hotels','singular_name'=>'Hotel'],'public'=>true,'show_in_rest'=>true,
            'menu_icon'=>'dashicons-building','supports'=>['title','editor','thumbnail','excerpt','revisions']
        ]);
        register_post_type('slt_enquiry', [
            'labels'=>['name'=>'Enquiries','singular_name'=>'Enquiry'],'public'=>false,'show_ui'=>true,
            'menu_icon'=>'dashicons-email-alt2','supports'=>['title']
        ]);
        foreach ([
            'slt_destination'=>['Destinations','Destination','destination'],
            'slt_travel_style'=>['Travel Styles','Travel Style','travel-style']
        ] as $tax=>$cfg) {
            register_taxonomy($tax, ['slt_tour'], [
                'labels'=>['name'=>$cfg[0],'singular_name'=>$cfg[1]],'public'=>true,'hierarchical'=>true,
                'show_in_rest'=>true,'rewrite'=>['slug'=>$cfg[2]]
            ]);
        }
    }

    public static function acf(): void {
        if (!function_exists('acf_add_local_field_group')) return;

        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title'=>'Travel Site Settings','menu_title'=>'Site Settings','menu_slug'=>'slt-site-settings',
                'capability'=>'manage_options','redirect'=>false,'icon_url'=>'dashicons-admin-settings'
            ]);
            acf_add_local_field_group([
                'key'=>'group_slt_settings','title'=>'Business & Booking Settings',
                'fields'=>[
                    ['key'=>'slt_email','label'=>'Enquiry email','name'=>'business_email','type'=>'email'],
                    ['key'=>'slt_phone','label'=>'Phone','name'=>'phone','type'=>'text'],
                    ['key'=>'slt_whatsapp','label'=>'WhatsApp','name'=>'whatsapp','type'=>'text'],
                    ['key'=>'slt_currency','label'=>'Currency','name'=>'currency','type'=>'select','choices'=>['EUR'=>'EUR (€)','USD'=>'USD ($)','GBP'=>'GBP (£)'],'default_value'=>'EUR'],
                    ['key'=>'slt_deposit','label'=>'Default deposit','name'=>'deposit_percent','type'=>'number','append'=>'%','min'=>0,'max'=>100,'default_value'=>30],
                    ['key'=>'slt_payment','label'=>'Payment mode','name'=>'payment_mode','type'=>'select','choices'=>['enquiry'=>'Enquiry / quote only','woocommerce'=>'WooCommerce checkout'],'default_value'=>'enquiry'],
                    ['key'=>'slt_company','label'=>'Company details','name'=>'company_details','type'=>'textarea']
                ],
                'location'=>[[['param'=>'options_page','operator'=>'==','value'=>'slt-site-settings']]]
            ]);
        }

        acf_add_local_field_group([
            'key'=>'group_slt_tour','title'=>'Tour Details','position'=>'acf_after_title','style'=>'seamless',
            'fields'=>[
                ['key'=>'tour_days','label'=>'Days','name'=>'duration_days','type'=>'number','required'=>1,'min'=>1],
                ['key'=>'tour_nights','label'=>'Nights','name'=>'duration_nights','type'=>'number','min'=>0],
                ['key'=>'tour_price','label'=>'Price from','name'=>'price_from','type'=>'number','min'=>0,'step'=>'0.01'],
                ['key'=>'tour_basis','label'=>'Price basis','name'=>'price_basis','type'=>'select','choices'=>['person'=>'Per person','couple'=>'Per couple','trip'=>'Per trip','request'=>'Price on request'],'default_value'=>'person'],
                ['key'=>'tour_tagline','label'=>'Short tagline','name'=>'short_tagline','type'=>'text'],
                ['key'=>'tour_featured','label'=>'Featured tour','name'=>'featured','type'=>'true_false','ui'=>1],
                ['key'=>'tour_highlights','label'=>'Highlights','name'=>'highlights','type'=>'repeater','button_label'=>'Add highlight','sub_fields'=>[
                    ['key'=>'tour_highlight_text','label'=>'Highlight','name'=>'text','type'=>'text','required'=>1]
                ]],
                ['key'=>'tour_itinerary','label'=>'Itinerary','name'=>'itinerary','type'=>'repeater','button_label'=>'Add day','layout'=>'block','sub_fields'=>[
                    ['key'=>'tour_day','label'=>'Day','name'=>'day','type'=>'number','required'=>1,'min'=>1],
                    ['key'=>'tour_day_title','label'=>'Title','name'=>'title','type'=>'text','required'=>1],
                    ['key'=>'tour_day_description','label'=>'Description','name'=>'description','type'=>'wysiwyg','tabs'=>'visual','toolbar'=>'basic','media_upload'=>0],
                    ['key'=>'tour_day_hotel','label'=>'Hotel','name'=>'hotel','type'=>'post_object','post_type'=>['slt_hotel'],'return_format'=>'object','allow_null'=>1],
                    ['key'=>'tour_day_meals','label'=>'Meals','name'=>'meals','type'=>'checkbox','choices'=>['breakfast'=>'Breakfast','lunch'=>'Lunch','dinner'=>'Dinner']],
                    ['key'=>'tour_day_image','label'=>'Image','name'=>'image','type'=>'image','return_format'=>'id']
                ]],
                ['key'=>'tour_included','label'=>'Included','name'=>'included','type'=>'repeater','button_label'=>'Add item','sub_fields'=>[
                    ['key'=>'tour_included_item','label'=>'Item','name'=>'item','type'=>'text']
                ]],
                ['key'=>'tour_excluded','label'=>'Not included','name'=>'excluded','type'=>'repeater','button_label'=>'Add item','sub_fields'=>[
                    ['key'=>'tour_excluded_item','label'=>'Item','name'=>'item','type'=>'text']
                ]],
                ['key'=>'tour_gallery','label'=>'Gallery','name'=>'gallery','type'=>'gallery','return_format'=>'id']
            ],
            'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'slt_tour']]]
        ]);

        acf_add_local_field_group([
            'key'=>'group_slt_hotel','title'=>'Hotel Details',
            'fields'=>[
                ['key'=>'hotel_location','label'=>'Location','name'=>'location','type'=>'text'],
                ['key'=>'hotel_rating','label'=>'Star rating','name'=>'star_rating','type'=>'select','choices'=>['3'=>'3★','4'=>'4★','5'=>'5★'],'allow_null'=>1],
                ['key'=>'hotel_gallery','label'=>'Gallery','name'=>'hotel_gallery','type'=>'gallery','return_format'=>'id']
            ],
            'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'slt_hotel']]]
        ]);
    }

    public static function form($atts=[]): string {
        $atts=shortcode_atts(['tour_id'=>get_the_ID()],$atts,'slt_enquiry_form');
        $tour_id=absint($atts['tour_id']);
        ob_start(); ?>
        <form class="slt-enquiry-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="slt_submit_enquiry">
            <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
            <?php wp_nonce_field('slt_submit_enquiry','slt_nonce'); ?>
            <div class="slt-form-grid">
                <label>Full name<input name="name" required></label>
                <label>Email<input type="email" name="email" required></label>
                <label>Phone / WhatsApp<input name="phone"></label>
                <label>Travel date<input type="date" name="travel_date"></label>
                <label>Adults<input type="number" name="adults" min="1" value="2"></label>
                <label>Children<input type="number" name="children" min="0" value="0"></label>
            </div>
            <label>Tell us about your trip<textarea name="message" rows="5"></textarea></label>
            <label class="slt-checkbox"><input type="checkbox" name="privacy" value="1" required> I agree that my information may be used to answer this enquiry.</label>
            <button class="slt-button" type="submit">Request my quote</button>
        </form>
        <?php return (string)ob_get_clean();
    }

    public static function submit(): void {
        if (!isset($_POST['slt_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_nonce'])),'slt_submit_enquiry')) wp_die('Invalid request.',403);
        $name=sanitize_text_field(wp_unslash($_POST['name']??''));
        $email=sanitize_email(wp_unslash($_POST['email']??''));
        if (!$name || !is_email($email) || empty($_POST['privacy'])) wp_die('Please complete the required fields.',400);
        $tour_id=absint($_POST['tour_id']??0);
        $tour=$tour_id ? get_the_title($tour_id) : 'Tailor-made trip';
        $id=wp_insert_post(['post_type'=>'slt_enquiry','post_status'=>'publish','post_title'=>$name.' — '.$tour]);
        if (!is_wp_error($id)) {
            foreach (['phone','travel_date','adults','children','message'] as $key) {
                $value=$_POST[$key]??'';
                $value=$key==='message' ? sanitize_textarea_field(wp_unslash($value)) : sanitize_text_field(wp_unslash($value));
                update_post_meta($id,'_slt_'.$key,$value);
            }
            update_post_meta($id,'_slt_name',$name); update_post_meta($id,'_slt_email',$email); update_post_meta($id,'_slt_tour_id',$tour_id);
            $to=function_exists('get_field') ? (get_field('business_email','option')?:get_option('admin_email')) : get_option('admin_email');
            wp_mail($to,'New trip enquiry: '.$tour,"Name: $name\nEmail: $email\nTour: $tour",['Reply-To: '.$name.' <'.$email.'>']);
        }
        wp_safe_redirect(add_query_arg('enquiry','success',wp_get_referer()?:home_url('/'))); exit;
    }
}
SLT_Core::init();
register_activation_hook(__FILE__, function(){SLT_Core::content();flush_rewrite_rules();});
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
