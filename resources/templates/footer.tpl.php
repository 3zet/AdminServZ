<footer>
	<div id="footer-inner">
		AdminServ
		&mdash;
		&copy; 2006&ndash;<?php echo date('Y'); ?>
		&mdash;
		<a href="https://github.com/kev717">Kev717</a>
		&amp;
		<a href="https://github.com/Chris92de">Chris92</a>
		|
		<a href="https://github.com/3zet/AdminServZ">AdminServZ</a>
		<?php echo ADMINSERV_VERSION; ?>
		<?php echo date('Y'); ?>
		<?php if (ADMINSERV_TIMER): ?>
			&mdash; <?php echo AdminServ::endTimer(); ?>s
		<?php endif; ?>
		<span id="path_resources" hidden="hidden"><?php echo AdminServConfig::$PATH_RESOURCES; ?></span>
	</div>
</footer>
