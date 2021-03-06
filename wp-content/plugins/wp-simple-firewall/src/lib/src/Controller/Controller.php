<?php

namespace FernleafSystems\Wordpress\Plugin\Shield\Controller;

use FernleafSystems\Utilities\Data\Adapter\StdClassAdapter;
use FernleafSystems\Wordpress\Plugin\Shield;
use FernleafSystems\Wordpress\Services\Services;
use FernleafSystems\Wordpress\Services\Utilities\Options\Transient;

/**
 * Class Controller
 * @package FernleafSystems\Wordpress\Plugin\Shield\Controller
 * @property Config\ConfigVO                                        $cfg
 * @property bool                                                   $is_activating
 * @property bool                                                   $is_debug
 * @property bool                                                   $modules_loaded
 * @property bool                                                   $rebuild_options
 * @property Shield\Modules\Integrations\Lib\MainWP\Common\MainWPVO $mwpVO
 * @property bool                                                   $plugin_deactivating
 * @property bool                                                   $plugin_deleting
 * @property bool                                                   $plugin_reset
 * @property false|string                                           $file_forceoff
 * @property string                                                 $base_file
 * @property string                                                 $root_file
 * @property bool                                                   $user_can_base_permissions
 * @property Shield\Modules\Events\Lib\EventsService                $service_events
 * @property mixed[]|Shield\Modules\Base\ModCon[]                   $modules
 */
class Controller {

	use StdClassAdapter {
		__get as __adapterGet;
	}

	/**
	 * @var \stdClass
	 */
	private static $oControllerOptions;

	/**
	 * @var Controller
	 */
	public static $oInstance;

	/**
	 * @var string
	 * @deprecated 10.1
	 */
	private $sRootFile;

	/**
	 * @var string
	 * @deprecated 10.1
	 */
	private $sPluginBaseFile;

	/**
	 * @var array
	 */
	private $aRequirementsMessages;

	/**
	 * @var string
	 */
	protected static $sSessionId;

	/**
	 * @var string
	 */
	protected static $sRequestId;

	/**
	 * @var string
	 */
	protected $sAdminNoticeError = '';

	/**
	 * @var Shield\Modules\BaseShield\ModCon[]
	 */
	protected $aModules;

	/**
	 * @var Shield\Utilities\AdminNotices\Controller
	 */
	protected $oNotices;

	/**
	 * @var Shield\Modules\Events\Lib\EventsService
	 */
	private $oEventsService;

	/**
	 * @param string $event
	 * @param array  $meta
	 * @return $this
	 */
	public function fireEvent( string $event, $meta = [] ) :self {
		$this->loadEventsService()->fireEvent( $event, $meta );
		return $this;
	}

	/**
	 * @return array
	 */
	public function getAllEvents() {
		return $this->loadEventsService()->getEvents();
	}

	/**
	 * @return Shield\Modules\Events\Lib\EventsService
	 */
	public function loadEventsService() {
		if ( !isset( $this->oEventsService ) ) {
			$this->oEventsService = ( new Shield\Modules\Events\Lib\EventsService() )
				->setCon( $this );
			$this->service_events = $this->oEventsService;
		}
		return $this->oEventsService;
	}

	/**
	 * @param string $rootFile
	 * @return Controller
	 * @throws \Exception
	 */
	public static function GetInstance( $rootFile = null ) {
		if ( !isset( static::$oInstance ) ) {
			if ( empty( $rootFile ) ) {
				throw new \Exception( 'Empty root file provided for instantiation' );
			}
			static::$oInstance = new static( $rootFile );
		}
		return static::$oInstance;
	}

	/**
	 * @param string $rootFile
	 * @throws \Exception
	 */
	protected function __construct( string $rootFile ) {
		$this->sRootFile = $rootFile;
		$this->root_file = $rootFile;
		$this->base_file = $this->getPluginBaseFile();
		$this->modules = [];

		$this->loadServices();
		$this->loadConfig();

		$this->checkMinimumRequirements();
		$this->doRegisterHooks();

		( new Shield\Controller\I18n\LoadTextDomain() )
			->setCon( $this )
			->run();
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$val = $this->__adapterGet( $key );

		switch ( $key ) {

			case 'cfg':
				if ( !$val instanceof Config\ConfigVO ) {
					$val = $this->loadConfig();
				}
				break;

			case 'is_debug':
				if ( is_null( $val ) ) {
					$val = ( new Shield\Controller\Utilities\DebugMode() )
						->setCon( $this )
						->isDebugMode();
					$this->is_debug = $val;
				}
				break;

			default:
				break;
		}

		return $val;
	}

	/**
	 * @throws \Exception
	 */
	private function loadServices() {
		Services::GetInstance();
	}

	/**
	 * @return array
	 * @throws \Exception
	 * @deprecated 10.1.4
	 */
	private function readPluginSpecification() :array {
		$spec = [];
		$content = Services::Data()->readFileContentsUsingInclude( $this->getPathPluginSpec() );
		if ( !empty( $content ) ) {
			$spec = json_decode( $content, true );
			if ( empty( $spec ) || !is_array( $spec ) ) {
				throw new \Exception( 'Could not load plugin spec configuration.' );
			}
		}
		return $spec;
	}

	/**
	 * @param bool $bCheckOnlyFrontEnd
	 * @throws \Exception
	 */
	private function checkMinimumRequirements( $bCheckOnlyFrontEnd = true ) {
		if ( $bCheckOnlyFrontEnd && !is_admin() ) {
			return;
		}

		$bMeetsRequirements = true;
		$aRequirementsMessages = $this->getRequirementsMessages();

		$php = $this->cfg->requirements[ 'php' ];
		if ( !empty( $php ) ) {
			if ( version_compare( Services::Data()->getPhpVersion(), $php, '<' ) ) {
				$aRequirementsMessages[] = sprintf( 'PHP does not meet minimum version. Your version: %s.  Required Version: %s.', PHP_VERSION, $php );
				$bMeetsRequirements = false;
			}
		}

		$wp = $this->cfg->requirements[ 'wordpress' ];
		if ( !empty( $wp ) ) {
			$sWpVersion = Services::WpGeneral()->getVersion( true );
			if ( version_compare( $sWpVersion, $wp, '<' ) ) {
				$aRequirementsMessages[] = sprintf( 'WordPress does not meet minimum version. Your version: %s.  Required Version: %s.', $sWpVersion, $wp );
				$bMeetsRequirements = false;
			}
		}

		if ( !$bMeetsRequirements ) {
			$this->aRequirementsMessages = $aRequirementsMessages;
			add_action( 'admin_notices', [ $this, 'adminNoticeDoesNotMeetRequirements' ] );
			add_action( 'network_admin_notices', [ $this, 'adminNoticeDoesNotMeetRequirements' ] );
			throw new \Exception( 'Plugin does not meet minimum requirements' );
		}
	}

	public function adminNoticeDoesNotMeetRequirements() {
		$aMessages = $this->getRequirementsMessages();
		if ( !empty( $aMessages ) && is_array( $aMessages ) ) {
			$aDisplayData = [
				'strings' => [
					'requirements'     => $aMessages,
					'summary_title'    => sprintf( 'Web Hosting requirements for Plugin "%s" are not met and you should deactivate the plugin.', $this->getHumanName() ),
					'more_information' => 'Click here for more information on requirements'
				],
				'hrefs'   => [
					'more_information' => sprintf( 'https://wordpress.org/plugins/%s/faq', $this->getTextDomain() )
				]
			];

			$this->getRenderer()
				 ->setTemplate( 'notices/does-not-meet-requirements' )
				 ->setRenderVars( $aDisplayData )
				 ->display();
		}
	}

	public function adminNoticePluginFailedToLoad() {
		$aDisplayData = [
			'strings' => [
				'summary_title'    => 'Perhaps due to a failed upgrade, the Shield plugin failed to load certain component(s) - you should remove the plugin and reinstall.',
				'more_information' => $this->sAdminNoticeError
			]
		];
		$this->getRenderer()
			 ->setTemplate( 'notices/plugin-failed-to-load' )
			 ->setRenderVars( $aDisplayData )
			 ->display();
	}

	/**
	 * All our module page names are prefixed
	 * @return bool
	 */
	public function isThisPluginModuleRequest() {
		return strpos( Services::Request()->query( 'page' ), $this->prefix() ) === 0;
	}

	/**
	 * @return array
	 */
	protected function getRequirementsMessages() {
		if ( !isset( $this->aRequirementsMessages ) ) {
			$this->aRequirementsMessages = [
				'<h4>Shield Security Plugin - minimum site requirements are not met:</h4>'
			];
		}
		return $this->aRequirementsMessages;
	}

	public function onWpDeactivatePlugin() {
		do_action( $this->prefix( 'pre_deactivate_plugin' ) );
		if ( $this->isPluginAdmin() ) {
			do_action( $this->prefix( 'deactivate_plugin' ) );
			$this->plugin_deactivating = true;
			if ( apply_filters( $this->prefix( 'delete_on_deactivate' ), false ) ) {
				$this->plugin_deleting = true;
				do_action( $this->prefix( 'delete_plugin' ) );
			}
		}
		$this->deleteCronJobs();
	}

	public function onWpActivatePlugin() {
		$this->is_activating = true;
		$oModPlugin = $this->getModule_Plugin();
		if ( $oModPlugin instanceof \ICWP_WPSF_FeatureHandler_Base ) {
			$oModPlugin->setActivatedAt();
		}
	}

	/**
	 * @param string $sFilePath
	 * @return string|false
	 */
	public function getPluginCachePath( $sFilePath = '' ) {
		if ( !$this->buildPluginCacheDir() ) {
//			throw new \Exception( sprintf( 'Failed to create cache path: "%s"', $this->getPath_PluginCache() ) );
			return false;
		}
		return path_join( $this->getPath_PluginCache(), $sFilePath );
	}

	/**
	 * @return bool
	 */
	private function buildPluginCacheDir() {
		$bSuccess = false;
		$sBase = $this->getPath_PluginCache();
		$oFs = Services::WpFs();
		if ( $oFs->mkdir( $sBase ) ) {
			$sHt = path_join( $sBase, '.htaccess' );
			$sHtContent = "Options -Indexes\ndeny from all";
			if ( !$oFs->exists( $sHt ) || ( md5_file( $sHt ) != md5( $sHtContent ) ) ) {
				$oFs->putFileContent( $sHt, $sHtContent );
			}
			$sIndex = path_join( $sBase, 'index.php' );
			$sIndexContent = "<?php\nhttp_response_code(404);";
			if ( !$oFs->exists( $sIndex ) || ( md5_file( $sIndex ) != md5( $sIndexContent ) ) ) {
				$oFs->putFileContent( $sIndex, $sIndexContent );
			}
			$bSuccess = true;
		}
		return $bSuccess;
	}

	protected function doRegisterHooks() {
		register_deactivation_hook( $this->getRootFile(), [ $this, 'onWpDeactivatePlugin' ] );

		add_action( 'init', [ $this, 'onWpInit' ], -1000 );
		add_action( 'wp_loaded', [ $this, 'onWpLoaded' ] );
		add_action( 'admin_init', [ $this, 'onWpAdminInit' ] );

		add_action( 'admin_menu', [ $this, 'onWpAdminMenu' ] );
		add_action( 'network_admin_menu', [ $this, 'onWpAdminMenu' ] );

		if ( Services::WpGeneral()->isAjax() ) {
			add_action( 'wp_ajax_'.$this->prefix(), [ $this, 'ajaxAction' ] );
			add_action( 'wp_ajax_nopriv_'.$this->prefix(), [ $this, 'ajaxAction' ] );
		}

		$sBaseFile = $this->getPluginBaseFile();
		add_filter( 'all_plugins', [ $this, 'filter_hidePluginFromTableList' ] );
		add_filter( 'all_plugins', [ $this, 'doPluginLabels' ] );
		add_filter( 'plugin_action_links_'.$sBaseFile, [ $this, 'onWpPluginActionLinks' ], 50, 1 );
		add_filter( 'plugin_row_meta', [ $this, 'onPluginRowMeta' ], 50, 2 );
		add_filter( 'site_transient_update_plugins', [ $this, 'filter_hidePluginUpdatesFromUI' ] );
		add_action( 'in_plugin_update_message-'.$sBaseFile, [ $this, 'onWpPluginUpdateMessage' ] );
		add_filter( 'site_transient_update_plugins', [ $this, 'blockIncompatibleUpdates' ] );
		add_filter( 'auto_update_plugin', [ $this, 'onWpAutoUpdate' ], 500, 2 );
		add_filter( 'set_site_transient_update_plugins', [ $this, 'setUpdateFirstDetectedAt' ] );

		add_action( 'shutdown', [ $this, 'onWpShutdown' ], -1 );
		add_action( 'wp_logout', [ $this, 'onWpLogout' ] );

		// GDPR
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'onWpPrivacyRegisterExporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'onWpPrivacyRegisterEraser' ] );

		/**
		 * Support for WP-CLI and it marks the cli as complete plugin admin
		 */
		add_filter( $this->prefix( 'bypass_is_plugin_admin' ), function ( $bByPass ) {
			if ( Services::WpGeneral()->isWpCli() && $this->isPremiumActive() ) {
				$bByPass = true;
			}
			return $bByPass;
		}, PHP_INT_MAX );
	}

	public function onWpAdminInit() {
		add_action( 'admin_bar_menu', [ $this, 'onWpAdminBarMenu' ], 100 );
		add_action( 'wp_dashboard_setup', [ $this, 'onWpDashboardSetup' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'onWpEnqueueAdminCss' ], 100 );
		add_action( 'admin_enqueue_scripts', [ $this, 'onWpEnqueueAdminJs' ], 5 );

		if ( Services::Request()->query( $this->prefix( 'runtests' ) ) && $this->isPluginAdmin() ) {
			$this->runTests();
		}

		if ( !empty( $this->modules_loaded ) && !Services::WpGeneral()->isAjax()
			 && function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( $this->getHumanName(), $this->buildPrivacyPolicyContent() );
		}
	}

	/**
	 * In order to prevent certain errors when the back button is used
	 * @param array $aHeaders
	 * @return array
	 */
	public function adjustNocacheHeaders( $aHeaders ) {
		if ( is_array( $aHeaders ) && !empty( $aHeaders[ 'Cache-Control' ] ) ) {
			$aHs = array_map( 'trim', explode( ',', $aHeaders[ 'Cache-Control' ] ) );
			$aHs[] = 'no-store';
			$aHeaders[ 'Cache-Control' ] = implode( ', ', array_unique( $aHs ) );
		}
		return $aHeaders;
	}

	public function onWpInit() {
		$this->getMeetsBasePermissions();
		add_action( 'wp_enqueue_scripts', [ $this, 'onWpEnqueueFrontendCss' ], 99 );

		if ( $this->isModulePage() ) {
			add_filter( 'nocache_headers', [ $this, 'adjustNocacheHeaders' ] );
		}
	}

	/**
	 * Only set to rebuild as required if you're doing so at the same point in the WordPress load each time.
	 * Certain plugins can modify the ID at different points in the load.
	 * @return string - the unique, never-changing site install ID.
	 */
	public function getSiteInstallationId() {
		$oWP = Services::WpGeneral();
		$sOptKey = $this->prefixOption( 'install_id' );

		$mStoredID = $oWP->getOption( $sOptKey );
		if ( is_array( $mStoredID ) && !empty( $mStoredID[ 'id' ] ) ) {
			$sID = $mStoredID[ 'id' ];
			$bUpdate = true;
		}
		elseif ( is_string( $mStoredID ) && strpos( $mStoredID, ':' ) ) {
			$sID = explode( ':', $mStoredID, 2 )[ 1 ];
			$bUpdate = true;
		}
		else {
			$sID = $mStoredID;
			$bUpdate = false;
		}

		if ( empty( $sID ) || !is_string( $sID ) || ( strlen( $sID ) !== 40 && !\Ramsey\Uuid\Uuid::isValid( $sID ) ) ) {
			try {
				$sID = \Ramsey\Uuid\Uuid::uuid4()->toString();
			}
			catch ( \Exception $e ) {
				$sID = sha1( uniqid( $oWP->getHomeUrl( '', true ), true ) );
			}
			$bUpdate = true;
		}

		if ( $bUpdate ) {
			$oWP->updateOption( $sOptKey, $sID );
		}

		return $sID;
	}

	/**
	 * TODO: Use to set ID after license verify where applicable
	 * @param string $sID
	 */
	public function setSiteInstallID( $sID ) {
		if ( !empty( $sID ) && ( \Ramsey\Uuid\Uuid::isValid( $sID ) ) ) {
			Services::WpGeneral()->updateOption( $this->prefixOption( 'install_id' ), $sID );
		}
	}

	public function onWpLoaded() {
		$this->getAdminNotices();
		$this->initCrons();
	}

	protected function initCrons() {
		( new Shield\Crons\HourlyCron() )
			->setCon( $this )
			->run();
		( new Shield\Crons\DailyCron() )
			->setCon( $this )
			->run();
	}

	public function onWpAdminMenu() {
		if ( $this->isValidAdminArea() ) {
			$this->createPluginMenu();
		}
	}

	/**
	 * @param \WP_Admin_Bar $oAdminBar
	 */
	public function onWpAdminBarMenu( $oAdminBar ) {
		$bShow = apply_filters( $this->prefix( 'show_admin_bar_menu' ),
			$this->isValidAdminArea( true ) && $this->cfg->properties[ 'show_admin_bar_menu' ]
		);
		if ( $bShow ) {
			$aMenuItems = apply_filters( $this->prefix( 'admin_bar_menu_items' ), [] );
			if ( !empty( $aMenuItems ) && is_array( $aMenuItems ) ) {
				$nCountWarnings = 0;
				foreach ( $aMenuItems as $aMenuItem ) {
					$nCountWarnings += isset( $aMenuItem[ 'warnings' ] ) ? $aMenuItem[ 'warnings' ] : 0;
				}

				$sNodeId = $this->prefix( 'adminbarmenu' );
				$oAdminBar->add_node( [
					'id'    => $sNodeId,
					'title' => $this->getHumanName()
							   .sprintf( '<div class="wp-core-ui wp-ui-notification shield-counter"><span aria-hidden="true">%s</span></div>', $nCountWarnings ),
				] );
				foreach ( $aMenuItems as $aMenuItem ) {
					$aMenuItem[ 'parent' ] = $sNodeId;
					$oAdminBar->add_menu( $aMenuItem );
				}
			}
		}
	}

	public function onWpDashboardSetup() {
		$show = apply_filters( $this->prefix( 'show_dashboard_widget' ),
			$this->isValidAdminArea() && $this->cfg->properties[ 'show_dashboard_widget' ]
		);
		if ( $show ) {
			wp_add_dashboard_widget(
				$this->prefix( 'dashboard_widget' ),
				apply_filters( $this->prefix( 'dashboard_widget_title' ), $this->getHumanName() ),
				function () {
					do_action( $this->prefix( 'dashboard_widget_content' ) );
				}
			);
		}
	}

	/**
	 * @return Shield\Utilities\AdminNotices\Controller
	 */
	public function getAdminNotices() {
		if ( !isset( $this->oNotices ) ) {
			if ( $this->getIsPage_PluginAdmin() ) {
				remove_all_filters( 'admin_notices' );
				remove_all_filters( 'network_admin_notices' );
			}
			$this->oNotices = ( new Shield\Utilities\AdminNotices\Controller() )->setCon( $this );
		}
		return $this->oNotices;
	}

	/**
	 * @param string $sAction
	 * @return array
	 */
	public function getNonceActionData( $sAction = '' ) {
		return [
			'action'     => $this->prefix(), //wp ajax doesn't work without this.
			'exec'       => $sAction,
			'exec_nonce' => wp_create_nonce( $sAction ),
			//			'rand'       => wp_rand( 10000, 99999 )
		];
	}

	public function ajaxAction() {
		$nonceAction = Services::Request()->request( 'exec' );
		check_ajax_referer( $nonceAction, 'exec_nonce' );

		ob_start();
		$response = apply_filters(
			$this->prefix( Services::WpUsers()->isUserLoggedIn() ? 'ajaxAuthAction' : 'ajaxNonAuthAction' ),
			[], $nonceAction
		);
		$noise = ob_get_clean();

		if ( is_array( $response ) && isset( $response[ 'success' ] ) ) {
			$bSuccess = $response[ 'success' ];
		}
		else {
			$bSuccess = false;
			$response = [];
		}

		wp_send_json(
			[
				'success' => $bSuccess,
				'data'    => $response,
				'noise'   => $noise
			]
		);
	}

	/**
	 * @return bool
	 */
	protected function createPluginMenu() {
		$menu = $this->cfg->menu;

		if ( apply_filters( $this->prefix( 'filter_hidePluginMenu' ), !$menu[ 'show' ] ) ) {
			return true;
		}

		if ( $menu[ 'top_level' ] ) {

			$labels = $this->getLabels();
			$sMenuTitle = empty( $labels[ 'MenuTitle' ] ) ? $menu[ 'title' ] : $labels[ 'MenuTitle' ];
			if ( is_null( $sMenuTitle ) ) {
				$sMenuTitle = $this->getHumanName();
			}

			$sMenuIcon = $this->getPluginUrl_Image( $menu[ 'icon_image' ] );
			$sIconUrl = empty( $labels[ 'icon_url_16x16' ] ) ? $sMenuIcon : $labels[ 'icon_url_16x16' ];

			$sFullParentMenuId = $this->getPluginPrefix();
			add_menu_page(
				$this->getHumanName(),
				$sMenuTitle,
				$this->getBasePermissions(),
				$sFullParentMenuId,
				[ $this, $menu[ 'callback' ] ],
				$sIconUrl
			);

			if ( $menu[ 'has_submenu' ] ) {

				$menuItems = apply_filters( $this->prefix( 'submenu_items' ), [] );
				if ( !empty( $menuItems ) ) {
					foreach ( $menuItems as $sMenuTitle => $aMenu ) {
						list( $sMenuItemText, $sMenuItemId, $aMenuCallBack, $bShowItem ) = $aMenu;
						add_submenu_page(
							$bShowItem ? $sFullParentMenuId : null,
							$sMenuTitle,
							$sMenuItemText,
							$this->getBasePermissions(),
							$sMenuItemId,
							$aMenuCallBack
						);
					}
				}
			}

			if ( $menu[ 'do_submenu_fix' ] ) {
				$this->fixSubmenu();
			}
		}
		return true;
	}

	protected function fixSubmenu() {
		global $submenu;
		$sFullParentMenuId = $this->getPluginPrefix();
		if ( isset( $submenu[ $sFullParentMenuId ] ) ) {
			unset( $submenu[ $sFullParentMenuId ][ 0 ] );
		}
	}

	/**
	 * Displaying all views now goes through this central function and we work out
	 * what to display based on the name of current hook/filter being processed.
	 */
	public function onDisplayTopMenu() {
	}

	/**
	 * @param array  $aPluginMeta
	 * @param string $sPluginFile
	 * @return array
	 */
	public function onPluginRowMeta( $aPluginMeta, $sPluginFile ) {

		if ( $sPluginFile == $this->getPluginBaseFile() ) {
			$sTemplate = '<strong><a href="%s" target="_blank">%s</a></strong>';
			foreach ( $this->cfg->plugin_meta as $aHref ) {
				array_push( $aPluginMeta, sprintf( $sTemplate, $aHref[ 'href' ], $aHref[ 'name' ] ) );
			}
		}
		return $aPluginMeta;
	}

	/**
	 * @param array $aActionLinks
	 * @return array
	 */
	public function onWpPluginActionLinks( $aActionLinks ) {

		if ( $this->isValidAdminArea() ) {

			if ( array_key_exists( 'edit', $aActionLinks ) ) {
				unset( $aActionLinks[ 'edit' ] );
			}

			$links = $this->cfg->action_links[ 'add' ];
			if ( is_array( $links ) ) {

				$bPro = $this->isPremiumActive();
				$oDP = Services::Data();
				$sLinkTemplate = '<a href="%s" target="%s" title="%s">%s</a>';
				foreach ( $links as $aLink ) {
					$aLink = array_merge(
						[
							'highlight' => false,
							'show'      => 'always',
							'name'      => '',
							'title'     => '',
							'href'      => '',
							'target'    => '_top',
						],
						$aLink
					);

					$sShow = $aLink[ 'show' ];
					$bShow = ( $sShow == 'always' ) || ( $bPro && $sShow == 'pro' ) || ( !$bPro && $sShow == 'free' );
					if ( !$oDP->isValidWebUrl( $aLink[ 'href' ] ) && method_exists( $this, $aLink[ 'href' ] ) ) {
						$aLink[ 'href' ] = $this->{$aLink[ 'href' ]}();
					}

					if ( !$bShow || !$oDP->isValidWebUrl( $aLink[ 'href' ] )
						 || empty( $aLink[ 'name' ] ) || empty( $aLink[ 'href' ] ) ) {
						continue;
					}

					$aLink[ 'name' ] = __( $aLink[ 'name' ], 'wp-simple-firewall' );

					$sLink = sprintf( $sLinkTemplate, $aLink[ 'href' ], $aLink[ 'target' ], $aLink[ 'title' ], $aLink[ 'name' ] );
					if ( $aLink[ 'highlight' ] ) {
						$sLink = sprintf( '<span style="font-weight: bold;">%s</span>', $sLink );
					}

					$aActionLinks = array_merge(
						[ $this->prefix( sanitize_key( $aLink[ 'name' ] ) ) => $sLink ],
						$aActionLinks
					);
				}
			}
		}
		return $aActionLinks;
	}

	public function onWpEnqueueFrontendCss() {
		$includes = $this->cfg->includes[ 'frontend' ];
		if ( isset( $includes[ 'css' ] ) && !empty( $includes[ 'css' ] ) && is_array( $includes[ 'css' ] ) ) {

			$aDeps = [];
			foreach ( $includes[ 'css' ] as $sAsset ) {
				$sUrl = $this->getPluginUrl_Css( $sAsset );
				if ( !empty( $sUrl ) ) {
					$sAsset = $this->prefix( $sAsset );
					wp_register_style( $sAsset, $sUrl, $aDeps, $this->getVersion() );
					wp_enqueue_style( $sAsset );
					$aDeps[] = $aDeps;
				}
			}
		}
	}

	public function onWpEnqueueAdminJs() {

		$aIncludes = [];
		if ( $this->getIsPage_PluginAdmin() ) {
			$includes = $this->cfg->includes[ 'plugin_admin' ];
			if ( !empty( $includes[ 'js' ] ) && is_array( $includes[ 'js' ] ) ) {
				$aIncludes = $includes[ 'js' ];
			}
		}
		elseif ( $this->isValidAdminArea() ) {
			$includes = $this->cfg->includes[ 'admin' ];
			if ( !empty( $includes[ 'js' ] ) && is_array( $includes[ 'js' ] ) ) {
				$aIncludes = $includes[ 'js' ];
			}
		}

		$nativeWP = [ 'jquery' ];

		$aDeps = [];
		foreach ( $aIncludes as $asset ) {

			// Built-in handles
			if ( in_array( $asset, $nativeWP ) ) {
				if ( wp_script_is( $asset, 'registered' ) ) {
					wp_enqueue_script( $asset );
					$aDeps[] = $asset;
				}
			}
			else {
				$sUrl = $this->getPluginUrl_Js( $asset );
				if ( !empty( $sUrl ) ) {
					$asset = $this->prefix( $asset );
					wp_register_script( $asset, $sUrl, $aDeps, $this->getVersion() );
					wp_enqueue_script( $asset );
					$aDeps[] = $asset;
				}
			}
		}
	}

	public function onWpEnqueueAdminCss() {

		$aIncludes = [];
		if ( $this->getIsPage_PluginAdmin() ) {
			$includes = $this->cfg->includes[ 'plugin_admin' ];
			if ( !empty( $includes[ 'css' ] ) && is_array( $includes[ 'css' ] ) ) {
				$aIncludes = $includes[ 'css' ];
			}
		}
		elseif ( $this->isValidAdminArea() ) {
			$includes = $this->cfg->includes[ 'admin' ];
			if ( !empty( $includes[ 'css' ] ) && is_array( $includes[ 'css' ] ) ) {
				$aIncludes = $includes[ 'css' ];
			}
		}

		$aDeps = [];
		foreach ( $aIncludes as $asset ) {
			$sUrl = $this->getPluginUrl_Css( $asset );
			if ( !empty( $sUrl ) ) {
				$asset = $this->prefix( $asset );
				wp_register_style( $asset, $sUrl, $aDeps, $this->getVersion() );
				wp_enqueue_style( $asset );
				$aDeps[] = $asset;
			}
		}
	}

	/**
	 * Displays a message in the plugins listing when a plugin has an update available.
	 */
	public function onWpPluginUpdateMessage() {
		$sMessage = __( 'Update Now To Keep Your Security Current With The Latest Features.', 'wp-simple-firewall' );
		if ( empty( $sMessage ) ) {
			$sMessage = '';
		}
		else {
			$sMessage = sprintf(
				' <span class="%s plugin_update_message">%s</span>',
				$this->getPluginPrefix(),
				$sMessage
			);
		}
		echo $sMessage;
	}

	/**
	 * Prevents upgrades to Shield versions when the system PHP version is too old.
	 * @param \stdClass $oUpdates
	 * @return \stdClass
	 */
	public function blockIncompatibleUpdates( $oUpdates ) {
		$sFile = $this->getPluginBaseFile();
		if ( !empty( $oUpdates->response ) && isset( $oUpdates->response[ $sFile ] ) ) {
			$aUpgradeReqs = $this->getPluginSpec()[ 'upgrade_reqs' ];
			if ( is_array( $aUpgradeReqs ) ) {
				foreach ( $aUpgradeReqs as $sShieldVer => $aReqs ) {
					$bNeedsHidden = version_compare( $oUpdates->response[ $sFile ]->new_version, $sShieldVer, '>=' )
									&& (
										!Services::Data()->getPhpVersionIsAtLeast( $aReqs[ 'php' ] )
										|| !Services::WpGeneral()->getWordpressIsAtLeastVersion( $aReqs[ 'wp' ] )
									);
					if ( $bNeedsHidden ) {
						unset( $oUpdates->response[ $sFile ] );
						break;
					}
				}
			}
		}
		return $oUpdates;
	}

	/**
	 * This will hook into the saving of plugin update information and if there is an update for this plugin, it'll add
	 * a data stamp to state when the update was first detected.
	 * @param \stdClass $data
	 * @return \stdClass
	 */
	public function setUpdateFirstDetectedAt( $data ) {

		if ( !empty( $data ) && !empty( $data->response ) && isset( $data->response[ $this->getPluginBaseFile() ] ) ) {
			// i.e. update available

			$new = Services::WpPlugins()->getUpdateNewVersion( $this->getPluginBaseFile() );
			if ( !empty( $new ) && isset( $this->cfg ) ) {
				$updates = $this->cfg->update_first_detected;
				if ( count( $updates ) > 3 ) {
					$updates = [];
				}
				if ( !isset( $updates[ $new ] ) ) {
					$updates[ $new ] = Services::Request()->ts();
				}
				$this->cfg->update_first_detected = $updates;
			}
		}

		return $data;
	}

	/**
	 * This is a filter method designed to say whether WordPress plugin upgrades should be permitted,
	 * based on the plugin settings.
	 * @param bool          $isAutoUpdate
	 * @param string|object $mItem
	 * @return bool
	 */
	public function onWpAutoUpdate( $isAutoUpdate, $mItem ) {
		$WP = Services::WpGeneral();
		$oWpPlugins = Services::WpPlugins();

		$sFile = $WP->getFileFromAutomaticUpdateItem( $mItem );

		// The item in question is this plugin...
		if ( $sFile === $this->getPluginBaseFile() ) {
			$autoupdateSelf = $this->cfg->properties[ 'autoupdate' ];

			if ( !$WP->isRunningAutomaticUpdates() && $autoupdateSelf == 'confidence' ) {
				$autoupdateSelf = 'yes'; // so that we appear to be automatically updating
			}

			$new = $oWpPlugins->getUpdateNewVersion( $sFile );

			switch ( $autoupdateSelf ) {

				case 'yes' :
					$isAutoUpdate = true;
					break;

				case 'block' :
					$isAutoUpdate = false;
					break;

				case 'confidence' :
					$isAutoUpdate = false;
					if ( !empty( $new ) ) {
						$firstDetected = $this->cfg->update_first_detected[ $new ] ?? 0;
						$availableFor = Services::Request()->ts() - $firstDetected;
						$isAutoUpdate = $firstDetected > 0
										&& $availableFor > DAY_IN_SECONDS*$this->cfg->properties[ 'autoupdate_days' ];
					}
					break;

				case 'pass' :
				default:
					break;
			}
		}
		return $isAutoUpdate;
	}

	/**
	 * @param array $aPlugins
	 * @return array
	 */
	public function doPluginLabels( $aPlugins ) {
		$aLabelData = $this->getLabels();
		if ( empty( $aLabelData ) ) {
			return $aPlugins;
		}

		$sPluginFile = $this->getPluginBaseFile();
		// For this plugin, overwrite any specified settings
		if ( array_key_exists( $sPluginFile, $aPlugins ) ) {
			foreach ( $aLabelData as $sLabelKey => $sLabel ) {
				$aPlugins[ $sPluginFile ][ $sLabelKey ] = $sLabel;
			}
		}

		return $aPlugins;
	}

	public function getLabels() :array {

		$labels = array_map(
			'stripslashes',
			apply_filters( $this->prefix( 'plugin_labels' ), $this->cfg->labels )
		);

		$oDP = Services::Data();
		foreach ( [ '16x16', '32x32', '128x128' ] as $dimension ) {
			$key = 'icon_url_'.$dimension;
			if ( !empty( $labels[ $key ] ) && !$oDP->isValidWebUrl( $labels[ $key ] ) ) {
				$labels[ $key ] = $this->getPluginUrl_Image( $labels[ $key ] );
			}
		}

		return $labels;
	}

	public function onWpShutdown() {
		$this->getSiteInstallationId();
		do_action( $this->prefix( 'pre_plugin_shutdown' ) );
		do_action( $this->prefix( 'plugin_shutdown' ) );
		$this->saveCurrentPluginControllerOptions();
		$this->deleteFlags();
	}

	public function onWpLogout() {
		if ( $this->hasSessionId() ) {
			$this->clearSession();
		}
	}

	protected function deleteFlags() {
		$FS = Services::WpFs();
		if ( $FS->exists( $this->getPath_Flags( 'rebuild' ) ) ) {
			$FS->deleteFile( $this->getPath_Flags( 'rebuild' ) );
		}
		if ( $this->getIsResetPlugin() ) {
			$FS->deleteFile( $this->getPath_Flags( 'reset' ) );
		}
	}

	/**
	 * Added to a WordPress filter ('all_plugins') which will remove this particular plugin from the
	 * list of all plugins based on the "plugin file" name.
	 * @param array $plugins
	 * @return array
	 */
	public function filter_hidePluginFromTableList( $plugins ) {
		if ( apply_filters( $this->prefix( 'hide_plugin' ), false ) ) {
			unset( $plugins[ $this->getPluginBaseFile() ] );
		}
		return $plugins;
	}

	/**
	 * Added to the WordPress filter ('site_transient_update_plugins') in order to remove visibility of updates
	 * from the WordPress Admin UI.
	 * In order to ensure that WordPress still checks for plugin updates it will not remove this plugin from
	 * the list of plugins if DOING_CRON is set to true.
	 * @param \stdClass $plugins
	 * @return \stdClass
	 */
	public function filter_hidePluginUpdatesFromUI( $plugins ) {
		if ( !Services::WpGeneral()->isCron() && apply_filters( $this->prefix( 'hide_plugin_updates' ), false ) ) {
			unset( $plugins->response[ $this->getPluginBaseFile() ] );
		}
		return $plugins;
	}

	/**
	 * @param string $suffix
	 * @param string $glue
	 * @return string
	 */
	public function prefix( $suffix = '', $glue = '-' ) {
		$sPrefix = $this->getPluginPrefix( $glue );

		if ( $suffix == $sPrefix || strpos( $suffix, $sPrefix.$glue ) === 0 ) { //it already has the full prefix
			return $suffix;
		}

		return sprintf( '%s%s%s', $sPrefix, empty( $suffix ) ? '' : $glue, empty( $suffix ) ? '' : $suffix );
	}

	public function prefixOption( string $suffix = '' ) :string {
		return $this->prefix( $suffix, '_' );
	}

	/**
	 * @return Config\ConfigVO
	 * @throws \Exception
	 */
	private function loadConfig() :Config\ConfigVO {
		$this->cfg = ( new Config\Ops\LoadConfig( $this->getPathPluginSpec(), $this->getConfigStoreKey() ) )
			->setCon( $this )
			->run();
		$this->rebuild_options = $this->cfg->rebuilt;
		return $this->cfg;
	}

	/**
	 * @return array
	 * @deprecated 10.2
	 */
	public function getPluginSpec() {
		if ( isset( $this->cfg ) ) {
			return $this->cfg->getRawDataAsArray();
		}
		return $this->getPluginControllerOptions()->plugin_spec;
	}

	/**
	 * @param string $key
	 * @return array
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_ActionLinks( string $key ) :array {
		$aData = $this->getPluginSpec()[ 'action_links' ];
		return $aData[ $key ] ?? [];
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_Include( string $key ) {
		$aData = $this->getPluginSpec()[ 'includes' ];
		return $aData[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @return array|string
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_Labels( string $key = '' ) {
		$oSpec = $this->getPluginSpec();
		$aLabels = isset( $oSpec[ 'labels' ] ) ? $oSpec[ 'labels' ] : [];

		if ( empty( $key ) ) {
			return $aLabels;
		}

		return isset( $oSpec[ 'labels' ][ $key ] ) ? $oSpec[ 'labels' ][ $key ] : null;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_Menu( string $key ) {
		$aData = $this->getPluginSpec()[ 'menu' ];
		return $aData[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @return string|null
	 */
	public function getPluginSpec_Path( string $key ) {
		if ( isset( $this->cfg ) ) {
			return $this->cfg->paths[ $key ];
		}
		$aData = $this->getPluginSpec()[ 'paths' ];
		return $aData[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 */
	protected function getCfgProperty( string $key ) {
		return $this->cfg->properties[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 * @deprecated 10.1.4 - getCfgProperty()
	 */
	protected function getPluginSpec_Property( string $key ) {
		if ( isset( $this->cfg ) ) {
			return $this->cfg->properties[ $key ];
		}
		$data = $this->getPluginSpec()[ 'properties' ];
		return $data[ $key ] ?? null;
	}

	/**
	 * @return array
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_PluginMeta() {
		$aSpec = $this->getPluginSpec();
		return ( isset( $aSpec[ 'plugin_meta' ] ) && is_array( $aSpec[ 'plugin_meta' ] ) ) ? $aSpec[ 'plugin_meta' ] : [];
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 * @deprecated 10.1.4
	 */
	protected function getPluginSpec_Requirement( string $key ) {
		$aData = $this->getPluginSpec()[ 'requirements' ];
		return $aData[ $key ] ?? null;
	}

	public function getBasePermissions() :string {
		if ( isset( $this->cfg ) ) {
			return $this->cfg->properties[ 'base_permissions' ];
		}
		return $this->getPluginSpec_Property( 'base_permissions' );
	}

	public function isValidAdminArea( bool $bCheckUserPerms = false ) :bool {
		if ( $bCheckUserPerms && did_action( 'init' ) && !$this->isPluginAdmin() ) {
			return false;
		}

		$oWp = Services::WpGeneral();
		if ( !$oWp->isMultisite() && is_admin() ) {
			return true;
		}
		elseif ( $oWp->isMultisite() && $this->getIsWpmsNetworkAdminOnly() && ( is_network_admin() || $oWp->isAjax() ) ) {
			return true;
		}
		return false;
	}

	public function isModulePage() :bool {
		return strpos( Services::Request()->query( 'page' ), $this->prefix() ) === 0;
	}

	/**
	 * only ever consider after WP INIT (when a logged-in user is recognised)
	 */
	public function isPluginAdmin() :bool {
		return apply_filters( $this->prefix( 'bypass_is_plugin_admin' ), false )
			   || ( $this->getMeetsBasePermissions() // takes care of did_action('init)
					&& apply_filters( $this->prefix( 'is_plugin_admin' ), true )
			   );
	}

	/**
	 * DO NOT CHANGE THIS IMPLEMENTATION.
	 * We call this as early as possible so that the
	 * current_user_can() never gets caught up in an infinite loop of permissions checking
	 */
	public function getMeetsBasePermissions() :bool {
		if ( did_action( 'init' ) && !isset( $this->user_can_base_permissions ) ) {
			$this->user_can_base_permissions = current_user_can( $this->getBasePermissions() );
		}
		return (bool)$this->user_can_base_permissions;
	}

	public function getOptionStoragePrefix() :string {
		return $this->getPluginPrefix( '_' ).'_';
	}

	public function getPluginPrefix( string $sGlue = '-' ) :string {
		return sprintf( '%s%s%s', $this->getParentSlug(), $sGlue, $this->getPluginSlug() );
	}

	/**
	 * Default is to take the 'Name' from the labels section but can override with "human_name" from property section.
	 * @return string
	 */
	public function getHumanName() {
		$labels = $this->getLabels();
		return empty( $labels[ 'Name' ] ) ? $this->getPluginSpec_Property( 'human_name' ) : $labels[ 'Name' ];
	}

	/**
	 * @return string
	 */
	public function isLoggingEnabled() {
		return $this->getPluginSpec_Property( 'logging_enabled' );
	}

	/**
	 * @return bool
	 */
	public function getIsPage_PluginAdmin() {
		return ( strpos( Services::WpGeneral()->getCurrentWpAdminPage(), $this->getPluginPrefix() ) === 0 );
	}

	public function getIsPage_PluginMainDashboard() :bool {
		return Services::WpGeneral()->getCurrentWpAdminPage() === $this->getPluginPrefix();
	}

	/**
	 * @return bool
	 * @deprecated 10.1.4
	 */
	public function getIsRebuildOptionsFromFile() :bool {
		return $this->rebuild_options;
	}

	public function getIsResetPlugin() :bool {
		if ( !isset( $this->plugin_reset ) ) {
			$this->plugin_reset = (bool)Services::WpFs()->isFile( $this->getPath_Flags( 'reset' ) );
		}
		return (bool)$this->plugin_reset;
	}

	/**
	 * @return bool
	 */
	public function getIsWpmsNetworkAdminOnly() {
		return $this->getPluginSpec_Property( 'wpms_network_admin_only' );
	}

	public function getParentSlug() :string {
		return $this->getPluginSpec_Property( 'slug_parent' );
	}

	public function getPluginBaseFile() :string {
		if ( !isset( $this->base_file ) ) {
			$this->base_file = plugin_basename( $this->getRootFile() );
		}
		return $this->base_file;
	}

	public function getPluginSlug() :string {
		return $this->getPluginSpec_Property( 'slug_plugin' );
	}

	public function getPluginUrl( string $path = '' ) :string {
		return add_query_arg( [ 'ver' => $this->getVersion() ], plugins_url( $path, $this->getRootFile() ) );
	}

	public function getPluginUrl_Asset( string $asset ) :string {
		$url = '';
		$sAssetPath = $this->getPath_Assets( $asset );
		if ( Services::WpFs()->exists( $sAssetPath ) ) {
			$url = $this->getPluginUrl( $this->getPluginSpec_Path( 'assets' ).'/'.$asset );
			return Services::Includes()->addIncludeModifiedParam( $url, $sAssetPath );
		}
		return $url;
	}

	public function getPluginUrl_Css( string $asset ) :string {
		return $this->getPluginUrl_Asset( 'css/'.Services::Data()->addExtensionToFilePath( $asset, 'css' ) );
	}

	public function getPluginUrl_Image( string $asset ) :string {
		return $this->getPluginUrl_Asset( 'images/'.$asset );
	}

	public function getPluginUrl_Js( string $asset ) :string {
		return $this->getPluginUrl_Asset( 'js/'.Services::Data()->addExtensionToFilePath( $asset, 'js' ) );
	}

	public function getPluginUrl_AdminMainPage() :string {
		return $this->getModule_Plugin()->getUrl_AdminPage();
	}

	public function getPath_Assets( string $asset = '' ) :string {
		$base = path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'assets' ) );
		return empty( $asset ) ? $base : path_join( $base, $asset );
	}

	public function getPath_Flags( string $flag = '' ) :string {
		$base = path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'flags' ) );
		return empty( $flag ) ? $base : path_join( $base, $flag );
	}

	/**
	 * @param string $sTmpFile
	 * @return string
	 */
	public function getPath_Temp( $sTmpFile = '' ) {
		$sTempPath = null;

		$sBase = path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'temp' ) );
		if ( Services::WpFs()->mkdir( $sBase ) ) {
			$sTempPath = $sBase;
		}
		return empty( $sTmpFile ) ? $sTempPath : path_join( $sTempPath, $sTmpFile );
	}

	public function getPath_AssetCss( string $asset = '' ) :string {
		return $this->getPath_Assets( 'css/'.$asset );
	}

	public function getPath_AssetJs( string $asset = '' ) :string {
		return $this->getPath_Assets( 'js/'.$asset );
	}

	public function getPath_AssetImage( string $asset = '' ) :string {
		return $this->getPath_Assets( 'images/'.$asset );
	}

	public function getPath_ConfigFile( string $slug ) :string {
		return $this->getPath_SourceFile( sprintf( 'config/feature-%s.php', $slug ) );
	}

	public function getPath_Languages() :string {
		return trailingslashit( path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'languages' ) ) );
	}

	public function getPath_LibFile( string $libFile ) :string {
		return $this->getPath_SourceFile( 'lib/'.$libFile );
	}

	public function getPath_Autoload() :string {
		return $this->getPath_SourceFile( $this->getPluginSpec_Path( 'autoload' ) );
	}

	public function getPath_PluginCache() :string {
		return path_join( WP_CONTENT_DIR, $this->getPluginSpec_Path( 'cache' ) );
	}

	public function getPath_SourceFile( string $sourceFile ) :string {
		$base = path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'source' ) );
		return empty( $sourceFile ) ? $base : path_join( $base, $sourceFile );
	}

	public function getPath_Templates() :string {
		return path_join( $this->getRootDir(), $this->getPluginSpec_Path( 'templates' ) ).'/';
	}

	public function getPath_TemplatesFile( string $sTemplate ) :string {
		return path_join( $this->getPath_Templates(), $sTemplate );
	}

	private function getPathPluginSpec() :string {
		return path_join( $this->getRootDir(), 'plugin-spec.php' );
	}

	public function getRootDir() :string {
		return dirname( $this->getRootFile() ).DIRECTORY_SEPARATOR;
	}

	public function getRootFile() :string {
		if ( empty( $this->root_file ) ) {
			$VO = ( new \FernleafSystems\Wordpress\Services\Utilities\WpOrg\Plugin\Files() )
				->findPluginFromFile( __FILE__ );
			if ( $VO instanceof \FernleafSystems\Wordpress\Services\Core\VOs\WpPluginVo ) {
				$this->root_file = path_join( WP_PLUGIN_DIR, $VO->file );
			}
			else {
				$this->root_file = __FILE__;
			}
		}
		return $this->root_file;
	}

	/**
	 * @return int
	 */
	public function getReleaseTimestamp() {
		return $this->getPluginSpec_Property( 'release_timestamp' );
	}

	public function getTextDomain() :string {
		return $this->getPluginSpec_Property( 'text_domain' );
	}

	/**
	 * @return string
	 */
	public function getBuild() {
		return $this->getPluginSpec_Property( 'build' );
	}

	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->getPluginSpec_Property( 'version' );
	}

	/**
	 * @return string
	 * @deprecated 10.1.4
	 */
	public function getPreviousVersion() :string {
		$opts = $this->getPluginControllerOptions();
		if ( empty( $opts->previous_version ) ) {
			$opts->previous_version = $this->getVersion();
		}
		return $opts->previous_version;
	}

	public function getVersionNumeric() :int {
		$parts = explode( '.', $this->getVersion() );
		return (int)( $parts[ 0 ]*100 + $parts[ 1 ]*10 + $parts[ 2 ] );
	}

	public function getShieldAction() :string {
		$action = sanitize_key( Services::Request()->query( 'shield_action', '' ) );
		return empty( $action ) ? '' : $action;
	}

	/**
	 * @return \stdClass
	 */
	public function getPluginControllerOptions() {
		return self::$oControllerOptions;
	}

	protected function deleteCronJobs() {
		$oWpCron = Services::WpCron();
		$aCrons = $oWpCron->getCrons();

		$sPattern = sprintf( '#^(%s|%s)#', $this->getParentSlug(), $this->getPluginSlug() );
		foreach ( $aCrons as $aCron ) {
			if ( is_array( $aCrons ) ) {
				foreach ( $aCron as $sKey => $aCronEntry ) {
					if ( is_string( $sKey ) && preg_match( $sPattern, $sKey ) ) {
						$oWpCron->deleteCronJob( $sKey );
					}
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isPremiumExtensionsEnabled() {
		return (bool)$this->getPluginSpec_Property( 'enable_premium' );
	}

	public function isPremiumActive() :bool {
		return $this->getModule_License()->getLicenseHandler()->hasValidWorkingLicense();
	}

	public function isRelabelled() :bool {
		return (bool)apply_filters( $this->prefix( 'is_relabelled' ), false );
	}

	protected function saveCurrentPluginControllerOptions() {
		$WP = Services::WpGeneral();
		add_filter( $this->prefix( 'bypass_is_plugin_admin' ), '__return_true' );

		if ( $this->plugin_deleting ) {
			Transient::Delete( $this->getConfigStoreKey() );
		}
		elseif ( isset( $this->cfg ) ) {
			Config\Ops\Save::ToWp( $this->cfg, $this->getConfigStoreKey() );
		}
		else {
			/* @deprecated 10.1.4 */
			$WP->updateOption(
				$this->getPluginControllerOptionsKey(),
				$this->getPluginControllerOptions()
			);
		}
		remove_filter( $this->prefix( 'bypass_is_plugin_admin' ), '__return_true' );
	}

	/**
	 * This should always be used to modify or delete the options as it works within the Admin Access Permission system.
	 * @param \stdClass|bool $oOptions
	 * @return $this
	 */
	protected function setPluginControllerOptions( $oOptions ) {
		self::$oControllerOptions = $oOptions;
		return $this;
	}

	private function getConfigStoreKey() :string {
		return 'aptoweb_controller_'.substr( md5( get_class() ), 0, 6 );
	}

	/**
	 * @return string
	 * @deprecated 10.1.4
	 */
	private function getPluginControllerOptionsKey() {
		return strtolower( get_class() );
	}

	public function deactivateSelf() {
		if ( $this->isPluginAdmin() && function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->getPluginBaseFile() );
		}
	}

	public function clearSession() {
		Services::Response()->cookieDelete( $this->getSessionCookieID() );
		self::$sSessionId = null;
	}

	/**
	 * @return $this
	 */
	public function deleteForceOffFile() {
		if ( $this->getIfForceOffActive() ) {
			Services::WpFs()->deleteFile( $this->getForceOffFilePath() );
			unset( $this->file_forceoff );
			clearstatcache();
		}
		return $this;
	}

	public function getIfForceOffActive() :bool {
		return $this->getForceOffFilePath() !== false;
	}

	/**
	 * @return false|string
	 */
	protected function getForceOffFilePath() {
		if ( !isset( $this->file_forceoff ) ) {
			$FS = Services::WpFs();
			$file = $FS->findFileInDir( 'forceoff', $this->getRootDir(), false, false );
			$this->file_forceoff = empty( $file ) ? false : $file;
		}
		return $this->file_forceoff;
	}

	/**
	 * @param bool $bSetIfNeeded
	 * @return string
	 */
	public function getSessionId( $bSetIfNeeded = true ) {
		if ( empty( self::$sSessionId ) ) {
			$req = Services::Request();
			self::$sSessionId = $req->cookie( $this->getSessionCookieID(), '' );
			if ( empty( self::$sSessionId ) && $bSetIfNeeded ) {
				self::$sSessionId = md5( uniqid( $this->getPluginPrefix() ) );
				$this->setSessionCookie();
			}
		}
		return self::$sSessionId;
	}

	public function getUniqueRequestId( bool $bSetIfNeeded = false ) :string {
		if ( !isset( self::$sRequestId ) ) {
			self::$sRequestId = md5(
				$this->getSessionId( $bSetIfNeeded ).Services::IP()->getRequestIp().Services::Request()->ts().wp_rand()
			);
		}
		return self::$sRequestId;
	}

	public function getShortRequestId() :string {
		return substr( $this->getUniqueRequestId( false ), 0, 10 );
	}

	public function hasSessionId() :bool {
		return !empty( $this->getSessionId( false ) );
	}

	protected function setSessionCookie() {
		Services::Response()->cookieSet(
			$this->getSessionCookieID(),
			$this->getSessionId(),
			Services::Request()->ts() + DAY_IN_SECONDS*30,
			Services::WpGeneral()->getCookiePath(),
			Services::WpGeneral()->getCookieDomain()
		);
	}

	private function getSessionCookieID() :string {
		return 'wp-'.$this->getPluginPrefix();
	}

	/**
	 * We let the \Exception from the core plugin feature to bubble up because it's critical.
	 * @return Shield\Modules\Plugin\ModCon
	 * @throws \Exception from loadFeatureHandler()
	 */
	public function loadCorePluginFeatureHandler() {
		if ( !isset( $this->modules[ 'plugin' ] )
			 || !$this->modules[ 'plugin' ] instanceof \ICWP_WPSF_FeatureHandler_Base ) {
			$this->loadFeatureHandler(
				[
					'slug'          => 'plugin',
					'storage_key'   => 'plugin',
					'load_priority' => 10
				]
			);
		}
		return $this->modules[ 'plugin' ];
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function loadAllFeatures() :bool {
		foreach ( array_keys( $this->loadCorePluginFeatureHandler()->getActivePluginFeatures() ) as $slug ) {
			try {
				$this->getModule( $slug );
			}
			catch ( \Exception $e ) {
				if ( $this->isValidAdminArea() && $this->isPluginAdmin() ) {
					$this->sAdminNoticeError = $e->getMessage();
					add_action( 'admin_notices', [ $this, 'adminNoticePluginFailedToLoad' ] );
					add_action( 'network_admin_notices', [ $this, 'adminNoticePluginFailedToLoad' ] );
				}
			}
		}

		$this->modules_loaded = true;

		// Upgrade modules
		( new Shield\Controller\Utilities\Upgrade() )
			->setCon( $this )
			->execute();

		do_action( $this->prefix( 'modules_loaded' ) );
		do_action( $this->prefix( 'run_processors' ) );
		return true;
	}

	/**
	 * @param string $slug
	 * @return \ICWP_WPSF_FeatureHandler_Base|null|mixed
	 */
	public function getModule( string $slug ) {
		$mod = isset( $this->modules[ $slug ] ) ? $this->modules[ $slug ] : null;
		if ( !$mod instanceof \ICWP_WPSF_FeatureHandler_Base ) {
			try {
				$mods = $this->loadCorePluginFeatureHandler()->getActivePluginFeatures();
				if ( isset( $mods[ $slug ] ) ) {
					$mod = $this->loadFeatureHandler( $mods[ $slug ] );
				}
			}
			catch ( \Exception $e ) {
			}
		}
		return $mod;
	}

	public function getModule_AuditTrail() :Shield\Modules\AuditTrail\ModCon {
		return $this->getModule( 'audit_trail' );
	}

	public function getModule_Comments() :Shield\Modules\CommentsFilter\ModCon {
		return $this->getModule( 'comments_filter' );
	}

	public function getModule_Comms() :Shield\Modules\Comms\ModCon {
		return $this->getModule( 'comms' );
	}

	public function getModule_Email() :Shield\Modules\Email\ModCon {
		return $this->getModule( 'email' );
	}

	public function getModule_Events() :Shield\Modules\Events\ModCon {
		return $this->getModule( 'events' );
	}

	public function getModule_HackGuard() :Shield\Modules\HackGuard\ModCon {
		return $this->getModule( 'hack_protect' );
	}

	public function getModule_Insights() :Shield\Modules\Insights\ModCon {
		return $this->getModule( 'insights' );
	}

	public function getModule_Integrations() :Shield\Modules\Integrations\ModCon {
		return $this->getModule( 'integrations' );
	}

	public function getModule_IPs() :Shield\Modules\IPs\ModCon {
		return $this->getModule( 'ips' );
	}

	public function getModule_License() :Shield\Modules\License\ModCon {
		return $this->getModule( 'license' );
	}

	public function getModule_LoginGuard() :Shield\Modules\LoginGuard\ModCon {
		return $this->getModule( 'login_protect' );
	}

	public function getModule_Plugin() :Shield\Modules\Plugin\ModCon {
		return $this->getModule( 'plugin' );
	}

	public function getModule_Reporting() :Shield\Modules\Reporting\ModCon {
		return $this->getModule( 'reporting' );
	}

	public function getModule_SecAdmin() :Shield\Modules\SecurityAdmin\ModCon {
		return $this->getModule( 'admin_access_restriction' );
	}

	public function getModule_Sessions() :Shield\Modules\Sessions\ModCon {
		return $this->getModule( 'sessions' );
	}

	public function getModule_Traffic() :Shield\Modules\Traffic\ModCon {
		return $this->getModule( 'traffic' );
	}

	public function getModule_UserManagement() :Shield\Modules\UserManagement\ModCon {
		return $this->getModule( 'user_management' );
	}

	public function getModulesNamespace() :string {
		return '\FernleafSystems\Wordpress\Plugin\Shield\Modules';
	}

	/**
	 * @param array $modProps
	 * @return \ICWP_WPSF_FeatureHandler_Base|mixed
	 * @throws \Exception
	 */
	public function loadFeatureHandler( array $modProps ) {
		$modSlug = $modProps[ 'slug' ];
		$mod = isset( $this->modules[ $modSlug ] ) ? $this->modules[ $modSlug ] : null;
		if ( $mod instanceof \ICWP_WPSF_FeatureHandler_Base || $mod instanceof Shield\Modules\Base\ModCon ) {
			return $mod;
		}

		if ( empty( $modProps[ 'storage_key' ] ) ) {
			$modProps[ 'storage_key' ] = $modSlug;
		}
		if ( empty( $modProps[ 'namespace' ] ) ) {
			$modProps[ 'namespace' ] = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $modSlug ) ) );
		}

		if ( !empty( $modProps[ 'min_php' ] )
			 && !Services::Data()->getPhpVersionIsAtLeast( $modProps[ 'min_php' ] ) ) {
			return null;
		}

		$modName = $modProps[ 'namespace' ];
		$sOptionsVarName = sprintf( 'oFeatureHandler%s', $modName ); // e.g. oFeatureHandlerPlugin

		$className = $this->getModulesNamespace().sprintf( '\\%s\\ModCon', $modName );
		if ( !@class_exists( $className ) ) {
			$className = sprintf( '%s_FeatureHandler_%s', strtoupper( $this->getPluginPrefix( '_' ) ), $modName );
		}

		// All this to prevent fatal errors if the plugin doesn't install/upgrade correctly
		if ( !class_exists( $className ) ) {
			$sMessage = sprintf( 'Class "%s" is missing', $className );
			throw new \Exception( $sMessage );
		}

		$this->{$sOptionsVarName} = new $className( $this, $modProps );

		$aMs = $this->modules;
		$aMs[ $modSlug ] = $this->{$sOptionsVarName};
		$this->modules = $aMs;
		return $this->modules[ $modSlug ];
	}

	/**
	 * @return Shield\Users\ShieldUserMeta
	 */
	public function getCurrentUserMeta() {
		return $this->getUserMeta( Services::WpUsers()->getCurrentWpUser() );
	}

	/**
	 * @param \WP_User $user
	 * @return Shield\Users\ShieldUserMeta|mixed
	 */
	public function getUserMeta( $user ) {
		$oMeta = null;
		try {
			if ( $user instanceof \WP_User ) {
				/** @var Shield\Users\ShieldUserMeta $oMeta */
				$oMeta = Shield\Users\ShieldUserMeta::Load( $this->prefix(), $user->ID );
				if ( !$oMeta instanceof Shield\Users\ShieldUserMeta ) {
					// Weird: user reported an error where it wasn't of the correct type
					$oMeta = new Shield\Users\ShieldUserMeta( $this->prefix(), $user->ID );
					Shield\Users\ShieldUserMeta::AddToCache( $oMeta );
				}
				$oMeta->setPasswordStartedAt( $user->user_pass )
					  ->updateFirstSeenAt();
				Services::WpUsers()
						->updateUserMeta( $this->prefix( 'meta-version' ), $this->getVersionNumeric(), $user->ID );
			}
		}
		catch ( \Exception $e ) {
		}
		return $oMeta;
	}

	/**
	 * @return \FernleafSystems\Wordpress\Services\Utilities\Render
	 */
	public function getRenderer() {
		$oRndr = Services::Render();
		$oLocator = ( new Shield\Render\LocateTemplateDirs() )->setCon( $this );
		foreach ( $oLocator->run() as $sDir ) {
			$oRndr->setTwigTemplateRoot( $sDir );
		}
		$oRndr->setTemplateRoot( $this->getPath_Templates() );
		return $oRndr;
	}

	/**
	 * @param array[] $aRegistered
	 * @return array[]
	 */
	public function onWpPrivacyRegisterExporter( $aRegistered ) {
		if ( !is_array( $aRegistered ) ) {
			$aRegistered = []; // account for crap plugins that do-it-wrong.
		}

		$aRegistered[] = [
			'exporter_friendly_name' => $this->getHumanName(),
			'callback'               => [ $this, 'wpPrivacyExport' ],
		];
		return $aRegistered;
	}

	/**
	 * @param array[] $aRegistered
	 * @return array[]
	 */
	public function onWpPrivacyRegisterEraser( $aRegistered ) {
		if ( !is_array( $aRegistered ) ) {
			$aRegistered = []; // account for crap plugins that do-it-wrong.
		}

		$aRegistered[] = [
			'eraser_friendly_name' => $this->getHumanName(),
			'callback'             => [ $this, 'wpPrivacyErase' ],
		];
		return $aRegistered;
	}

	/**
	 * @param string $sEmail
	 * @param int    $nPage
	 * @return array
	 */
	public function wpPrivacyExport( $sEmail, $nPage = 1 ) {

		$bValid = Services::Data()->validEmail( $sEmail )
				  && ( Services::WpUsers()->getUserByEmail( $sEmail ) instanceof \WP_User );

		return [
			'data' => $bValid ? apply_filters( $this->prefix( 'wpPrivacyExport' ), [], $sEmail, $nPage ) : [],
			'done' => true,
		];
	}

	/**
	 * @param string $sEmail
	 * @param int    $nPage
	 * @return array
	 */
	public function wpPrivacyErase( $sEmail, $nPage = 1 ) {

		$bValidUser = Services::Data()->validEmail( $sEmail )
					  && ( Services::WpUsers()->getUserByEmail( $sEmail ) instanceof \WP_User );

		$aResult = [
			'items_removed'  => $bValidUser,
			'items_retained' => false,
			'messages'       => $bValidUser ? [] : [ 'Email address not valid or does not belong to a user.' ],
			'done'           => true,
		];
		if ( $bValidUser ) {
			$aResult = apply_filters( $this->prefix( 'wpPrivacyErase' ), $aResult, $sEmail, $nPage );
		}
		return $aResult;
	}

	/**
	 * @return string
	 */
	private function buildPrivacyPolicyContent() {
		try {
			if ( $this->getModule_SecAdmin()->isWlEnabled() ) {
				$name = $this->getHumanName();
				$href = $this->getLabels()[ 'PluginURI' ];
			}
			else {
				$name = $this->cfg->menu[ 'title' ];
				$href = $this->cfg->meta[ 'privacy_policy_href' ];
			}

			/** @var Shield\Modules\AuditTrail\Options $oOpts */
			$oOpts = $this->getModule_AuditTrail()->getOptions();

			$content = $this->getRenderer()
							->setTemplate( 'snippets/privacy_policy' )
							->setTemplateEngineTwig()
							->setRenderVars(
								[
									'name'             => $name,
									'href'             => $href,
									'audit_trail_days' => $oOpts->getAutoCleanDays()
								]
							)
							->render();
		}
		catch ( \Exception $e ) {
			$content = '';
		}
		return empty( $content ) ? '' : wp_kses_post( wpautop( $content, false ) );
	}

	private function runTests() {
		die();
		( new Shield\Tests\VerifyUniqueEvents() )->setCon( $this )->run();
		foreach ( $this->modules as $oModule ) {
			( new \FernleafSystems\Wordpress\Plugin\Shield\Tests\VerifyConfig() )
				->setOpts( $oModule->getOptions() )
				->run();
		}
	}
}