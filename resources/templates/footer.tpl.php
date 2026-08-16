
					</div><!-- #content-inner -->
				</div><!-- #content -->

				<footer>
					<div id="footer-inner">
						AdminServ <?php echo ADMINSERV_VERSION; ?>
						&mdash;
						© 2006–<?php echo date('Y'); ?>
						<a href="http://www.zone-kev717.info">Kev717</a>
						&amp; <a href="http://forum.maniaplanet.com/memberlist.php?mode=viewprofile&u=282">Chris92</a>
						<?php if (ADMINSERV_TIMER): ?>
							&mdash; <?php echo AdminServ::endTimer(); ?>s
						<?php endif; ?>
						<span id="path_resources" hidden="hidden"><?php echo AdminServConfig::$PATH_RESOURCES; ?></span>
					</div>
				</footer>

			</div><!-- #page-inner -->
		</div><!-- #page -->
	</body>
</html>
