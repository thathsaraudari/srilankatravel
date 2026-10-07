<?php
/**
 * Plugin Name: SLT Core
 * Description: Travel package CMS, bookings and Mollie-ready European payments for the Sri Lanka Travel site.
 * Version: 0.3.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) exit;

final class SLT_Core {
    private const SETTINGS_KEY='slt_settings';

    public static function init(): void {
        add_action('init',[__CLASS__,'content']);
        add_action('rest_api_init',[__CLASS__,'rest_routes']);
        add_action('admin_menu',[__CLASS__,'settings_menu']);
        add_action('admin_init',[__CLASS__,'register_settings']);
        add_action('add_meta_boxes',[__CLASS__,'meta_boxes']);
        add_action('save_post_slt_tour',[__CLASS__,'save_tour']);
        add_action('save_post_slt_hotel',[__CLASS__,'save_hotel']);
        add_action('save_post_slt_booking',[__CLASS__,'save_booking']);
        add_action('admin_enqueue_scripts',[__CLASS__,'admin_assets']);
        add_filter('manage_slt_tour_posts_columns',[__CLASS__,'tour_columns']);
        add_action('manage_slt_tour_posts_custom_column',[__CLASS__,'tour_column'],10,2);
        add_filter('manage_slt_booking_posts_columns',[__CLASS__,'booking_columns']);
        add_action('manage_slt_booking_posts_custom_column',[__CLASS__,'booking_column'],10,2);

        add_shortcode('slt_booking_form',[__CLASS__,'booking_form']);
        add_shortcode('slt_booking_result',[__CLASS__,'booking_result']);
        add_shortcode('slt_contact_details',[__CLASS__,'contact_details']);

        add_action('admin_post_nopriv_slt_submit_booking',[__CLASS__,'submit_booking']);
        add_action('admin_post_slt_submit_booking',[__CLASS__,'submit_booking']);
    }

    public static function content(): void {
        register_post_type('slt_tour',[
            'labels'=>['name'=>'Tours','singular_name'=>'Tour','add_new_item'=>'Add New Tour','edit_item'=>'Edit Tour','menu_name'=>'Tours','all_items'=>'All Tours'],
            'public'=>true,'show_in_rest'=>true,'has_archive'=>'tours','rewrite'=>['slug'=>'tours'],
            'menu_icon'=>'dashicons-palmtree','supports'=>['title','editor','thumbnail','excerpt','revisions']
        ]);
        register_post_type('slt_hotel',[
            'labels'=>['name'=>'Hotels','singular_name'=>'Hotel','add_new_item'=>'Add New Hotel','edit_item'=>'Edit Hotel'],
            'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-building',
            'supports'=>['title','editor','thumbnail','excerpt','revisions']
        ]);
        register_post_type('slt_booking',[
            'labels'=>['name'=>'Bookings','singular_name'=>'Booking','menu_name'=>'Bookings','all_items'=>'All Bookings'],
            'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-tickets-alt','supports'=>['title']
        ]);
        foreach([
            'slt_destination'=>['Destinations','Destination','destination'],
            'slt_travel_style'=>['Travel Styles','Travel Style','travel-style']
        ] as $tax=>$cfg){
            register_taxonomy($tax,['slt_tour'],[
                'labels'=>['name'=>$cfg[0],'singular_name'=>$cfg[1]],'public'=>true,'hierarchical'=>true,
                'show_in_rest'=>true,'rewrite'=>['slug'=>$cfg[2]]
            ]);
        }
    }

    public static function rest_routes(): void {
        register_rest_route('slt/v1','/mollie-webhook',[
            'methods'=>'POST',
            'callback'=>[__CLASS__,'mollie_webhook'],
            'permission_callback'=>'__return_true'
        ]);
    }

    public static function admin_assets(string $hook): void {
        $screen=get_current_screen();
        if(!$screen)return;
        if(in_array($screen->post_type,['slt_tour','slt_hotel','slt_booking'],true)||$hook==='toplevel_page_slt-site-settings'){
            wp_enqueue_style('slt-admin',plugin_dir_url(__FILE__).'assets/admin.css',[],'0.3.0');
            if($screen->post_type==='slt_tour'){
                wp_enqueue_media();
                wp_enqueue_script('slt-admin',plugin_dir_url(__FILE__).'assets/admin.js',[],'0.3.0',true);
            }
        }
    }

    public static function settings_menu(): void {
        add_menu_page('Travel Site Settings','Site Settings','manage_options','slt-site-settings',[__CLASS__,'settings_page'],'dashicons-admin-settings',59);
    }

    public static function register_settings(): void {
        register_setting('slt_settings_group',self::SETTINGS_KEY,[
            'sanitize_callback'=>[__CLASS__,'sanitize_settings'],
            'default'=>['currency'=>'EUR','deposit_percent'=>30,'payment_mode'=>'demo']
        ]);
    }

    public static function sanitize_settings(array $value): array {
        $old=get_option(self::SETTINGS_KEY,[]);
        $key=trim((string)($value['mollie_api_key']??''));
        if($key==='••••••••')$key=(string)($old['mollie_api_key']??'');
        return [
            'business_email'=>sanitize_email($value['business_email']??''),
            'phone'=>sanitize_text_field($value['phone']??''),
            'whatsapp'=>sanitize_text_field($value['whatsapp']??''),
            'currency'=>'EUR',
            'deposit_percent'=>min(100,max(1,(int)($value['deposit_percent']??30))),
            'payment_mode'=>in_array(($value['payment_mode']??'demo'),['demo','mollie'],true)?$value['payment_mode']:'demo',
            'mollie_api_key'=>sanitize_text_field($key),
            'company_details'=>sanitize_textarea_field($value['company_details']??'')
        ];
    }

    public static function settings_page(): void {
        $s=get_option(self::SETTINGS_KEY,[]); ?>
        <div class="wrap slt-admin-wrap">
            <h1>Travel Site Settings</h1>
            <p class="description">Configure contact details and the European payment flow. Keep Demo mode enabled until a Mollie account is ready.</p>
            <form method="post" action="options.php"><?php settings_fields('slt_settings_group'); ?>
                <div class="slt-settings-card">
                    <?php self::setting_input('business_email','Booking email','email',$s); ?>
                    <?php self::setting_input('phone','Phone','text',$s); ?>
                    <?php self::setting_input('whatsapp','WhatsApp','text',$s); ?>
                    <div class="slt-field"><label>Currency</label><input value="EUR (€)" disabled></div>
                    <div class="slt-field"><label for="deposit">Default deposit %</label><input id="deposit" type="number" min="1" max="100" name="slt_settings[deposit_percent]" value="<?php echo esc_attr((string)($s['deposit_percent']??30)); ?>"></div>
                    <div class="slt-field"><label for="payment_mode">Payment mode</label><select id="payment_mode" name="slt_settings[payment_mode]">
                        <option value="demo" <?php selected($s['payment_mode']??'demo','demo'); ?>>Demo – no real charge</option>
                        <option value="mollie" <?php selected($s['payment_mode']??'demo','mollie'); ?>>Mollie – live/test API</option>
                    </select></div>
                    <div class="slt-field slt-field--wide"><label for="mollie_api_key">Mollie API key</label><input id="mollie_api_key" type="password" autocomplete="off" name="slt_settings[mollie_api_key]" value="<?php echo !empty($s['mollie_api_key'])?'••••••••':''; ?>" placeholder="test_xxx or live_xxx"><small>Stored in WordPress settings, never in GitHub. Use a test key while the site is a demo.</small></div>
                    <div class="slt-field slt-field--wide"><label>Checkout priority</label>
                        <div class="slt-payment-priority"><strong>Wero</strong> → Carte bancaire (CB / Visa / Mastercard) → Apple Pay → iDEAL → PayPal / SEPA</div>
                    </div>
                    <div class="slt-field slt-field--wide"><label for="company_details">Company details</label><textarea id="company_details" rows="5" name="slt_settings[company_details]"><?php echo esc_textarea($s['company_details']??''); ?></textarea></div>
                </div>
                <?php submit_button('Save settings'); ?>
            </form>
        </div>
    <?php }

    private static function setting_input(string $key,string $label,string $type,array $s): void { ?>
        <div class="slt-field"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><input id="<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($type); ?>" name="slt_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string)($s[$key]??'')); ?>"></div>
    <?php }

    public static function meta_boxes(): void {
        add_meta_box('slt_tour_details','Tour Details',[__CLASS__,'tour_box'],'slt_tour','normal','high');
        add_meta_box('slt_hotel_details','Hotel Details',[__CLASS__,'hotel_box'],'slt_hotel','normal','high');
        add_meta_box('slt_booking_details','Booking Details',[__CLASS__,'booking_box'],'slt_booking','normal','high');
    }

    public static function tour_box(WP_Post $post): void {
        wp_nonce_field('slt_save_tour','slt_tour_nonce');
        $days=(int)get_post_meta($post->ID,'duration_days',true);
        $nights=(int)get_post_meta($post->ID,'duration_nights',true);
        $price=get_post_meta($post->ID,'price_from',true);
        $basis=get_post_meta($post->ID,'price_basis',true)?:'person';
        $tagline=get_post_meta($post->ID,'short_tagline',true);
        $featured=(bool)get_post_meta($post->ID,'featured',true);
        $highlights=self::as_array(get_post_meta($post->ID,'highlights',true));
        $itinerary=self::as_array(get_post_meta($post->ID,'itinerary',true));
        $included=self::as_array(get_post_meta($post->ID,'included',true));
        $excluded=self::as_array(get_post_meta($post->ID,'excluded',true));
        $hotels=get_posts(['post_type'=>'slt_hotel','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC','post_status'=>['publish','draft']]); ?>
        <div class="slt-admin-grid">
            <div class="slt-field"><label>Days</label><input type="number" min="1" name="slt[duration_days]" value="<?php echo esc_attr((string)$days); ?>"></div>
            <div class="slt-field"><label>Nights</label><input type="number" min="0" name="slt[duration_nights]" value="<?php echo esc_attr((string)$nights); ?>"></div>
            <div class="slt-field"><label>Price from (€)</label><input type="number" min="0" step="0.01" name="slt[price_from]" value="<?php echo esc_attr((string)$price); ?>"></div>
            <div class="slt-field"><label>Price basis</label><select name="slt[price_basis]"><?php foreach(['person'=>'Per person','couple'=>'Per couple','trip'=>'Per trip','request'=>'Price on request'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($basis,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
            <div class="slt-field slt-field--wide"><label>Short tagline</label><input type="text" name="slt[short_tagline]" value="<?php echo esc_attr((string)$tagline); ?>"></div>
            <div class="slt-field slt-field--wide"><label class="slt-inline-check"><input type="checkbox" name="slt[featured]" value="1" <?php checked($featured); ?>> Feature this tour on the homepage</label></div>
            <div class="slt-field slt-field--wide"><label>Highlights <small>one per line</small></label><textarea name="slt[highlights_text]" rows="5"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['text']??''):(string)$x,$highlights))); ?></textarea></div>
        </div>
        <div class="slt-admin-section"><div class="slt-admin-section__head"><div><h3>Itinerary</h3><p>Add, remove or reorder the days of this package.</p></div><button type="button" class="button button-secondary" id="slt-add-day">Add day</button></div>
        <div id="slt-itinerary" data-next="<?php echo esc_attr((string)count($itinerary)); ?>"><?php foreach($itinerary as $i=>$row) self::day_row((int)$i,is_array($row)?$row:[],$hotels); ?></div></div>
        <div class="slt-admin-grid">
            <div class="slt-field"><label>What's included <small>one per line</small></label><textarea name="slt[included_text]" rows="8"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['item']??''):(string)$x,$included))); ?></textarea></div>
            <div class="slt-field"><label>Not included <small>one per line</small></label><textarea name="slt[excluded_text]" rows="8"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['item']??''):(string)$x,$excluded))); ?></textarea></div>
        </div>
        <script type="text/template" id="slt-day-template"><?php self::day_row('__INDEX__',[],$hotels); ?></script>
    <?php }

    private static function day_row($i,array $row,array $hotels): void {
        $meals=$row['meals']??[];if(!is_array($meals))$meals=[];$image_id=(int)($row['image_id']??0); ?>
        <div class="slt-day" data-row>
            <div class="slt-day__bar"><strong>Itinerary day</strong><div><button type="button" class="button-link slt-move-up">↑ Up</button> <button type="button" class="button-link slt-move-down">↓ Down</button> <button type="button" class="button-link-delete slt-remove-day">Remove</button></div></div>
            <div class="slt-admin-grid">
                <div class="slt-field"><label>Day</label><input type="number" min="1" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][day]" value="<?php echo esc_attr((string)($row['day']??'')); ?>"></div>
                <div class="slt-field"><label>Title</label><input type="text" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][title]" value="<?php echo esc_attr((string)($row['title']??'')); ?>"></div>
                <div class="slt-field slt-field--wide"><label>Description</label><textarea rows="6" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][description]"><?php echo esc_textarea((string)($row['description']??'')); ?></textarea></div>
                <div class="slt-field slt-field--wide"><label>Photo</label><div class="slt-image-picker"><input type="hidden" class="slt-image-id" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][image_id]" value="<?php echo esc_attr((string)$image_id); ?>"><div class="slt-image-preview"><?php if($image_id)echo wp_get_attachment_image($image_id,'medium'); ?></div><div><button type="button" class="button slt-choose-image">Choose image</button> <button type="button" class="button-link-delete slt-remove-image" <?php echo $image_id?'':'style="display:none"'; ?>>Remove</button></div></div></div>
                <div class="slt-field"><label>Hotel</label><select name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][hotel_id]"><option value="">— None —</option><?php foreach($hotels as $hotel): ?><option value="<?php echo esc_attr((string)$hotel->ID); ?>" <?php selected((int)($row['hotel_id']??0),$hotel->ID); ?>><?php echo esc_html($hotel->post_title); ?></option><?php endforeach; ?></select></div>
                <div class="slt-field"><label>Meals</label><div class="slt-meals"><?php foreach(['breakfast'=>'Breakfast','lunch'=>'Lunch','dinner'=>'Dinner'] as $k=>$v): ?><label><input type="checkbox" name="slt[itinerary][<?php echo esc_attr((string)$i); ?>][meals][]" value="<?php echo esc_attr($k); ?>" <?php checked(in_array($k,$meals,true)); ?>> <?php echo esc_html($v); ?></label><?php endforeach; ?></div></div>
            </div>
        </div>
    <?php }

    public static function hotel_box(WP_Post $post): void {
        wp_nonce_field('slt_save_hotel','slt_hotel_nonce');
        $location=get_post_meta($post->ID,'location',true);$rating=get_post_meta($post->ID,'star_rating',true); ?>
        <div class="slt-admin-grid"><div class="slt-field"><label>Location</label><input type="text" name="slt_hotel[location]" value="<?php echo esc_attr((string)$location); ?>"></div>
        <div class="slt-field"><label>Star rating</label><select name="slt_hotel[star_rating]"><option value="">—</option><?php foreach(['3'=>'3★','4'=>'4★','5'=>'5★'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($rating,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div></div>
    <?php }

    public static function booking_box(WP_Post $post): void {
        wp_nonce_field('slt_save_booking','slt_booking_nonce');
        $tour_id=(int)get_post_meta($post->ID,'_slt_tour_id',true);
        $fields=['Name'=>'_slt_name','Email'=>'_slt_email','Phone'=>'_slt_phone','Travel date'=>'_slt_travel_date','Adults'=>'_slt_adults','Children'=>'_slt_children','Payment method'=>'_slt_payment_method'];
        echo '<table class="widefat striped slt-enquiry-table"><tbody>';
        if($tour_id)echo '<tr><th>Tour</th><td><a href="'.esc_url(get_edit_post_link($tour_id)).'">'.esc_html(get_the_title($tour_id)).'</a></td></tr>';
        foreach($fields as $label=>$key){$value=get_post_meta($post->ID,$key,true);echo '<tr><th>'.esc_html($label).'</th><td>'.esc_html((string)$value).'</td></tr>';}
        echo '</tbody></table>';
        $status=get_post_meta($post->ID,'_slt_status',true)?:'new';
        $payment=get_post_meta($post->ID,'_slt_payment_status',true)?:'not_started';
        $total=(float)get_post_meta($post->ID,'_slt_total',true);
        $deposit=(float)get_post_meta($post->ID,'_slt_deposit',true);
        $mollie=get_post_meta($post->ID,'_slt_mollie_id',true);
        $notes=get_post_meta($post->ID,'_slt_internal_notes',true); ?>
        <div class="slt-admin-section"><h3>Booking & payment</h3><div class="slt-admin-grid">
            <div class="slt-field"><label>Status</label><select name="slt_booking[status]"><?php foreach(['new'=>'New','pending_price'=>'Pending price','payment_pending'=>'Payment pending','deposit_paid'=>'Deposit paid','confirmed'=>'Confirmed','cancelled'=>'Cancelled'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($status,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
            <div class="slt-field"><label>Payment status</label><input value="<?php echo esc_attr((string)$payment); ?>" disabled></div>
            <div class="slt-field"><label>Total (€)</label><input value="<?php echo esc_attr(number_format($total,2,'.','')); ?>" disabled></div>
            <div class="slt-field"><label>Deposit (€)</label><input value="<?php echo esc_attr(number_format($deposit,2,'.','')); ?>" disabled></div>
            <div class="slt-field slt-field--wide"><label>Mollie payment ID</label><input value="<?php echo esc_attr((string)$mollie); ?>" disabled></div>
            <div class="slt-field slt-field--wide"><label>Internal notes</label><textarea rows="5" name="slt_booking[internal_notes]"><?php echo esc_textarea((string)$notes); ?></textarea></div>
        </div></div>
    <?php }

    public static function save_tour(int $post_id): void {
        if(!isset($_POST['slt_tour_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_tour_nonce'])),'slt_save_tour')||!current_user_can('edit_post',$post_id)||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE))return;
        $s=isset($_POST['slt'])&&is_array($_POST['slt'])?wp_unslash($_POST['slt']):[];
        update_post_meta($post_id,'duration_days',max(0,(int)($s['duration_days']??0)));
        update_post_meta($post_id,'duration_nights',max(0,(int)($s['duration_nights']??0)));
        update_post_meta($post_id,'price_from',max(0,(float)($s['price_from']??0)));
        $basis=in_array(($s['price_basis']??'person'),['person','couple','trip','request'],true)?$s['price_basis']:'person';
        update_post_meta($post_id,'price_basis',$basis);
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
        if(!isset($_POST['slt_hotel_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_hotel_nonce'])),'slt_save_hotel')||!current_user_can('edit_post',$post_id)||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE))return;
        $s=isset($_POST['slt_hotel'])&&is_array($_POST['slt_hotel'])?wp_unslash($_POST['slt_hotel']):[];
        update_post_meta($post_id,'location',sanitize_text_field($s['location']??''));
        update_post_meta($post_id,'star_rating',in_array(($s['star_rating']??''),['3','4','5'],true)?$s['star_rating']:'');
    }

    public static function save_booking(int $post_id): void {
        if(!isset($_POST['slt_booking_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_booking_nonce'])),'slt_save_booking')||!current_user_can('edit_post',$post_id)||(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE))return;
        $s=isset($_POST['slt_booking'])&&is_array($_POST['slt_booking'])?wp_unslash($_POST['slt_booking']):[];
        $status=in_array(($s['status']??'new'),['new','pending_price','payment_pending','deposit_paid','confirmed','cancelled'],true)?$s['status']:'new';
        update_post_meta($post_id,'_slt_status',$status);
        update_post_meta($post_id,'_slt_internal_notes',sanitize_textarea_field($s['internal_notes']??''));
    }

    private static function line_rows(string $text,string $key): array {
        $lines=preg_split('/\r\n|\r|\n/',sanitize_textarea_field($text))?:[];
        return array_values(array_map(fn($line)=>[$key=>trim($line)],array_filter($lines,fn($line)=>trim($line)!=='')));
    }
    private static function as_array($value): array {return is_array($value)?$value:[];}

    public static function tour_columns(array $columns): array {
        return ['cb'=>$columns['cb']??'','title'=>'Tour','slt_duration'=>'Duration','slt_price'=>'Price','slt_featured'=>'Featured','date'=>'Date'];
    }
    public static function tour_column(string $column,int $post_id): void {
        if($column==='slt_duration'){echo esc_html((int)get_post_meta($post_id,'duration_days',true).' days / '.(int)get_post_meta($post_id,'duration_nights',true).' nights');}
        if($column==='slt_price'){$basis=get_post_meta($post_id,'price_basis',true);$price=get_post_meta($post_id,'price_from',true);echo $basis==='request'?'On request':esc_html($price?('€'.number_format_i18n((float)$price,0)):'—');}
        if($column==='slt_featured')echo get_post_meta($post_id,'featured',true)?'★':'—';
    }

    public static function booking_columns(array $columns): array {
        return ['cb'=>$columns['cb']??'','title'=>'Booking','slt_status'=>'Status','slt_payment'=>'Payment','slt_tour'=>'Tour','slt_date'=>'Travel date','date'=>'Created'];
    }
    public static function booking_column(string $column,int $post_id): void {
        if($column==='slt_status')echo esc_html((string)(get_post_meta($post_id,'_slt_status',true)?:'new'));
        if($column==='slt_payment')echo esc_html((string)(get_post_meta($post_id,'_slt_payment_status',true)?:'not_started'));
        if($column==='slt_tour'){$id=(int)get_post_meta($post_id,'_slt_tour_id',true);echo $id?esc_html(get_the_title($id)):'—';}
        if($column==='slt_date')echo esc_html((string)get_post_meta($post_id,'_slt_travel_date',true));
    }

    public static function setting(string $key,$default=null){
        $s=get_option(self::SETTINGS_KEY,[]);
        return array_key_exists($key,$s)?$s[$key]:$default;
    }

    private static function pricing(int $tour_id,int $adults,int $children): array {
        $price=(float)get_post_meta($tour_id,'price_from',true);
        $basis=(string)(get_post_meta($tour_id,'price_basis',true)?:'person');
        if($basis==='request'||$price<=0)return ['total'=>0.0,'deposit'=>0.0,'payable'=>false];
        if($basis==='trip')$total=$price;
        elseif($basis==='couple')$total=$price*(int)ceil(max(1,$adults)/2);
        else $total=$price*max(1,$adults);
        $deposit=round($total*((int)self::setting('deposit_percent',30)/100),2);
        return ['total'=>$total,'deposit'=>$deposit,'payable'=>$deposit>0];
    }

    public static function booking_form($atts=[]): string {
        $atts=shortcode_atts(['tour_id'=>get_the_ID()],$atts,'slt_booking_form');
        $tour_id=absint($atts['tour_id']);$mode=(string)self::setting('payment_mode','demo');
        ob_start(); ?>
        <div class="slt-checkout">
            <?php if($mode==='demo'): ?><div class="slt-demo-badge">Mode démonstration — aucun paiement réel ne sera débité.</div><?php endif; ?>
            <form class="slt-booking-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="slt_submit_booking"><input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
                <?php wp_nonce_field('slt_submit_booking','slt_booking_nonce'); ?>
                <div class="slt-form-grid">
                    <label>Nom complet<input name="name" required autocomplete="name"></label>
                    <label>Email<input type="email" name="email" required autocomplete="email"></label>
                    <label>Téléphone<input name="phone" autocomplete="tel"></label>
                    <label>Date de départ<input type="date" name="travel_date" required></label>
                    <label>Adultes<input type="number" name="adults" min="1" value="2" required></label>
                    <label>Enfants<input type="number" name="children" min="0" value="0"></label>
                </div>
                <fieldset class="slt-payment-methods"><legend>Moyen de paiement</legend>
                    <label class="slt-pay-option slt-pay-option--featured"><input type="radio" name="payment_method" value="wero" checked><span><strong>Wero</strong><small>Paiement bancaire européen instantané, si disponible</small></span><b>Européen</b></label>
                    <label class="slt-pay-option"><input type="radio" name="payment_method" value="creditcard"><span><strong>Cartes Bancaires / Visa / Mastercard</strong><small>Idéal pour les clients en France</small></span></label>
                    <label class="slt-pay-option"><input type="radio" name="payment_method" value="applepay"><span><strong>Apple Pay</strong><small>Si disponible sur l’appareil</small></span></label>
                    <label class="slt-pay-option"><input type="radio" name="payment_method" value="ideal"><span><strong>iDEAL</strong><small>Pour les clients néerlandais</small></span></label>
                    <label class="slt-pay-option"><input type="radio" name="payment_method" value="paypal"><span><strong>PayPal</strong><small>Portefeuille en ligne</small></span></label>
                    <label class="slt-pay-option"><input type="radio" name="payment_method" value="banktransfer"><span><strong>Virement SEPA</strong><small>Virement bancaire en euros</small></span></label>
                </fieldset>
                <label class="slt-checkbox"><input type="checkbox" name="terms" value="1" required> J’accepte les conditions de réservation et la politique de confidentialité.</label>
                <button class="slt-button slt-button--pay" type="submit"><?php echo $mode==='demo'?'Simuler la réservation':'Réserver et payer l’acompte'; ?></button>
                <p class="slt-secure-note">Paiement sécurisé via Mollie. Le montant final est toujours vérifié côté serveur.</p>
            </form>
        </div>
        <?php return (string)ob_get_clean();
    }

    public static function submit_booking(): void {
        if(!isset($_POST['slt_booking_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slt_booking_nonce'])),'slt_submit_booking'))wp_die('Invalid request.',403);
        $name=sanitize_text_field(wp_unslash($_POST['name']??''));$email=sanitize_email(wp_unslash($_POST['email']??''));
        if(!$name||!is_email($email)||empty($_POST['terms']))wp_die('Please complete the required fields.',400);
        $tour_id=absint($_POST['tour_id']??0);if(!$tour_id||get_post_type($tour_id)!=='slt_tour')wp_die('Invalid tour.',400);
        $adults=max(1,(int)($_POST['adults']??1));$children=max(0,(int)($_POST['children']??0));
        $pricing=self::pricing($tour_id,$adults,$children);
        $method=sanitize_key($_POST['payment_method']??'wero');
        $allowed=['wero','creditcard','applepay','ideal','paypal','banktransfer'];if(!in_array($method,$allowed,true))$method='wero';
        $booking_id=wp_insert_post(['post_type'=>'slt_booking','post_status'=>'publish','post_title'=>$name.' — '.get_the_title($tour_id)]);
        if(is_wp_error($booking_id))wp_die('Could not create booking.',500);
        $data=['name'=>$name,'email'=>$email,'phone'=>sanitize_text_field(wp_unslash($_POST['phone']??'')),'travel_date'=>sanitize_text_field(wp_unslash($_POST['travel_date']??'')),'adults'=>$adults,'children'=>$children,'tour_id'=>$tour_id,'payment_method'=>$method];
        foreach($data as $k=>$v)update_post_meta($booking_id,'_slt_'.$k,$v);
        $public_token=wp_generate_password(24,false,false);update_post_meta($booking_id,'_slt_public_token',$public_token);
        update_post_meta($booking_id,'_slt_total',$pricing['total']);update_post_meta($booking_id,'_slt_deposit',$pricing['deposit']);

        $mode=(string)self::setting('payment_mode','demo');
        if(!$pricing['payable']){
            update_post_meta($booking_id,'_slt_status','pending_price');update_post_meta($booking_id,'_slt_payment_status','not_started');
            self::send_booking_email($booking_id);
            wp_safe_redirect(add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'pending_price'],home_url('/reservation/')));exit;
        }
        if($mode==='demo'){
            update_post_meta($booking_id,'_slt_status','payment_pending');update_post_meta($booking_id,'_slt_payment_status','demo');
            self::send_booking_email($booking_id);
            wp_safe_redirect(add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'demo'],home_url('/reservation/')));exit;
        }

        $api=(string)self::setting('mollie_api_key','');
        if($api===''){
            update_post_meta($booking_id,'_slt_status','payment_pending');update_post_meta($booking_id,'_slt_payment_status','configuration_required');
            wp_safe_redirect(add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'configuration_required'],home_url('/reservation/')));exit;
        }

        $payload=[
            'amount'=>['currency'=>'EUR','value'=>number_format((float)$pricing['deposit'],2,'.','')],
            'description'=>'Acompte réservation #'.$booking_id.' - '.get_the_title($tour_id),
            'redirectUrl'=>add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'return'],home_url('/reservation/')),
            'cancelUrl'=>add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'cancelled'],home_url('/reservation/')),
            'webhookUrl'=>rest_url('slt/v1/mollie-webhook'),
            'method'=>$method,
            'locale'=>'fr_FR',
            'metadata'=>['booking_id'=>$booking_id,'tour_id'=>$tour_id]
        ];
        $payment=self::mollie('POST','payments',$payload);
        if(is_wp_error($payment)){
            unset($payload['method']);
            $payment=self::mollie('POST','payments',$payload);
        }
        if(is_wp_error($payment)||empty($payment['id'])||empty($payment['_links']['checkout']['href'])){
            update_post_meta($booking_id,'_slt_status','payment_pending');update_post_meta($booking_id,'_slt_payment_status','failed_to_start');
            wp_safe_redirect(add_query_arg(['booking'=>$booking_id,'token'=>$public_token,'state'=>'payment_error'],home_url('/reservation/')));exit;
        }
        update_post_meta($booking_id,'_slt_mollie_id',sanitize_text_field($payment['id']));
        update_post_meta($booking_id,'_slt_status','payment_pending');update_post_meta($booking_id,'_slt_payment_status',sanitize_text_field($payment['status']??'open'));
        self::send_booking_email($booking_id);
        wp_redirect(esc_url_raw($payment['_links']['checkout']['href']));exit;
    }

    private static function mollie(string $method,string $endpoint,array $payload=[]){
        $api=(string)self::setting('mollie_api_key','');if($api==='')return new WP_Error('mollie_missing','Mollie API key missing');
        $args=['method'=>$method,'timeout'=>20,'headers'=>['Authorization'=>'Bearer '.$api,'Content-Type'=>'application/json']];
        if($payload)$args['body']=wp_json_encode($payload);
        $r=wp_remote_request('https://api.mollie.com/v2/'.ltrim($endpoint,'/'),$args);
        if(is_wp_error($r))return $r;
        $code=wp_remote_retrieve_response_code($r);$body=json_decode(wp_remote_retrieve_body($r),true);
        if($code<200||$code>=300)return new WP_Error('mollie_http','Mollie request failed',['status'=>$code,'body'=>$body]);
        return is_array($body)?$body:[];
    }

    public static function mollie_webhook(WP_REST_Request $request): WP_REST_Response {
        $id=sanitize_text_field((string)($request->get_param('id')??''));
        if($id===''){
            $json=$request->get_json_params();$id=sanitize_text_field((string)($json['id']??''));
        }
        if($id==='')return new WP_REST_Response(['ok'=>false],400);
        $payment=self::mollie('GET','payments/'.rawurlencode($id));
        if(is_wp_error($payment))return new WP_REST_Response(['ok'=>false],502);
        $booking_id=absint($payment['metadata']['booking_id']??0);
        if(!$booking_id||get_post_type($booking_id)!=='slt_booking')return new WP_REST_Response(['ok'=>false],404);
        $status=sanitize_key($payment['status']??'unknown');
        update_post_meta($booking_id,'_slt_payment_status',$status);
        if($status==='paid')update_post_meta($booking_id,'_slt_status','deposit_paid');
        elseif(in_array($status,['failed','expired','canceled'],true))update_post_meta($booking_id,'_slt_status','payment_pending');
        return new WP_REST_Response(['ok'=>true],200);
    }

    public static function booking_result(): string {
        $booking_id=absint($_GET['booking']??0);$state=sanitize_key($_GET['state']??'');$token=sanitize_text_field(wp_unslash($_GET['token']??''));
        $stored=$booking_id?(string)get_post_meta($booking_id,'_slt_public_token',true):'';
        if(!$booking_id||get_post_type($booking_id)!=='slt_booking'||$stored===''||$token===''||!hash_equals($stored,$token))return '<div class="slt-result"><h2>Votre réservation</h2><p>Utilisez le bouton « Réserver » sur un circuit pour commencer.</p></div>';
        $payment_id=(string)get_post_meta($booking_id,'_slt_mollie_id',true);
        if($state==='return'&&$payment_id&&self::setting('payment_mode','demo')==='mollie'){
            $payment=self::mollie('GET','payments/'.rawurlencode($payment_id));
            if(!is_wp_error($payment)){
                $status=sanitize_key($payment['status']??'unknown');update_post_meta($booking_id,'_slt_payment_status',$status);
                if($status==='paid')update_post_meta($booking_id,'_slt_status','deposit_paid');
            }
        }
        $status=(string)get_post_meta($booking_id,'_slt_status',true);$payment=(string)get_post_meta($booking_id,'_slt_payment_status',true);
        $deposit=(float)get_post_meta($booking_id,'_slt_deposit',true);$tour_id=(int)get_post_meta($booking_id,'_slt_tour_id',true);
        ob_start(); ?>
        <div class="slt-result">
            <div class="eyebrow">Réservation #<?php echo esc_html((string)$booking_id); ?></div>
            <?php if($state==='demo'): ?><h2>Réservation de démonstration créée</h2><p>Aucun montant réel n’a été débité. Le parcours de réservation fonctionne et passera vers Mollie dès qu’une clé API sera ajoutée.</p>
            <?php elseif($status==='deposit_paid'||$payment==='paid'): ?><h2>Acompte reçu</h2><p>Merci. Votre acompte a été reçu et la réservation peut maintenant être confirmée.</p>
            <?php elseif($state==='pending_price'): ?><h2>Réservation enregistrée</h2><p>Ce circuit n’a pas encore de tarif fixe. L’équipe doit confirmer le prix avant de déclencher le paiement.</p>
            <?php elseif($state==='payment_error'): ?><h2>Le paiement n’a pas pu démarrer</h2><p>Votre réservation est bien enregistrée. Vous pourrez reprendre le paiement une fois la configuration vérifiée.</p>
            <?php else: ?><h2>Réservation enregistrée</h2><p>Statut du paiement : <strong><?php echo esc_html($payment?:'en attente'); ?></strong>.</p><?php endif; ?>
            <div class="slt-result-summary"><span><?php echo esc_html(get_the_title($tour_id)); ?></span><?php if($deposit>0): ?><strong>Acompte : €<?php echo esc_html(number_format_i18n($deposit,2)); ?></strong><?php endif; ?></div>
            <a class="button button--dark" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Voir les circuits</a>
        </div>
        <?php return (string)ob_get_clean();
    }

    public static function contact_details(): string {
        $email=(string)self::setting('business_email',get_option('admin_email'));$phone=(string)self::setting('phone','');$wa=(string)self::setting('whatsapp','');
        ob_start(); ?><div class="contact-cards">
            <div><strong>Email</strong><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
            <?php if($phone): ?><div><strong>Téléphone</strong><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$phone)); ?>"><?php echo esc_html($phone); ?></a></div><?php endif; ?>
            <?php if($wa): ?><div><strong>WhatsApp</strong><span><?php echo esc_html($wa); ?></span></div><?php endif; ?>
        </div><?php return (string)ob_get_clean();
    }

    private static function send_booking_email(int $booking_id): void {
        $to=(string)(self::setting('business_email',get_option('admin_email'))?:get_option('admin_email'));
        $name=(string)get_post_meta($booking_id,'_slt_name',true);$email=(string)get_post_meta($booking_id,'_slt_email',true);
        $tour_id=(int)get_post_meta($booking_id,'_slt_tour_id',true);
        wp_mail($to,'Nouvelle réservation #'.$booking_id.' - '.get_the_title($tour_id),"Nom: $name\nEmail: $email\nCircuit: ".get_the_title($tour_id)."\nDate: ".get_post_meta($booking_id,'_slt_travel_date',true)."\nAdultes: ".get_post_meta($booking_id,'_slt_adults',true)."\nEnfants: ".get_post_meta($booking_id,'_slt_children',true),['Reply-To: '.$name.' <'.$email.'>']);
    }
}
SLT_Core::init();
register_activation_hook(__FILE__,function(){SLT_Core::content();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,'flush_rewrite_rules');
