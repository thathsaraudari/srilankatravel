<?php
/**
 * Plugin Name: SLT Core
 * Description: Travel package content model, admin editor, settings and enquiry handling for the Sri Lanka Travel site.
 * Version: 0.3.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) exit;

final class SLT_Core {
    private const SETTINGS_KEY = 'slt_settings';

    public static function init(): void {
        add_action('init', [__CLASS__, 'content']);
        add_action('admin_menu', [__CLASS__, 'settings_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('add_meta_boxes', [__CLASS__, 'meta_boxes']);
        add_action('save_post_slt_tour', [__CLASS__, 'save_tour']);
        add_action('save_post_slt_hotel', [__CLASS__, 'save_hotel']);
        add_action('save_post_slt_enquiry', [__CLASS__, 'save_enquiry']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);
        add_filter('manage_slt_tour_posts_columns', [__CLASS__, 'tour_columns']);
        add_action('manage_slt_tour_posts_custom_column', [__CLASS__, 'tour_column'], 10, 2);
        add_filter('manage_slt_enquiry_posts_columns', [__CLASS__, 'enquiry_columns']);
        add_action('manage_slt_enquiry_posts_custom_column', [__CLASS__, 'enquiry_column'], 10, 2);
        add_shortcode('slt_enquiry_form', [__CLASS__, 'form']);
        add_action('admin_post_nopriv_slt_submit_enquiry', [__CLASS__, 'submit']);
        add_action('admin_post_slt_submit_enquiry', [__CLASS__, 'submit']);
    }

    public static function content(): void {
        register_post_type('slt_tour', [
            'labels'=>[
                'name'=>'Tours','singular_name'=>'Tour','add_new_item'=>'Add New Tour','edit_item'=>'Edit Tour',
                'menu_name'=>'Tours','all_items'=>'All Tours'
            ],
            'public'=>true,'show_in_rest'=>true,'has_archive'=>'tours','rewrite'=>['slug'=>'tours'],
            'menu_icon'=>'dashicons-palmtree','supports'=>['title','editor','thumbnail','excerpt','revisions']
        ]);
        register_post_type('slt_hotel', [
            'labels'=>['name'=>'Hotels','singular_name'=>'Hotel','add_new_item'=>'Add New Hotel','edit_item'=>'Edit Hotel'],
            'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-building',
            'supports'=>['title','editor','thumbnail','excerpt','revisions']
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

        $meta = [
            'duration_days'=>'integer','duration_nights'=>'integer','price_from'=>'number','price_basis'=>'string',
            'short_tagline'=>'string','featured'=>'boolean','highlights'=>'array','itinerary'=>'array',
            'included'=>'array','excluded'=>'array','pricing_mode'=>'string','price_1'=>'number','price_2'=>'number','price_3_4'=>'number','price_5_6'=>'number','price_7_plus'=>'number','child_discount_percent'=>'number','single_room_supplement'=>'number','season_start'=>'string','season_end'=>'string','season_surcharge_percent'=>'number','deposit_percent_override'=>'number','location'=>'string','star_rating'=>'string'
        ];
        foreach ($meta as $key=>$type) {
            register_post_meta($key === 'location' || $key === 'star_rating' ? 'slt_hotel' : 'slt_tour', $key, [
                'show_in_rest'=>false,'single'=>true,'type'=>$type === 'array' ? 'string' : $type,
                'auth_callback'=>fn()=>current_user_can('edit_posts')
            ]);
        }
    }

    public static function admin_assets(string $hook): void {
        $screen = get_current_screen();
        if (!$screen) return;
        if (in_array($screen->post_type, ['slt_tour','slt_hotel'], true) || $hook === 'toplevel_page_slt-site-settings') {
            wp_enqueue_style('slt-admin', plugin_dir_url(__FILE__).'assets/admin.css', [], '0.2.0');
            if ($screen->post_type === 'slt_tour') {
                wp_enqueue_media();
                wp_enqueue_script('slt-admin', plugin_dir_url(__FILE__).'assets/admin.js', [], '0.2.0', true);
            }
        }
    }

    public static function settings_menu(): void {
        add_menu_page('Travel Site Settings','Site Settings','manage_options','slt-site-settings',[__CLASS__,'settings_page'],'dashicons-admin-settings',59);
    }

    public static function register_settings(): void {
        register_setting('slt_settings_group', self::SETTINGS_KEY, [
            'sanitize_callback'=>[__CLASS__,'sanitize_settings'],
            'default'=>['currency'=>'EUR','deposit_percent'=>30,'payment_mode'=>'enquiry']
        ]);
    }

    public static function sanitize_settings(array $value): array {
        return [
            'business_email'=>sanitize_email($value['business_email']??''),
            'phone'=>sanitize_text_field($value['phone']??''),
            'whatsapp'=>sanitize_text_field($value['whatsapp']??''),
            'currency'=>in_array(($value['currency']??'EUR'),['EUR','USD','GBP'],true)?$value['currency']:'EUR',
            'deposit_percent'=>min(100,max(0,(int)($value['deposit_percent']??30))),
            'payment_mode'=>in_array(($value['payment_mode']??'enquiry'),['enquiry','woocommerce'],true)?$value['payment_mode']:'enquiry',
            'company_details'=>sanitize_textarea_field($value['company_details']??'')
        ];
    }

    public static function settings_page(): void {
        $s=get_option(self::SETTINGS_KEY,[]); ?>
        <div class="wrap slt-admin-wrap"><h1>Travel Site Settings</h1><p class="description">Business-wide settings used by enquiries and future payments.</p>
        <form method="post" action="options.php"><?php settings_fields('slt_settings_group'); ?>
        <div class="slt-settings-card">
            <?php self::setting_input('business_email','Enquiry email','email',$s); ?>
            <?php self::setting_input('phone','Phone','text',$s); ?>
            <?php self::setting_input('whatsapp','WhatsApp','text',$s); ?>
            <div class="slt-field"><label for="currency">Currency</label><select id="currency" name="slt_settings[currency]">
                <?php foreach(['EUR'=>'EUR (€)','USD'=>'USD ($)','GBP'=>'GBP (£)'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($s['currency']??'EUR',$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?>
            </select></div>
            <div class="slt-field"><label for="deposit">Default deposit %</label><input id="deposit" type="number" min="0" max="100" name="slt_settings[deposit_percent]" value="<?php echo esc_attr((string)($s['deposit_percent']??30)); ?>"></div>
            <div class="slt-field"><label for="payment_mode">Payment mode</label><select id="payment_mode" name="slt_settings[payment_mode]">
                <option value="enquiry" <?php selected($s['payment_mode']??'enquiry','enquiry'); ?>>Enquiry / quote only</option>
                <option value="woocommerce" <?php selected($s['payment_mode']??'enquiry','woocommerce'); ?>>WooCommerce checkout</option>
            </select></div>
            <div class="slt-field slt-field--wide"><label for="company_details">Company details</label><textarea id="company_details" rows="5" name="slt_settings[company_details]"><?php echo esc_textarea($s['company_details']??''); ?></textarea></div>
        </div><?php submit_button('Save settings'); ?></form></div>
    }

    private static function setting_input(string $key,string $label,string $type,array $s): void { ?>
        <div class="slt-field"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><input id="<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($type); ?>" name="slt_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string)($s[$key]??'')); ?>"></div>
    <?php }

    public static function meta_boxes(): void {
        add_meta_box('slt_tour_details','Tour Details',[__CLASS__,'tour_box'],'slt_tour','normal','high');
        add_meta_box('slt_hotel_details','Hotel Details',[__CLASS__,'hotel_box'],'slt_hotel','normal','high');
        add_meta_box('slt_enquiry_details','Enquiry Details',[__CLASS__,'enquiry_box'],'slt_enquiry','normal','high');
    }

    public static function tour_box(WP_Post $post): void {
        wp_nonce_field('slt_save_tour','slt_tour_nonce');
        $days=(int)get_post_meta($post->ID,'duration_days',true);
        $nights=(int)get_post_meta($post->ID,'duration_nights',true);
        $price=get_post_meta($post->ID,'price_from',true);
        $basis=get_post_meta($post->ID,'price_basis',true)?:'person';
        $pricing_mode=get_post_meta($post->ID,'pricing_mode',true)?:'matrix';
        $price_1=(float)get_post_meta($post->ID,'price_1',true);
        $price_2=(float)get_post_meta($post->ID,'price_2',true);
        $price_3_4=(float)get_post_meta($post->ID,'price_3_4',true);
        $price_5_6=(float)get_post_meta($post->ID,'price_5_6',true);
        $price_7_plus=(float)get_post_meta($post->ID,'price_7_plus',true);
        $child_discount=(float)get_post_meta($post->ID,'child_discount_percent',true);
        $single_supplement=(float)get_post_meta($post->ID,'single_room_supplement',true);
        $season_start=(string)get_post_meta($post->ID,'season_start',true);
        $season_end=(string)get_post_meta($post->ID,'season_end',true);
        $season_surcharge=(float)get_post_meta($post->ID,'season_surcharge_percent',true);
        $deposit_override=get_post_meta($post->ID,'deposit_percent_override',true);
        $tagline=get_post_meta($post->ID,'short_tagline',true);
        $featured=(bool)get_post_meta($post->ID,'featured',true);
        $highlights=self::as_array(get_post_meta($post->ID,'highlights',true));
        $itinerary=self::as_array(get_post_meta($post->ID,'itinerary',true));
        $included=self::as_array(get_post_meta($post->ID,'included',true));
        $excluded=self::as_array(get_post_meta($post->ID,'excluded',true));
        $hotels=get_posts(['post_type'=>'slt_hotel','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC','post_status'=>['publish','draft']]);
        ?>
        <div class="slt-admin-grid">
            <div class="slt-field"><label>Days</label><input type="number" min="1" name="slt[duration_days]" value="<?php echo esc_attr((string)$days); ?>"></div>
            <div class="slt-field"><label>Nights</label><input type="number" min="0" name="slt[duration_nights]" value="<?php echo esc_attr((string)$nights); ?>"></div>
            <div class="slt-field"><label>Pricing</label><select name="slt[pricing_mode]"><option value="matrix" <?php selected($pricing_mode,'matrix'); ?>>Automatic price matrix</option><option value="request" <?php selected($pricing_mode,'request'); ?>>Price on request</option></select></div>
            <div class="slt-field"><label>Public price from</label><input type="text" value="<?php echo $pricing_mode==='matrix' && $price ? esc_attr('€'.number_format_i18n((float)$price,0).' / person') : 'Calculated automatically'; ?>" disabled></div>
            <div class="slt-field slt-field--wide"><label>Short tagline</label><input type="text" name="slt[short_tagline]" value="<?php echo esc_attr((string)$tagline); ?>"></div>
            <div class="slt-field slt-field--wide"><label class="slt-inline-check"><input type="checkbox" name="slt[featured]" value="1" <?php checked($featured); ?>> Feature this tour on the homepage</label></div>
            <div class="slt-field slt-field--wide"><label>Highlights <small>one per line</small></label><textarea name="slt[highlights_text]" rows="5"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['text']??''):(string)$x,$highlights))); ?></textarea></div>
        </div>

        <div class="slt-admin-section slt-pricing-section">
            <div class="slt-admin-section__head"><div><h3>Pricing</h3><p>Per-person rates by total party size. The public “price from” is calculated automatically from the lowest active rate.</p></div></div>
            <?php if(get_post_meta($post->ID,'_slt_demo_pricing',true)): ?><div class="notice notice-warning inline"><p><strong>Demo rates:</strong> replace these example prices with the rates supplied by the Sri Lankan partner before launch.</p></div><?php endif; ?>
            <div class="slt-admin-grid slt-pricing-grid">
                <div class="slt-field"><label>1 traveller (€ / adult)</label><input type="number" min="0" step="0.01" name="slt[price_1]" value="<?php echo esc_attr((string)$price_1); ?>"></div>
                <div class="slt-field"><label>2 travellers (€ / adult)</label><input type="number" min="0" step="0.01" name="slt[price_2]" value="<?php echo esc_attr((string)$price_2); ?>"></div>
                <div class="slt-field"><label>3–4 travellers (€ / adult)</label><input type="number" min="0" step="0.01" name="slt[price_3_4]" value="<?php echo esc_attr((string)$price_3_4); ?>"></div>
                <div class="slt-field"><label>5–6 travellers (€ / adult)</label><input type="number" min="0" step="0.01" name="slt[price_5_6]" value="<?php echo esc_attr((string)$price_5_6); ?>"></div>
                <div class="slt-field"><label>7+ travellers (€ / adult)</label><input type="number" min="0" step="0.01" name="slt[price_7_plus]" value="<?php echo esc_attr((string)$price_7_plus); ?>"></div>
                <div class="slt-field"><label>Child discount (%)</label><input type="number" min="0" max="100" step="1" name="slt[child_discount_percent]" value="<?php echo esc_attr((string)$child_discount); ?>"><small>Applied to the adult rate selected for the party size.</small></div>
                <div class="slt-field"><label>Single-room supplement (€)</label><input type="number" min="0" step="0.01" name="slt[single_room_supplement]" value="<?php echo esc_attr((string)$single_supplement); ?>"><small>Added for each single room requested.</small></div>
                <div class="slt-field"><label>Deposit override (%)</label><input type="number" min="0" max="100" step="1" name="slt[deposit_percent_override]" value="<?php echo esc_attr((string)$deposit_override); ?>" placeholder="<?php echo esc_attr((string)self::setting('deposit_percent',30)); ?>"><small>Leave empty to use the site default.</small></div>
                <div class="slt-field"><label>High-season start</label><input type="date" name="slt[season_start]" value="<?php echo esc_attr($season_start); ?>"></div>
                <div class="slt-field"><label>High-season end</label><input type="date" name="slt[season_end]" value="<?php echo esc_attr($season_end); ?>"></div>
                <div class="slt-field"><label>High-season surcharge (%)</label><input type="number" min="0" max="200" step="1" name="slt[season_surcharge_percent]" value="<?php echo esc_attr((string)$season_surcharge); ?>"></div>
            </div>
        </div>

        <div class="slt-admin-section"><div class="slt-admin-section__head"><div><h3>Itinerary</h3><p>Add, remove or reorder the days of this package.</p></div><button type="button" class="button button-secondary" id="slt-add-day">Add day</button></div>
        <div id="slt-itinerary" data-next="<?php echo esc_attr((string)count($itinerary)); ?>">
            <?php foreach($itinerary as $i=>$row) self::day_row((int)$i,is_array($row)?$row:[],$hotels); ?>
        </div></div>

        <div class="slt-admin-grid">
            <div class="slt-field"><label>What's included <small>one per line</small></label><textarea name="slt[included_text]" rows="8"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['item']??''):(string)$x,$included))); ?></textarea></div>
            <div class="slt-field"><label>Not included <small>one per line</small></label><textarea name="slt[excluded_text]" rows="8"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['item']??''):(string)$x,$excluded))); ?></textarea></div>
        </div>
        <script type="text/template" id="slt-day-template"><?php self::day_row('__INDEX__',[], $hotels); ?></script>
        <?php
    }

    private static function day_row($i,array $row,array $hotels): void {
        $meals=$row['meals']??[]; if(!is_array($meals))$meals=[]; ?>
        <div class="slt-day" data-row>
            <div class="slt-day__bar"><strong>Itinerary day</strong><div><button type="button" class="button-link slt-move-up">↑ Up</button> <button type="button" class="button-link slt-move-down">↓ Down</button> <button type="button" class="button-link-delete slt-remove-day">Remove</button></div></div>
            <div class="slt-admin-grid">
                <div class="slt-field"><label>Day</label><input type="number" min="1" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][day]" value="<?php echo esc_attr((string)($row['day']??'')); ?>"></div>
                <div class="slt-field"><label>Title</label><input type="text" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][title]" value="<?php echo esc_attr((string)($row['title']??'')); ?>"></div>
                <div class="slt-field slt-field--wide"><label>Description</label><textarea rows="6" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][description]"><?php echo esc_textarea((string)($row['description']??'')); ?></textarea></div>
                <div class="slt-field slt-field--wide"><label>Photo</label><?php $image_id=(int)($row['image_id']??($row['image']??0)); ?><div class="slt-image-picker"><input type="hidden" class="slt-image-id" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][image_id]" value="<?php echo esc_attr((string)$image_id); ?>"><div class="slt-image-preview"><?php if($image_id)echo wp_get_attachment_image($image_id,'medium'); ?></div><div><button type="button" class="button slt-choose-image">Choose image</button> <button type="button" class="button-link-delete slt-remove-image" <?php echo $image_id?'':'style="display:none"'; ?>>Remove</button></div></div></div>
                <div class="slt-field"><label>Hotel</label><select name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][hotel_id]"><option value="">— None —</option><?php foreach($hotels as $hotel): ?><option value="<?php echo esc_attr((string)$hotel->ID); ?>" <?php selected((int)($row['hotel_id']??0),$hotel->ID); ?>><?php echo esc_html($hotel->post_title); ?></option><?php endforeach; ?></select></div>
                <div class="slt-field"><label>Meals</label><div class="slt-meals"><?php foreach(['breakfast'=>'Breakfast','lunch'=>'Lunch','dinner'=>'Dinner'] as $k=>$v): ?><label><input type="checkbox" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][meals][]" value="<?php echo esc_attr($k); ?>" <?php checked(in_array($k,$meals,true)); ?>> <?php echo esc_html($v); ?></label><?php endforeach; ?></div></div>
            </div>
        </div>
    <?php }

    public static function hotel_box(WP_Post $post): void {
        wp_nonce_field('slt_save_hotel','slt_hotel_nonce');
        $location=get_post_meta($post->ID,'location',true);
        $rating=get_post_meta($post->ID,'star_rating',true); ?>
        <div class="slt-admin-grid"><div class="slt-field"><label>Location</label><input type="text" name="slt_hotel[location]" value="<?php echo esc_attr((string)$location); ?>"></div>
        <div class="slt-field"><label>Star rating</label><select name="slt_hotel[star_rating]"><option value="">—</option><?php foreach(['3'=>'3★','4'=>'4★','5'=>'5★'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($rating,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div></div>
    <?php }

    public static function enquiry_box(WP_Post $post): void {
        wp_nonce_field('slt_save_enquiry','slt_enquiry_nonce');
        $tour_id=(int)get_post_meta($post->ID,'_slt_tour_id',true);
        $fields=['Name'=>'_slt_name','Email'=>'_slt_email','Phone / WhatsApp'=>'_slt_phone','Travel date'=>'_slt_travel_date','Adults'=>'_slt_adults','Children'=>'_slt_children','Single rooms'=>'_slt_single_rooms','Calculated total'=>'_slt_calculated_total','Deposit'=>'_slt_deposit_amount','Message'=>'_slt_message'];
        echo '<table class="widefat striped slt-enquiry-table"><tbody>';
        if($tour_id) echo '<tr><th>Tour</th><td><a href="'.esc_url(get_edit_post_link($tour_id)).'">'.esc_html(get_the_title($tour_id)).'</a></td></tr>';
        foreach($fields as $label=>$key){$value=get_post_meta($post->ID,$key,true);echo '<tr><th>'.esc_html($label).'</th><td>'.nl2br(esc_html((string)$value)).'</td></tr>';}
        echo '</tbody></table>';

        $status=get_post_meta($post->ID,'_slt_status',true)?:'new';
        $quote=get_post_meta($post->ID,'_slt_quote_amount',true);
        $payment=get_post_meta($post->ID,'_slt_payment_link',true);
        $notes=get_post_meta($post->ID,'_slt_internal_notes',true); ?>
        <div class="slt-admin-section"><h3>Sales follow-up</h3><div class="slt-admin-grid">
            <div class="slt-field"><label>Status</label><select name="slt_enquiry[status]">
                <?php foreach(['new'=>'New','contacted'=>'Contacted','quoted'=>'Quoted','booked'=>'Booked','closed'=>'Closed'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($status,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?>
            </select></div>
            <div class="slt-field"><label>Quote total (€)</label><input type="number" min="0" step="0.01" name="slt_enquiry[quote_amount]" value="<?php echo esc_attr((string)$quote); ?>"></div>
            <div class="slt-field slt-field--wide"><label>Payment link</label><input type="url" name="slt_enquiry[payment_link]" value="<?php echo esc_attr((string)$payment); ?>" placeholder="https://..."></div>
            <div class="slt-field slt-field--wide"><label>Internal notes</label><textarea rows="5" name="slt_enquiry[internal_notes]"><?php echo esc_textarea((string)$notes); ?></textarea></div>
        </div></div>
    <?php }

    public static function save_tour(int $post_id): void {
        if (!isset($_POST['slt_tour_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_tour_nonce'])),'slt_save_tour') || !current_user_can('edit_post',$post_id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)) return;
        $s=isset($_POST['slt'])&&is_array($_POST['slt'])?wp_unslash($_POST['slt']):[];
        update_post_meta($post_id,'duration_days',max(0,(int)($s['duration_days']??0)));
        update_post_meta($post_id,'duration_nights',max(0,(int)($s['duration_nights']??0)));
        $pricing_mode=in_array(($s['pricing_mode']??'matrix'),['matrix','request'],true)?$s['pricing_mode']:'matrix';
        update_post_meta($post_id,'pricing_mode',$pricing_mode);
        $rate_keys=['price_1','price_2','price_3_4','price_5_6','price_7_plus'];
        $active_rates=[];
        foreach($rate_keys as $key){
            $value=max(0,(float)($s[$key]??0));
            update_post_meta($post_id,$key,$value);
            if($value>0)$active_rates[]=$value;
        }
        update_post_meta($post_id,'child_discount_percent',min(100,max(0,(float)($s['child_discount_percent']??0))));
        update_post_meta($post_id,'single_room_supplement',max(0,(float)($s['single_room_supplement']??0)));
        update_post_meta($post_id,'season_start',sanitize_text_field($s['season_start']??''));
        update_post_meta($post_id,'season_end',sanitize_text_field($s['season_end']??''));
        update_post_meta($post_id,'season_surcharge_percent',min(200,max(0,(float)($s['season_surcharge_percent']??0))));
        $deposit_raw=trim((string)($s['deposit_percent_override']??''));
        if($deposit_raw==='')delete_post_meta($post_id,'deposit_percent_override');else update_post_meta($post_id,'deposit_percent_override',min(100,max(0,(float)$deposit_raw)));
        if($pricing_mode==='matrix' && $active_rates){
            update_post_meta($post_id,'price_from',min($active_rates));
            update_post_meta($post_id,'price_basis','person');
        } else {
            update_post_meta($post_id,'price_from',0);
            update_post_meta($post_id,'price_basis','request');
        }
        delete_post_meta($post_id,'_slt_demo_pricing');
        update_post_meta($post_id,'short_tagline',sanitize_text_field($s['short_tagline']??''));
        update_post_meta($post_id,'featured',!empty($s['featured'])?1:0);
        update_post_meta($post_id,'highlights',self::line_rows($s['highlights_text']??'','text'));
        update_post_meta($post_id,'included',self::line_rows($s['included_text']??'','item'));
        update_post_meta($post_id,'excluded',self::line_rows($s['excluded_text']??'','item'));
        $rows=[];
        foreach(($s['itinerary']??[]) as $row){
            if(!is_array($row))continue;
            $title=sanitize_text_field($row['title']??'');$description=wp_kses_post($row['description']??'');
            if($title===''&&trim(wp_strip_all_tags($description))==='')continue;
            $meals=array_values(array_intersect(['breakfast','lunch','dinner'],array_map('sanitize_key',(array)($row['meals']??[]))));
            $rows[]=['day'=>max(1,(int)($row['day']??count($rows)+1)),'title'=>$title,'description'=>$description,'image_id'=>absint($row['image_id']??0),'hotel_id'=>absint($row['hotel_id']??0),'meals'=>$meals];
        }
        update_post_meta($post_id,'itinerary',$rows);
    }

    public static function save_hotel(int $post_id): void {
        if (!isset($_POST['slt_hotel_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_hotel_nonce'])),'slt_save_hotel') || !current_user_can('edit_post',$post_id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)) return;
        $s=isset($_POST['slt_hotel'])&&is_array($_POST['slt_hotel'])?wp_unslash($_POST['slt_hotel']):[];
        update_post_meta($post_id,'location',sanitize_text_field($s['location']??''));
        $rating=in_array(($s['star_rating']??''),['3','4','5'],true)?$s['star_rating']:'';
        update_post_meta($post_id,'star_rating',$rating);
    }

    private static function line_rows(string $text,string $key): array {
        $lines=preg_split('/\r\n|\r|\n/',sanitize_textarea_field($text))?:[];
        return array_values(array_map(fn($line)=>[$key=>trim($line)],array_filter($lines,fn($line)=>trim($line)!=='')));
    }
    private static function as_array($value): array { return is_array($value)?$value:[]; }

    public static function save_enquiry(int $post_id): void {
        if (!isset($_POST['slt_enquiry_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_enquiry_nonce'])),'slt_save_enquiry') || !current_user_can('edit_post',$post_id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE)) return;
        $s=isset($_POST['slt_enquiry'])&&is_array($_POST['slt_enquiry'])?wp_unslash($_POST['slt_enquiry']):[];
        $status=in_array(($s['status']??'new'),['new','contacted','quoted','booked','closed'],true)?$s['status']:'new';
        update_post_meta($post_id,'_slt_status',$status);
        update_post_meta($post_id,'_slt_quote_amount',max(0,(float)($s['quote_amount']??0)));
        update_post_meta($post_id,'_slt_payment_link',esc_url_raw($s['payment_link']??''));
        update_post_meta($post_id,'_slt_internal_notes',sanitize_textarea_field($s['internal_notes']??''));
    }

    public static function tour_columns(array $columns): array {
        return ['cb'=>$columns['cb']??'','title'=>'Tour','slt_duration'=>'Duration','slt_price'=>'Price','slt_featured'=>'Featured','date'=>'Date'];
    }
    public static function tour_column(string $column,int $post_id): void {
        if($column==='slt_duration'){ $d=(int)get_post_meta($post_id,'duration_days',true);$n=(int)get_post_meta($post_id,'duration_nights',true);echo esc_html($d.' days / '.$n.' nights'); }
        if($column==='slt_price'){ $basis=get_post_meta($post_id,'price_basis',true);$price=get_post_meta($post_id,'price_from',true);echo $basis==='request'?'On request':esc_html($price?('€'.number_format_i18n((float)$price,0)):'—'); }
        if($column==='slt_featured')echo get_post_meta($post_id,'featured',true)?'★':'—';
    }

    public static function enquiry_columns(array $columns): array {
        return ['cb'=>$columns['cb']??'','title'=>'Enquiry','slt_status'=>'Status','slt_email'=>'Email','slt_tour'=>'Tour','slt_date'=>'Travel date','date'=>'Received'];
    }
    public static function enquiry_column(string $column,int $post_id): void {
        if($column==='slt_status'){ $status=get_post_meta($post_id,'_slt_status',true)?:'new';$labels=['new'=>'New','contacted'=>'Contacted','quoted'=>'Quoted','booked'=>'Booked','closed'=>'Closed'];echo esc_html($labels[$status]??ucfirst($status)); }
        if($column==='slt_email')echo esc_html((string)get_post_meta($post_id,'_slt_email',true));
        if($column==='slt_tour'){ $id=(int)get_post_meta($post_id,'_slt_tour_id',true); echo $id?esc_html(get_the_title($id)):'Tailor-made'; }
        if($column==='slt_date')echo esc_html((string)get_post_meta($post_id,'_slt_travel_date',true));
    }

    public static function setting(string $key,$default=null) {
        $s=get_option(self::SETTINGS_KEY,[]);
        return array_key_exists($key,$s)?$s[$key]:$default;
    }

    public static function pricing_data(int $tour_id): array {
        $deposit=get_post_meta($tour_id,'deposit_percent_override',true);
        if($deposit==='')$deposit=self::setting('deposit_percent',30);
        return [
            'mode'=>(string)(get_post_meta($tour_id,'pricing_mode',true)?:'request'),
            'price_1'=>(float)get_post_meta($tour_id,'price_1',true),
            'price_2'=>(float)get_post_meta($tour_id,'price_2',true),
            'price_3_4'=>(float)get_post_meta($tour_id,'price_3_4',true),
            'price_5_6'=>(float)get_post_meta($tour_id,'price_5_6',true),
            'price_7_plus'=>(float)get_post_meta($tour_id,'price_7_plus',true),
            'child_discount_percent'=>(float)get_post_meta($tour_id,'child_discount_percent',true),
            'single_room_supplement'=>(float)get_post_meta($tour_id,'single_room_supplement',true),
            'season_start'=>(string)get_post_meta($tour_id,'season_start',true),
            'season_end'=>(string)get_post_meta($tour_id,'season_end',true),
            'season_surcharge_percent'=>(float)get_post_meta($tour_id,'season_surcharge_percent',true),
            'deposit_percent'=>(float)$deposit,
            'currency'=>(string)self::setting('currency','EUR'),
        ];
    }

    public static function calculate_price(int $tour_id,int $adults,int $children,int $single_rooms,string $travel_date=''): array {
        $p=self::pricing_data($tour_id);
        $adults=max(1,$adults);$children=max(0,$children);$single_rooms=max(0,$single_rooms);
        $party=$adults+$children;
        if($p['mode']!=='matrix')return ['available'=>false,'currency'=>$p['currency'],'total'=>0,'deposit'=>0];
        if($party<=1)$adult_rate=$p['price_1'];
        elseif($party===2)$adult_rate=$p['price_2'];
        elseif($party<=4)$adult_rate=$p['price_3_4'];
        elseif($party<=6)$adult_rate=$p['price_5_6'];
        else $adult_rate=$p['price_7_plus'];
        if($adult_rate<=0)return ['available'=>false,'currency'=>$p['currency'],'total'=>0,'deposit'=>0];
        $child_rate=$adult_rate*(1-($p['child_discount_percent']/100));
        $base=($adult_rate*$adults)+($child_rate*$children)+($p['single_room_supplement']*$single_rooms);
        $seasonal=false;$season_amount=0.0;
        if($travel_date!=='' && $p['season_start']!=='' && $p['season_end']!=='' && $travel_date>=$p['season_start'] && $travel_date<=$p['season_end']){
            $seasonal=true;$season_amount=$base*($p['season_surcharge_percent']/100);
        }
        $total=round($base+$season_amount,2);
        $deposit=round($total*($p['deposit_percent']/100),2);
        return [
            'available'=>true,'currency'=>$p['currency'],'party_size'=>$party,'adult_rate'=>round($adult_rate,2),
            'child_rate'=>round($child_rate,2),'base'=>round($base,2),'seasonal'=>$seasonal,
            'season_surcharge'=>round($season_amount,2),'total'=>$total,'deposit'=>$deposit,
            'deposit_percent'=>$p['deposit_percent']
        ];
    }

    public static function form($atts=[]): string {
        $atts=shortcode_atts(['tour_id'=>get_the_ID()],$atts,'slt_enquiry_form');
        $tour_id=absint($atts['tour_id']);
        ob_start(); ?>
        <?php if(isset($_GET['enquiry'])&&$_GET['enquiry']==='success'): ?><div class="slt-notice">Merci — votre demande de voyage a bien été envoyée. Nous vous contacterons rapidement.</div><?php endif; ?>
        <form class="slt-enquiry-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="slt_submit_enquiry"><input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
            <?php wp_nonce_field('slt_submit_enquiry','slt_nonce'); ?>
            <div class="slt-form-grid">
                <label>Nom complet<input name="name" required autocomplete="name"></label>
                <label>Email<input type="email" name="email" required autocomplete="email"></label>
                <label>Téléphone / WhatsApp<input name="phone" autocomplete="tel"></label>
                <label>Date de voyage<input type="date" name="travel_date"></label>
                <label>Adultes<input type="number" name="adults" min="1" value="2"></label>
                <label>Enfants<input type="number" name="children" min="0" value="0"></label>
            </div>
            <label>Parlez-nous de votre voyage<textarea name="message" rows="5" placeholder="Centres d’intérêt, catégorie d’hôtel, demandes particulières…"></textarea></label>
            <label class="slt-checkbox"><input type="checkbox" name="privacy" value="1" required> J’accepte que mes informations soient utilisées pour répondre à ma demande.</label>
            <button class="slt-button" type="submit">Demander mon devis</button>
        </form>
        <?php return (string)ob_get_clean();
    }

    public static function submit(): void {
        if (!isset($_POST['slt_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_nonce'])),'slt_submit_enquiry')) wp_die('Invalid request.',403);
        $name=sanitize_text_field(wp_unslash($_POST['name']??''));$email=sanitize_email(wp_unslash($_POST['email']??''));
        if (!$name || !is_email($email) || empty($_POST['privacy'])) wp_die('Please complete the required fields.',400);
        $tour_id=absint($_POST['tour_id']??0);$tour=$tour_id?get_the_title($tour_id):'Tailor-made trip';
        $id=wp_insert_post(['post_type'=>'slt_enquiry','post_status'=>'publish','post_title'=>$name.' — '.$tour]);
        if (!is_wp_error($id)) {
            foreach(['phone','travel_date','adults','children','single_rooms','message'] as $key){$value=$_POST[$key]??'';$value=$key==='message'?sanitize_textarea_field(wp_unslash($value)):sanitize_text_field(wp_unslash($value));update_post_meta($id,'_slt_'.$key,$value);}
            update_post_meta($id,'_slt_name',$name);update_post_meta($id,'_slt_email',$email);update_post_meta($id,'_slt_tour_id',$tour_id);update_post_meta($id,'_slt_status','new');
            if($tour_id){
                $pricing=self::calculate_price($tour_id,(int)($_POST['adults']??1),(int)($_POST['children']??0),(int)($_POST['single_rooms']??0),sanitize_text_field(wp_unslash($_POST['travel_date']??'')));
                if(!empty($pricing['available'])){
                    update_post_meta($id,'_slt_calculated_total',$pricing['currency'].' '.number_format((float)$pricing['total'],2,'.',''));
                    update_post_meta($id,'_slt_deposit_amount',$pricing['currency'].' '.number_format((float)$pricing['deposit'],2,'.',''));
                    update_post_meta($id,'_slt_pricing_snapshot',wp_json_encode($pricing));
                }
            }
            $to=self::setting('business_email',get_option('admin_email'))?:get_option('admin_email');
            wp_mail($to,'Nouvelle demande de voyage : '.$tour,"Name: $name\nEmail: $email\nTour: $tour\nTravel date: ".sanitize_text_field(wp_unslash($_POST['travel_date']??''))."\n\n".sanitize_textarea_field(wp_unslash($_POST['message']??'')),['Reply-To: '.$name.' <'.$email.'>']);
        }
        wp_safe_redirect(add_query_arg('enquiry','success',wp_get_referer()?:home_url('/')));exit;
    }
}
SLT_Core::init();
register_activation_hook(__FILE__,function(){SLT_Core::content();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,'flush_rewrite_rules');
