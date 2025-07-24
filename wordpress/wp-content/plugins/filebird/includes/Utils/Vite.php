<?php

namespace FileBird\Utils;

class Vite {
	const HOST                 = 'https://localhost:3000/';
	const SCRIPT_HANDLE        = 'module/filebird/vite';
	const CLIENT_SCRIPT_HANDLE = 'module/filebird/vite-client';

	public static function base_path() {
		return NJFB_PLUGIN_URL . 'assets/dist/';
	}

	/**
	 * Enqueues the Vite script and its dependencies.
	 *
	 * @param string $script The name of the script to enqueue.
	 * @return void
	 */
	public static function enqueue_vite( string $script = 'main.tsx' ) {
		self::enqueue_preload( $script );
		// self::css_tag( $script );
		$script_handle = self::register( $script );
		add_filter(
			'script_loader_tag',
			function ( $tag, $handle, $src ) {
				if ( strpos( $handle, 'module/filebird/' ) !== false ) {
					$str  = "type='module'";
					$str .= NJFB_DEVELOPMENT ? ' crossorigin' : '';
					$tag  = '<script ' . $str . ' src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>';
				}
				return $tag;
			},
			10,
			3
		);

		add_filter(
			'script_loader_src',
			function( $src, $handle ) {
				if ( strpos( $handle, self::SCRIPT_HANDLE ) !== false && strpos( $src, '?ver=' ) ) {
					return remove_query_arg( 'ver', $src );
				}

				return $src;
			},
			10,
			2
		);

		return $script_handle;
	}

	public static function enqueue_preload( $script ) {
		add_action(
			'admin_head',
			function() use ( $script ) {
				if ( NJFB_DEVELOPMENT ) {
					echo '<script type="module">
					import RefreshRuntime from "' . esc_url( self::HOST ) . '@react-refresh"
					RefreshRuntime.injectIntoGlobalHook(window)
					window.$RefreshReg$ = () => {}
					window.$RefreshSig$ = () => (type) => type
					window.__vite_plugin_react_preamble_installed__ = true
					</script>';
				} else {
					foreach ( self::imports_urls( $script ) as $url ) {
						echo ( '<link rel="modulepreload" href="' . esc_url( $url ) . '">' );
					}
				}
			}
		);
	}

	public static function register( $entry ) {
		$url = NJFB_DEVELOPMENT ? self::HOST . $entry : self::asset_url( $entry );

		if ( ! $url ) {
			return '';
		}

		// wp_enqueue_script( self::CLIENT_SCRIPT_HANDLE, self::HOST . '@vite/client', array(), NJFB_VERSION, false );
		wp_enqueue_script( "module/filebird/$entry", $url, false, true, NJFB_DEVELOPMENT ? true : false );

		return "module/filebird/$entry";
	}

	private static function css_tag( string $entry ): string {
		// not needed on dev, it's inject by Vite
		if ( NJFB_DEVELOPMENT ) {
			return '';
		}

		$tags = '';
		foreach ( self::css_urls( $entry ) as $key => $url ) {
			wp_enqueue_style( "filebird/$key", $url, array(), NJFB_VERSION );
		}
		return $tags;
	}


	// Helpers to locate files

	private static function get_manifest(): array {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( NJFB_PLUGIN_PATH . 'assets/dist/manifest.json' );

		return json_decode( $content, true );
	}

	private static function asset_url( string $entry ): string {
		$manifest = self::get_manifest();

		return isset( $manifest[ $entry ] )
		? self::base_path() . $manifest[ $entry ]['file']
		: self::base_path() . $entry;
	}

	private static function get_public_url_base() {
		return NJFB_DEVELOPMENT ? '/dist/' : self::base_path();
	}

	private static function imports_urls( string $entry ): array {
		$urls     = array();
		$manifest = self::get_manifest();

		if ( ! empty( $manifest[ $entry ]['imports'] ) ) {
			foreach ( $manifest[ $entry ]['imports'] as $imports ) {
				$urls[] = self::get_public_url_base() . $manifest[ $imports ]['file'];
			}
		}
		return $urls;
	}

	private static function css_urls( string $entry ): array {
		$urls     = array();
		$manifest = self::get_manifest();

		if ( ! empty( $manifest[ $entry ]['css'] ) ) {
			foreach ( $manifest[ $entry ]['css'] as $file ) {
				$urls[ "filebird_entry_$file" ] = self::get_public_url_base() . $file;
			}
		}

		if ( ! empty( $manifest[ $entry ]['imports'] ) ) {
			foreach ( $manifest[ $entry ]['imports'] as $imports ) {
				if ( ! empty( $manifest[ $imports ]['css'] ) ) {
					foreach ( $manifest[ $imports ]['css'] as $css ) {
						$urls[ "filebird_imports_$css" ] = self::get_public_url_base() . $css;
					}
				}
			}
		}
		return $urls;
	}
}
