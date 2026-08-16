<?php
class ExtensionConfig {
	/********************* EXTENSION CONFIGURATION *********************/

	// Plugins list installed
	public static $PLUGINS = ['planets', 'coppers'];


	// Themes color list
	// Format: 'name' => ['color1 (accent)', 'color2 (hover/markdown)']
	// Dark variants have the "-dark" suffix -- dark background with light accents
	public static $THEMES = [
		'red'        => ['#ea4f4f', '#ffc0c0'],
		'blue'       => ['#5e9cd5', '#d9e8ff'],
		'green'      => ['#8aca1b', '#d1e9a8'],
		'orange'     => ['#ffa600', '#ffe3b0'],
		'purple'     => ['#b15cd5', '#e5c4f3'],
		'black'      => ['#727272', '#dedede'],

		// Accent (color1) -- brighter, readable on dark background
		// Hover (color2) -- subtle highlight on dark background
		'red-dark'    => ['#f07070', '#3a1a1a'],
		'blue-dark'   => ['#6aade8', '#0e2040'],
		'green-dark'  => ['#90d830', '#182a08'],
		'orange-dark' => ['#ffb830', '#2e1e00'],
		'purple-dark' => ['#c878e8', '#1e0e30'],
		'black-dark'  => ['#a0a0a0', '#1e1e1e'],
	];


	public static $LANG = ['fr' => 'Français', 'en' => 'English', 'de' => 'Deutsch', 'es' => 'Español', 'no' => 'Norsk', 'dk' => 'Danish', 'pl' => 'Polish', 'nl' => 'Dutch'];


	// Game modes list
	public static $GAMEMODES = [0 => 'Script', 1 => 'Rounds', 2 => 'TimeAttack', 3 => 'Team', 4 => 'Laps', 5 => 'Cup', 6 => 'Stunts'];
	public static $TEAMSCRIPTS = ['Elite', 'Battle', 'Siege', 'Heroes', 'SpeedBall'];


	// Menu list in maps page
	public static $MAPSMENU = ['maps-list' => 'List', 'maps-local' => 'Local', 'maps-upload' => 'Send', 'maps-order' => 'Order', 'maps-matchset' => 'MatchSettings', 'maps-creatematchset' => 'Create a MatchSettings'];
}
?>
