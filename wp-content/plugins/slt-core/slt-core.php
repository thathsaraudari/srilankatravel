<?php
/**
 * Plugin Name: SLT Core
 * Description: Travel package content model, admin editor, settings and enquiry handling for the Sri Lanka Travel site.
 * Version: 0.2.0
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
            'included'=>'array','excluded'=>'array','location'=>'string','star_rating'=>'string'
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
            <div class="slt-field"><label>Price from</label><input type="number" min="0" step="0.01" name="slt[price_from]" value="<?php echo esc_attr((string)$price); ?>"></div>
            <div class="slt-field"><label>Price basis</label><select name="slt[price_basis]"><?php foreach(['person'=>'Per person','couple'=>'Per couple','trip'=>'Per trip','request'=>'Price on request'] as $k=>$v): ?><option value="<?php echo esc_attr($k); ?>" <?php selected($basis,$k); ?>><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
            <div class="slt-field slt-field--wide"><label>Short tagline</label><input type="text" name="slt[short_tagline]" value="<?php echo esc_attr((string)$tagline); ?>"></div>
            <div class="slt-field slt-field--wide"><label class="slt-inline-check"><input type="checkbox" name="slt[featured]" value="1" <?php checked($featured); ?>> Feature this tour on the homepage</label></div>
            <div class="slt-field slt-field--wide"><label>Highlights <small>one per line</small></label><textarea name="slt[highlights_text]" rows="5"><?php echo esc_textarea(implode("\n",array_map(fn($x)=>is_array($x)?($x['text']??''):(string)$x,$highlights))); ?></textarea></div>
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
        $fields=['Name'=>'_slt_name','Email'=>'_slt_email','Phone / WhatsApp'=>'_slt_phone','Travel date'=>'_slt_travel_date','Adults'=>'_slt_adults','Children'=>'_slt_children','Message'=>'_slt_message'];
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
            foreach(['phone','travel_date','adults','children','message'] as $key){$value=$_POST[$key]??'';$value=$key==='message'?sanitize_textarea_field(wp_unslash($value)):sanitize_text_field(wp_unslash($value));update_post_meta($id,'_slt_'.$key,$value);}
            update_post_meta($id,'_slt_name',$name);update_post_meta($id,'_slt_email',$email);update_post_meta($id,'_slt_tour_id',$tour_id);update_post_meta($id,'_slt_status','new');
            $to=self::setting('business_email',get_option('admin_email'))?:get_option('admin_email');
            wp_mail($to,'Nouvelle demande de voyage : '.$tour,"Name: $name\nEmail: $email\nTour: $tour\nTravel date: ".sanitize_text_field(wp_unslash($_POST['travel_date']??''))."\n\n".sanitize_textarea_field(wp_unslash($_POST['message']??'')),['Reply-To: '.$name.' <'.$email.'>']);
        }
        wp_safe_redirect(add_query_arg('enquiry','success',wp_get_referer()?:home_url('/')));exit;
    }
}
SLT_Core::init();
register_activation_hook(__FILE__,function(){SLT_Core::content();flush_rewrite_rules();});
register_deactivation_hook(__FILE__,'flush_rewrite_rules');
