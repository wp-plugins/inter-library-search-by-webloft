<?php
/*
Plugin Name: ILS Search by Webloft
Plugin URI: http://www.webekspertene.no/
Description: Interlibrary search for your Wordpress site! NORWEGIAN: Setter inn s&oslash;kefelt som lar deg s&oslash;ke i mange forskjellige bibliotekssystemer.
Version: 2.0.3
Author: H&aring;kon Sundaune / Webekspertene
Author URI: http://www.webekspertene.no/
*/


// FIRST COMES THE SHORTCODE... EH, CODE!

define('ILS_URL', plugins_url(basename(__DIR__)));

function wl_ils_func($atts){

wp_enqueue_script('wl_ils-iframe-script', plugins_url( 'js/iframeheight.js', __FILE__ ), array('jquery') );
wp_enqueue_script('wl_ils-script', plugins_url( 'js/wl-ils.js', __FILE__ ), array('jquery') );

wp_enqueue_style( 'wl_ils', plugins_url( 'css/wl-ils.css', __FILE__ ), false, '1.0', 'all' );

extract(shortcode_atts(array(
	'mittbibliotek' => '0'
   ), $atts));

$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');
$bibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$enkeltbibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$standardbibliotek = get_option('wl_ils_option_mittbibliotek' , '0');
$omslagbokkilden = get_option('wl_ils_option_omslagbokkilden' , '0');
$omslagnb = get_option('wl_ils_option_omslagnb' , '0');
$hamedbilder = get_option('wl_ils_option_hamedbilder' , '1');
$makstreff = get_option('wl_ils_option_makstreff' , '25');
if (isset($_REQUEST['webloftsok_query'])) {
	$hamedsok = stripslashes(strip_tags($_REQUEST['webloftsok_query']));
} else {
	$hamedsok = '';
}

if ($mittbibliotek == "0") { // ikke satt i shortcode
	if ($standardbibliotek == "0") { // ikke satt i backend
		$brukbibliotek = "2020000"; // Akershus fylkesbib. er standard
	} else { // satt i backend
		$brukbibliotek = $standardbibliotek;
	}
} else { // satt i shortcode
	$brukbibliotek = $mittbibliotek;
}


if (isset($_REQUEST['katalog'])) { // kan være satt i widget
	$brukbibliotek = stripslashes(strip_tags($_REQUEST['katalog']));
}

// lage URL i tilfelle det er lenket direkte til søkeside

$frameurl = plugins_url('search.php' , __FILE__) . "?mittbibliotek=" . $brukbibliotek . "&omslagbokkilden=" . $omslagbokkilden . "&bibsysbestand=" . $bibsysbestand . "&omslagnb=" . $omslagnb . "&hamedbilder=" . $hamedbilder . "&makstreff=" . $makstreff . "&s=" . $hamedsok;

if ($hamedsok != '') {
	$framekode = " src=\"" . $frameurl . "\"";
} else {
	$framekode = '';
}

// Gjemmer iframe og viser spinner mens lasting
//


		ob_start();
		require dirname(__FILE__) . '/templates/search-form.php';
		$out = ob_get_clean();

// DOKUMENTASJON: https://github.com/Sly777/Iframe-Height-Jquery-Plugin

return $out;

};

//*******************************************************************************************
// Andre kortkode: Viser enkeltpost på siden hvor kortkoden [enkeltpost] står
//*******************************************************************************************

function enkeltpost_func ($atts)
{
	wp_enqueue_style('wl_ils-enkeltpost', plugins_url( 'css/wl-ils.css', __FILE__ ), false, '1.0', 'all');
	wp_enqueue_script('wl_ils-tabs-script', plugins_url( 'js/tabs.js', __FILE__ ), array('jquery'));
	wp_enqueue_script('wl_ils-script', plugins_url( 'js/wl-ils.js', __FILE__ ), array('jquery'));

	require_once dirname(__FILE__) . '/functions.php';
	require_once dirname(__FILE__) . '/systemer.php';

	$info = stristr($_SERVER['REQUEST_URI'] , "enkeltpostinfo="); // fra og med "enkeltpost="
	if (stristr($info , "&")) {
		$info = stristr($info , "&" , TRUE); // men bare fram til "&" hvis det finnes
	}
	$info = str_replace ("&" , "" , $info); // fjern &
	$info = str_replace ("enkeltpostinfo=" , "" , $info); // fjern det andre

	$system = stristr($_SERVER['REQUEST_URI'] , "system="); // fra og med "system="
	if (stristr($system , "&")) {
		$system = stristr($system , "&" , TRUE); // men bare fram til "&" hvis det finnes
	}
	$system = str_replace ("&" , "" , $system); // fjern &
	$system = str_replace ("system=" , "" , $system); // fjern det andre
	$system = strtolower($system);

	if (isset($info)) {

		//************** VISER ENKELPOST ***************

		if ($system == "koha") { // hvis Koha har vi fått all info i query string
			$treff = unserialize(base64_decode($info));
		} else { // Ikke koha, vi må gjøre oppslag
			$enkeltpostinfo = unserialize(base64_decode($info));
			$treff = hent_enkeltpost ($enkeltpostinfo['bibkode'], $system, $enkeltpostinfo['postid']);
			$treff = hente_omslag ($treff); // legger til omslag
			$treff = krydre_some ($treff); // legger til Twitt og face
		}

		ob_start();
		require dirname(__FILE__) . '/templates/single-result.php';
		$postout = ob_get_clean();

	} else {
		return ("Ingen post angitt!");
	}
	// Husk å returnere noe, ikke printe!
	return $postout;
}










// 		CODE FOR WIDGET IS BELOW !!


class wl_ils_widget extends WP_Widget {

    protected $widget_slug = 'wl_ils_widget';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( 'ILS Search by Webloft', $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Setter inn søkefelt for å søke og presentere fine trefflister integrert i Wordpress.', $this->get_widget_slug() )
			)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );


	} // end constructor

    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {


		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];

		// go on with your widget logic, put everything into a string and â€¦


		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// Here is where you manipulate your widget's values based on their input fields

		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;

		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget


	public function flush_widget_cache()
	{
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// Here is where you update your widget's old values with the new, incoming values

		$instance['resultatside'] = strip_tags($new_instance['resultatside']);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['katalog'] = strip_tags($new_instance['katalog']);

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// Define default values for your variables
		$defaults = array( 'resultatside' => '' , 'tittel' => 'Søk i katalogen' , 'katalog' => '2020000');
		$instance = wp_parse_args(
			(array) $instance, $defaults
		);

		// Store the values of the widget in their own variable

		$resultatside = esc_attr($instance['resultatside']);
		$title = esc_attr($instance['title']);
		$katalog = esc_attr($instance['katalog']);

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */

	public function widget_textdomain() {

	//load_plugin_textdomain( 'wl_ils', false, dirname( plugin_dir_path( __FILE__ ) ) . '/lang' );


	} // end widget_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// Define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// Define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		//wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );


	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		//wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'admin.js', __FILE__ ), array('jquery') );


	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

	// GJØRES ALLEREDE
	//wp_enqueue_style( 'wl_ils', plugins_url( 'css/wl-ils.css', __FILE__ ), false, '1.0', 'all' );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

	// GJØRES ALLEREDE
	//wp_enqueue_script( $this->get_widget_slug().'-iframe-script', plugins_url( 'js/iframeheight.js', __FILE__ ), array('jquery') );

	} // end register_widget_scripts

} // end class

// Settings page

add_action('admin_menu', 'SetupPage');
add_action('admin_init', 'RegisterSettings');

function SetupPage() {
    add_options_page("ILS Search by Webloft", "ILS Search by Webloft", "manage_options", "wl_ils_options", "wl_ils_settings_page");
}

function RegisterSettings() {
    // Add options to database if they don't already exist
    add_option("wl_ils_option_mittbibliotek", "2020000", "", "yes");
    add_option("wl_ils_option_omslagbokkilden", "0", "", "yes");
    add_option("wl_ils_option_omslagnb", "0", "", "yes");
    add_option("wl_ils_option_hamedbilder", "1", "", "yes");
    add_option("wl_ils_option_makstreff", "25", "", "yes");
    add_option("wl_ils_option_bibsysbestand", "0", "", "yes");
    add_option("wl_ils_option_enkeltpost", "", "", "yes");

    // Register settings that this form is allowed to update
    register_setting('wl_ils_options', 'wl_ils_option_mittbibliotek');
    register_setting('wl_ils_options', 'wl_ils_option_omslagbokkilden');
    register_setting('wl_ils_options', 'wl_ils_option_omslagnb');
    register_setting('wl_ils_options', 'wl_ils_option_hamedbilder');
    register_setting('wl_ils_options', 'wl_ils_option_makstreff');
    register_setting('wl_ils_options', 'wl_ils_option_bibsysbestand');
    register_setting('wl_ils_options', 'wl_ils_option_enkeltpost');
}

function wl_ils_settings_page() {
    if (!current_user_can('manage_options'))
        wp_die(__("You don't have access to this page"));
	require dirname(__FILE__) . '/templates/settings.php';
}



// All set:
add_action( 'widgets_init', create_function( '', 'register_widget("wl_ils_widget");' ) );
add_shortcode("wl-ils", "wl_ils_func");
add_shortcode("wl-ils-enkeltpost", "enkeltpost_func");
