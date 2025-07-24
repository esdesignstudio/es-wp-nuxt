<?php
namespace FileBird\Model;

defined( 'ABSPATH' ) || exit;

class ThemeModel {
    const COLORS = array(
		'default' => '#8f8f8f',
		'windows' => '#F3C73E',
		'dropbox' => '#88C1FC',
	);

	public static function getThemeColor( string $themeName ): string {
		return self::COLORS[ $themeName ] ?? self::COLORS['default'];
	}
}