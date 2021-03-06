<?php

namespace FernleafSystems\Wordpress\Plugin\Shield\Tables\Build;

use FernleafSystems\Wordpress\Plugin\Shield\Databases\AdminNotes\EntryVO;
use FernleafSystems\Wordpress\Plugin\Shield\Tables;

/**
 * Class AdminNotes
 * @package FernleafSystems\Wordpress\Plugin\Shield\Tables\Build
 */
class AdminNotes extends BaseBuild {

	/**
	 * @return array[]
	 */
	public function getEntriesFormatted() :array {
		$aEntries = [];

		foreach ( $this->getEntriesRaw() as $nKey => $oEntry ) {
			/** @var EntryVO $oEntry */
			$aE = $oEntry->getRawDataAsArray();
			$aE[ 'created_at' ] = $this->formatTimestampField( $oEntry->created_at );
			$aEntries[ $nKey ] = $aE;
		}

		return $aEntries;
	}

	/**
	 * @return Tables\Render\WpListTable\AdminNotes
	 */
	protected function getTableRenderer() {
		return new Tables\Render\WpListTable\AdminNotes();
	}
}