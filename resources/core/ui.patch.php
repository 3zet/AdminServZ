<?php
/*
 * PATCH INSTRUCTIONS FOR ui.php
 * =========================
 * Replace the two methods in resources/core/ui.php:
   1. getCss()      -- lines ~316-326
   2. getThemeList() -- lines ~122-154
 *
 * You can paste the fragments below directly into the file
 * or replace the whole file with the attached ui.php.full.php
 * (if included in the package).
 */


/* ============================================================
   FRAGMENT 1 -- replace the getCss() method in ui.php
   ============================================================ */

	public static function getCss(): string{
		$path = AdminServConfig::$PATH_RESOURCES .'css/';

		$out = '<link rel="stylesheet" href="'.$path.'fileuploader.css" />'."\n\t\t"
		.'<link rel="stylesheet" href="'.$path.'global.css" />'."\n\t\t"
		.'<!--[if IE]><link rel="stylesheet" href="'.$path.'ie.css" /><![endif]-->'."\n\t\t";

		if( defined('USER_THEME') && USER_THEME ){
			// Dark theme: -dark suffix
			$isDark = str_ends_with(USER_THEME, '-dark');

			// jQuery UI theme -- use the base name without "-dark"
			// The jqueryui/{color}.css file exists for the 6 original names.
			// For dark themes, use the same palette (e.g. blue-dark -> blue.css).
			$jqueryUiTheme = $isDark
				? substr(USER_THEME, 0, -5)  // 'blue-dark' → 'blue'
				: USER_THEME;

			if( file_exists($path.'jqueryui/'.$jqueryUiTheme.'.css') ){
				$out .= '<link rel="stylesheet" href="'.$path.'jqueryui/'. $jqueryUiTheme .'.css" />'."\n\t\t";
			}

			// Accent colors from theme.php
			$out .= '<link rel="stylesheet" href="'.$path.'theme.php?th='. USER_THEME .'" />'."\n\t\t";

			// Dark background + overrides
			if( $isDark ){
				$out .= '<link rel="stylesheet" href="'.$path.'dark.css" />'."\n\t\t";
			}
		}

		// Mobile CSS
		$out .= '<link rel="stylesheet" media="screen and (max-width: 1000px) and (min-width: 335px)" href="' . $path . 'mobile.css" />' . "\n";

		return $out;
	}


/* ============================================================
   FRAGMENT 2 -- replace the getThemeList() method in ui.php
   ============================================================
   Zmiany:
   - Dark variants (suffix -dark) shown as a circle
     split in half: left half = accent color, right = dark background.
   - Dodana klasa CSS "theme-color-dark" do identyfikacji w CSS.
   ============================================================ */

	public static function getThemeList($currentTheme = [] ){
		$out  = null;
		$list = [];
		if( self::hasTheme() ){
			$list = ExtensionConfig::$THEMES;
		}
		$countList = count($list);

		// Page URL parameter
		if(USER_PAGE && USER_PAGE != 'index'){
			$param = '?p='.USER_PAGE .'&amp;th=';
		}
		else{
			$param = '?th=';
		}

		if( $countList > 0 ){
			$out .= '<ul>';

			// Current theme first
			if( count($currentTheme) > 0 ){
				$currentThemeName  = key($currentTheme);
				$currentThemeColor = current($currentTheme);
				unset($list[$currentThemeName]);

				$isDark = str_ends_with($currentThemeName, '-dark');
				$label  = Utils::t( ucfirst(str_replace('-dark', ' (dark)', (string) $currentThemeName)) );

				if( $isDark ){
					$out .= '<li><a tabindex="-1" class="theme-color theme-color-dark"'
						.' style="background: linear-gradient(135deg, '.$currentThemeColor[0].' 50%, #111 50%);"'
						.' href="'.$param.$currentThemeName.'" title="'.$label.'"></a></li>';
				}
				else{
					$out .= '<li><a tabindex="-1" class="theme-color"'
						.' style="background-color: '.$currentThemeColor[0].';"'
						.' href="'.$param.$currentThemeName.'" title="'.$label.'"></a></li>';
				}
			}

			// Remaining themes
			foreach($list as $name => $color){
				$isDark = str_ends_with($name, '-dark');
				$label  = Utils::t( ucfirst(str_replace('-dark', ' (dark)', (string) $name)) );

				if( $isDark ){
					$out .= '<li><a tabindex="-1" class="theme-color theme-color-dark"'
						.' style="background: linear-gradient(135deg, '.$color[0].' 50%, #111 50%);"'
						.' href="'.$param.$name.'" title="'.$label.'"></a></li>';
				}
				else{
					$out .= '<li><a tabindex="-1" class="theme-color"'
						.' style="background-color: '.$color[0].';"'
						.' href="'.$param.$name.'" title="'.$label.'"></a></li>';
				}
			}

			$out .= '</ul>';
		}

		return $out;
	}


/* ============================================================
   FRAGMENT 3 -- in templates/general.tpl.php or wherever you
   use the displayserv widget -- add the ds-dark class for dark themes.

   Usage example:
   <div id="displayserv"<?php if(str_ends_with(USER_THEME, '-dark')): ?> class="ds-dark"<?php endif; ?>>
   	<?php echo AdminServUI::getDisplayServ(); ?>
   </div>

   If the widget has no dedicated template and is initialized
   tylko przez JS ($.fn.displayServ), to zamiast tego dodaj
   do adminserv_event.js lub adminserv_funct.js:

   var isDark = <?php echo str_ends_with(USER_THEME ?? '', '-dark') ? 'true' : 'false'; ?>;
   if(isDark){ $('#displayserv').addClass('ds-dark'); }
   ============================================================ */
?>
