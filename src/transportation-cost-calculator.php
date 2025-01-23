<?php
/**
 * Plugin Name: Transportation Cost Calculator
 * Description: A simple transportation cost calculator.
 * Version: 1.0
 * Author: Ali Ansari
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Enqueue necessary styles and scripts
function tcc_enqueue_assets() {
    wp_enqueue_style( 'tcc-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' );
    wp_enqueue_style( 'tcc-style', plugin_dir_url( __FILE__ ) . 'assets/css/calculator.css' );

    // Enqueue script and pass the fee and clearance variables from WordPress settings
    $fee = get_option('tcc_fee', '0.02'); // Get fee from options, default 0
    $clearance = get_option('tcc_clearance', '150'); // Get clearance from options, default 0
	$price_per_20ft_container = get_option('tcc_price_per_20ft_container', '1500');
	$price_per_40ft_container = get_option('tcc_price_per_40ft_container', '2000');
	$normal_sea_rate = get_option('tcc_normal_sea_rate', '650');
	$copy_sea_rate = get_option('tcc_copy_sea_rate', '950');
	$liquid_sea_rate = get_option('tcc_liquid_sea_rate', '1200');
	$battery_sea_rate = get_option('tcc_battery_sea_rate', '1400');
	$normal_air_rate = get_option('tcc_normal_air_rate', '650');
	$copy_air_rate = get_option('tcc_copy_air_rate', '950');
	$liquid_air_rate = get_option('tcc_liquid_air_rate', '1200');
	$battery_air_rate = get_option('tcc_battery_air_rate', '1400');
    wp_enqueue_script( 'tcc-script', plugin_dir_url( __FILE__ ) . 'assets/js/calculator.js', array('jquery'), null, true );
    
    wp_localize_script('tcc-script', 'tccVars', [
        'fee' => floatval($fee),
        'clearance' => floatval($clearance),
		'price_per_20ft_container' => floatval($price_per_20ft_container),
		'price_per_40ft_container' => floatval($price_per_40ft_container),
		'normal_sea_rate' => floatval($normal_sea_rate),
		'copy_sea_rate' => floatval($copy_sea_rate),
		'liquid_sea_rate' => floatval($liquid_sea_rate),
		'battery_sea_rate' => floatval($battery_sea_rate),
		'normal_air_rate' => floatval($normal_air_rate),
		'copy_air_rate' => floatval($copy_air_rate),
		'liquid_air_rate' => floatval($liquid_air_rate),
		'battery_air_rate' => floatval($battery_air_rate),
    ]);
}
add_action( 'wp_enqueue_scripts', 'tcc_enqueue_assets' );

/**
 * Create the settings page
 */
function tcc_add_settings_page() {
    add_menu_page(
        'TCC Settings', // Page title
        'TCC Settings', // Menu title
        'manage_options', // Capability
        'tcc-settings', // Slug
        'tcc_render_settings_page', // Callback function
        'dashicons-admin-generic', // Menu icon
        100 // Position
    );
}
add_action('admin_menu', 'tcc_add_settings_page');

/**
 * Render the settings page
 */
function tcc_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Transportation Cost Calculator Settings', 'tcc'); ?></h1>

        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting
            settings_fields('tcc_settings_group');
            // Output setting sections and their fields
            do_settings_sections('tcc-settings');
            // Output save settings button
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register settings, sections, and fields
 */
function tcc_register_settings() {
    // Register fee and clearance settings
    register_setting('tcc_settings_group', 'tcc_fee');
    register_setting('tcc_settings_group', 'tcc_clearance');
	register_setting('tcc_settings_group', 'tcc_price_per_20ft_container');
	register_setting('tcc_settings_group', 'tcc_price_per_40ft_container');
	register_setting('tcc_settings_group', 'tcc_normal_sea_rate');
	register_setting('tcc_settings_group', 'tcc_copy_sea_rate');
	register_setting('tcc_settings_group', 'tcc_liquid_sea_rate');
	register_setting('tcc_settings_group', 'tcc_battery_sea_rate');
	register_setting('tcc_settings_group', 'tcc_normal_air_rate');
	register_setting('tcc_settings_group', 'tcc_copy_air_rate');
	register_setting('tcc_settings_group', 'tcc_liquid_air_rate');
	register_setting('tcc_settings_group', 'tcc_battery_air_rate');
    // Add a settings section
    add_settings_section(
        'tcc_settings_section', 
        'Calculator Settings', 
        function() {
            echo '<p></p>';
        }, 
        'tcc-settings'
    );
add_settings_field('tcc_fee_field', 'کارمزد', 'tcc_fee_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_clearance_field', 'ترخیص', 'tcc_clearance_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_price_per_20ft_container_field', 'قیمت به ازای هر کانتینر 20 فوتی', 'tcc_price_per_20ft_container_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_price_per_40ft_container_field', 'قیمت به ازای هر کانتینر 40 فوتی', 'tcc_price_per_40ft_container_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_normal_sea_rate_field', 'نرخ کالاهای معمولی - دریایی', 'tcc_normal_sea_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_copy_sea_rate_field', 'نرخ کالاهای کپی - دریایی', 'tcc_copy_sea_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_liquid_sea_rate_field', 'نرخ کالاهای مایع - دریایی', 'tcc_liquid_sea_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_battery_sea_rate_field', 'نرخ کالاهای دارای باتری - دریایی', 'tcc_battery_sea_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_normal_air_rate_field', 'نرخ کالاهای معمولی - هوایی', 'tcc_normal_air_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_copy_air_rate_field', 'نرخ کالاهای کپی - هوایی', 'tcc_copy_air_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_liquid_air_rate_field', 'نرخ کالاهای مایع - هوایی', 'tcc_liquid_air_rate_field_render', 'tcc-settings', 'tcc_settings_section');
    add_settings_field('tcc_battery_air_rate_field', 'نرخ کالاهای دارای باتری - هوایی', 'tcc_battery_air_rate_field_render', 'tcc-settings', 'tcc_settings_section');
}
add_action('admin_init', 'tcc_register_settings');

function tcc_fee_field_render() {
    $fee = get_option('tcc_fee', '0');
    ?>
    <input type="number" step="0.01" name="tcc_fee" value="<?php echo esc_attr($fee); ?>" />
    <?php
}

function tcc_clearance_field_render() {
    $clearance = get_option('tcc_clearance', '0');
    ?>
    <input type="number" step="0.01" name="tcc_clearance" value="<?php echo esc_attr($clearance); ?>" />
    <?php
}


function tcc_price_per_20ft_container_field_render() {
    $price_per_20ft_container = get_option('tcc_price_per_20ft_container', '0');
    ?>
    <input type="number" step="0.01" name="tcc_price_per_20ft_container" value="<?php echo esc_attr($price_per_20ft_container); ?>" />
    <?php
}

function tcc_price_per_40ft_container_field_render() {
    $price_per_40ft_container = get_option('tcc_price_per_40ft_container', '0');
    ?>
    <input type="number" step="0.01" name="tcc_price_per_40ft_container" value="<?php echo esc_attr($price_per_40ft_container); ?>" />
    <?php
}

function tcc_normal_sea_rate_field_render() {
    $normal_sea_rate = get_option('tcc_normal_sea_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_normal_sea_rate" value="<?php echo esc_attr($normal_sea_rate); ?>" />
    <?php
}

function tcc_copy_sea_rate_field_render() {
    $copy_sea_rate = get_option('tcc_copy_sea_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_copy_sea_rate" value="<?php echo esc_attr($copy_sea_rate); ?>" />
    <?php
}

function tcc_liquid_sea_rate_field_render() {
    $liquid_sea_rate = get_option('tcc_liquid_sea_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_liquid_sea_rate" value="<?php echo esc_attr($liquid_sea_rate); ?>" />
    <?php
}

function tcc_battery_sea_rate_field_render() {
    $battery_sea_rate = get_option('tcc_battery_sea_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_battery_sea_rate" value="<?php echo esc_attr($battery_sea_rate); ?>" />
    <?php
}

function tcc_normal_air_rate_field_render() {
    $normal_air_rate = get_option('tcc_normal_air_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_normal_air_rate" value="<?php echo esc_attr($normal_air_rate); ?>" />
    <?php
}

function tcc_copy_air_rate_field_render() {
    $copy_air_rate = get_option('tcc_copy_air_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_copy_air_rate" value="<?php echo esc_attr($copy_air_rate); ?>" />
    <?php
}

function tcc_liquid_air_rate_field_render() {
    $liquid_air_rate = get_option('tcc_liquid_air_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_liquid_air_rate" value="<?php echo esc_attr($liquid_air_rate); ?>" />
    <?php
}

function tcc_battery_air_rate_field_render() {
    $battery_air_rate = get_option('tcc_battery_air_rate', '0');
    ?>
    <input type="number" step="0.01" name="tcc_battery_air_rate" value="<?php echo esc_attr($battery_air_rate); ?>" />
    <?php
}

// Create the shortcode to render the calculator
function tcc_calculator_shortcode() {
    ob_start(); ?>
	<div class="shrink-container">
    <div class="tcc-tab-container">
        <div class="tcc-tabs">
            
            <div class="tcc-tab active" onclick="tcc_showTabContent(0)">جعبه</div>
            <div class="tcc-tab" onclick="tcc_showTabContent(1)">کانتینر</div>
        </div>
        
<!-- Tab 2: Boxes -->
<div class="tcc-tab-content active">
    <div class="tcc-container my-5">
        <div class="tcc-card p-4 shadow-lg border-0">
            <h3 class="tcc-text-center tcc-text-danger mb-4">محاسبه‌گر جعبه</h3>
            <form id="tcc-boxes-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-length" class="form-label">طول (سانتی‌متر)</label>
                        <input type="number" id="tcc-boxes-length" class="form-control tcc-border-danger" placeholder="طول را وارد کنید" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-width" class="form-label">عرض (سانتی‌متر)</label>
                        <input type="number" id="tcc-boxes-width" class="form-control tcc-border-danger" placeholder="عرض را وارد کنید" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-height" class="form-label">ارتفاع (سانتی‌متر)</label>
                        <input type="number" id="tcc-boxes-height" class="form-control tcc-border-danger" placeholder="ارتفاع را وارد کنید" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-weight" class="form-label">وزن (کیلوگرم)</label>
                        <input type="number" id="tcc-boxes-weight" class="form-control tcc-border-danger" placeholder="وزن را وارد کنید" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="tcc-boxes-number" class="form-label">تعداد جعبه‌ها</label>
                        <input type="number" id="tcc-boxes-number" class="form-control tcc-border-danger" placeholder="تعداد جعبه‌ها را وارد کنید" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-method" class="form-label">روش حمل‌ونقل</label>
                        <select id="tcc-boxes-method" class="form-select tcc-border-danger" required>
                            <option value="" disabled selected>روش را انتخاب کنید</option>
                            <option value="sea">حمل دریایی</option>
                            <option value="air">حمل هوایی</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tcc-boxes-goodsType" class="form-label">نوع کالا</label>
                        <select id="tcc-boxes-goodsType" class="form-select tcc-border-danger" required>
                            <option value="" disabled selected>نوع کالا را انتخاب کنید</option>
                            <option value="normal">کالای عادی</option>
                            <option value="copy">کالای کپی</option>
                            <option value="liquid">کالای مایع</option>
                            <option value="battery">کالای باتری</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="tcc-boxes-price" class="form-label">قیمت (¥)</label>
                        <input type="number" id="tcc-boxes-price" class="form-control tcc-border-danger" placeholder="قیمت را وارد کنید" required>
                    </div>
                </div>
                <div id="tcc-boxes-result" class="tcc-result-card bg-light tcc-border-danger rounded mt-4">
                    <h5 class="tcc-text-success">هزینه حمل‌ونقل:</h5>
                    <h3 class="tcc-text-danger" style="font-family: 'Arial', sans-serif;">لطفاً طول را وارد کنید.</h3>
                </div>
                <p></p>
                <div class="tcc-text-center">
                    <button type="reset" id="tcc-boxes-resetButton" class="tcc-btn-reset">بازنشانی (Shift+N)</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- زبانه ۳: کانتینر -->
<div class="tcc-tab-content">
    <div class="tcc-container my-5">
        <div class="tcc-card p-4 shadow-lg border-0">
            <h3 class="tcc-text-center tcc-text-danger mb-4">محاسبه‌گر کانتینر</h3>
            <form id="tcc-container-form">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="tcc-container-type" class="form-label">نوع کانتینر</label>
                        <select id="tcc-container-type" class="form-select tcc-border-danger" required>
                            <option value="" disabled selected>نوع کانتینر را انتخاب کنید</option>
                            <option value="20ft">کانتینر ۲۰ فوت</option>
                            <option value="40ft">کانتینر ۴۰ فوت</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="tcc-container-number" class="form-label">تعداد کانتینرها</label>
                        <input type="number" id="tcc-container-number" class="form-control tcc-border-danger" placeholder="تعداد کانتینرها را وارد کنید" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="tcc-container-goodsType" class="form-label">نوع کالا</label>
                        <select id="tcc-container-goodsType" class="form-select tcc-border-danger" required>
                            <option value="" disabled selected>نوع کالا را انتخاب کنید</option>
                            <option value="normal">کالای عادی</option>
                            <option value="copy">کالای کپی</option>
                            <option value="liquid">کالای مایع</option>
                            <option value="battery">کالای باتری</option>
                        </select>
                    </div>
                </div>
                <div id="tcc-container-result" class="tcc-result-card bg-light tcc-border-danger rounded mt-4">
                    <h5 class="tcc-text-success">هزینه حمل‌ونقل:</h5>
                    <h3 class="tcc-text-danger" style="font-family: 'Arial', sans-serif;">لطفاً نوع کانتینر را انتخاب کنید.</h3>
                </div>
                <p></p>
                <div class="tcc-text-center">
                    <button type="reset" id="tcc-container-resetButton" class="tcc-btn-reset">بازنشانی (Shift+N)</button>
                </div>
            </form>
        </div>
    </div>
</div>

		</div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'transportation_cost_calculator', 'tcc_calculator_shortcode' );
?>
