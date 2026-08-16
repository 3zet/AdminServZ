<?php if (isset($_SESSION['adminserv']['check_password']) && $_SESSION['adminserv']['check_password'] === true): ?>

<section class="section-servers-online-config">
	<form method="post" action="./config/">
		<fieldset>
			<legend><?php echo Utils::t('Servers configuration'); ?></legend>
			<div class="connection-label">
				<label for="checkPassword"><?php echo Utils::t('Password'); ?></label>
				<input class="text" type="password" name="checkPassword" id="checkPassword" value="" placeholder="••••••••" />
			</div>
			<div class="connection-login">
				<input class="button dark" type="submit" name="configcheckpassword" id="configcheckpassword" value="<?php echo Utils::t('Connection'); ?>" />
			</div>
			<div class="connection-cancel">
				<a class="button light" href="./?logout"><?php echo Utils::t('Cancel'); ?></a>
			</div>
		</fieldset>
	</form>
</section>

<?php elseif (isset($_SESSION['adminserv']['get_password']) && $_SESSION['adminserv']['get_password'] === true): ?>

<section class="section-servers-online-config no-server">
	<form method="post" action="./config/">
		<fieldset>
			<legend><?php echo Utils::t('Online configuration'); ?></legend>
			<div class="connection-label">
				<label for="savePassword"><?php echo Utils::t('Password'); ?></label>
				<input class="text" type="password" name="savePassword" id="savePassword" value="" placeholder="••••••••" />
			</div>
			<div class="connection-login">
				<input class="button dark" type="submit" name="configsavepassword" id="configsavepassword" value="<?php echo Utils::t('Save'); ?>" />
			</div>
		</fieldset>
	</form>
</section>

<?php else: ?>

<section class="displayserv">
	<?php if (AdminServConfig::USE_DISPLAYSERV): ?>
		<link rel="stylesheet" href="<?php echo AdminServConfig::$PATH_RESOURCES; ?>css/displayserv.css" />
		<script src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>js/displayserv.js"></script>
		<script>
			$(document).ready(function(){
				var isDark = <?php echo str_ends_with(USER_THEME ?? '', '-dark') ? 'true' : 'false'; ?>;
				var $widget = $('#displayserv');
				if(isDark){ $widget.addClass('ds-dark'); }
				$widget.displayServ({
					color: '<?php echo AdminServUI::getThemeColor(); ?>'
				});
			});
		</script>
		<div id="displayserv"></div>
	<?php endif; ?>
</section>

<?php endif; ?>
