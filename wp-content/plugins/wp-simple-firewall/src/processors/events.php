<?php

use FernleafSystems\Wordpress\Plugin\Shield;
use FernleafSystems\Wordpress\Plugin\Shield\Databases;
use FernleafSystems\Wordpress\Plugin\Shield\Modules\Events;
use FernleafSystems\Wordpress\Plugin\Shield\Modules\Events\ModCon;

/**
 * @deprecated 10.1
 */
class ICWP_WPSF_Processor_Events extends Shield\Modules\BaseShield\ShieldProcessor {

	/**
	 * @var Events\Lib\StatsWriter
	 */
	private $oStatsWriter;

	public function run() {
		$this->loadStatsWriter()->setIfCommit( true );
		add_action( $this->getCon()->prefix( 'dashboard_widget_content' ), [ $this, 'statsWidget' ], 10 );
	}

	/**
	 * @return Events\Lib\StatsWriter
	 */
	public function loadStatsWriter() {
		if ( !isset( $this->oStatsWriter ) ) {
			/** @var ModCon $mod */
			$mod = $this->getMod();
			$this->oStatsWriter = ( new Events\Lib\StatsWriter( $this->getCon() ) )
				->setDbHandler( $mod->getDbHandler_Events() );
		}
		return $this->oStatsWriter;
	}

	public function statsWidget() {
		/** @var Databases\Events\Select $oSelEvents */
		$oSelEvents = $this->getCon()
						   ->getModule_Events()
						   ->getDbHandler_Events()
						   ->getQuerySelector();

		$aKeyStats = [
			'comments'          => [
				__( 'Comment Blocks', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvents( [
					'spam_block_bot',
					'spam_block_human',
					'spam_block_recaptcha'
				] )
			],
			'firewall'          => [
				__( 'Firewall Blocks', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( 'firewall_block' )
			],
			'login_fail'        => [
				__( 'Login Blocks', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( 'login_block' )
			],
			'login_verified'    => [
				__( 'Login Verified', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( '2fa_success' )
			],
			'session_start'     => [
				__( 'User Sessions', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( 'session_start' )
			],
			'ip_killed'         => [
				__( 'IP Blocks', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( 'conn_kill' )
			],
			'ip_transgressions' => [
				__( 'Total Offenses', 'wp-simple-firewall' ),
				$oSelEvents->clearWheres()->sumEvent( 'ip_offense' )
			],
		];

		$aDisplayData = [
			'sHeading'  => sprintf( __( '%s Statistics', 'wp-simple-firewall' ), $this->getCon()->getHumanName() ),
			'aKeyStats' => $aKeyStats,
		];

		echo $this->getMod()->renderTemplate(
			'snippets/widget_dashboard_statistics.php',
			$aDisplayData
		);
	}

	public function runDailyCron() {
		( new Events\Consolidate\ConsolidateAllEvents() )
			->setMod( $this->getMod() )
			->run();
	}
}