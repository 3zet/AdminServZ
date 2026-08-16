<fieldset class="gameinfos_general">
	<legend><img src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>images/16/restartrace.png" alt="" /><?php echo Utils::t('General'); ?></legend>
	<table>
		<?php if (AdminServAdminLevel::hasPermission('gameinfos_general_gamemode') && !isTM2020()): ?>
			<tr>
				<td class="key"><label for="NextGameMode"><?php echo Utils::t('Game mode'); ?></label></td>
				<?php if ($data['gameInfos']['curr'] != null): ?>
					<td class="value">
						<input class="text width2" type="text" name="CurrGameMode" id="CurrGameMode" readonly="readonly"
							value="<?php echo AdminServ::getGameModeName($data['gameInfos']['curr']['GameMode']); ?>" />
					</td>
				<?php endif; ?>
				<td class="value">
					<select class="width2" name="NextGameMode" id="NextGameMode">
						<?php echo AdminServUI::getGameModeList($data['gameInfos']['next']['GameMode']); ?>
					</select>
				</td>
				<td class="preview"></td>
			</tr>
		<?php elseif (isTM2020()): ?>
			<!-- TM2020: Game mode = Script, full script name on its own row -->
			<tr>
				<td class="key"><?php echo Utils::t('Game mode'); ?></td>
				<td class="value" colspan="3">
					<input class="text width3" type="text" readonly="readonly" value="Script" />
				</td>
			</tr>
			<tr>
				<td class="key"><label for="NextScriptName"><?php echo Utils::t('Script name'); ?></label></td>
				<?php if ($data['gameInfos']['curr'] != null): ?>
					<td class="value">
						<input class="text width3" type="text" name="CurrScriptName" id="CurrScriptName" readonly="readonly"
							title="<?php echo htmlspecialchars($data['gameInfos']['curr']['ScriptName'] ?? ''); ?>"
							value="<?php echo AdminServ::formatScriptName($data['gameInfos']['curr']['ScriptName'] ?? ''); ?>" />
					</td>
				<?php endif; ?>
				<td class="value" colspan="2">
					<input class="text width3" type="text" name="NextScriptName" id="NextScriptName"
						value="<?php echo $data['gameInfos']['next']['ScriptName'] ?? ''; ?>" />
				</td>
			</tr>
		<?php endif; ?>

		<?php if (AdminServAdminLevel::hasPermission('gameinfos_general_options') && !isTM2020()): ?>
			<tr>
				<td class="key"><label for="NextChatTime"><?php echo Utils::t('Map end time'); ?> <span>(<?php echo Utils::t('sec'); ?>)</span></label></td>
				<?php if ($data['gameInfos']['curr'] != null && $data['gameInfos']['curr']['ChatTime'] !== null): ?>
					<td class="value">
						<input class="text width2" type="text" name="CurrChatTime" id="CurrChatTime" readonly="readonly"
							value="<?php echo TimeDate::millisecToSec($data['gameInfos']['curr']['ChatTime'] + 8000); ?>" />
					</td>
				<?php endif; ?>
				<td class="value">
					<input class="text width2" type="number" min="0" name="NextChatTime" id="NextChatTime"
						value="<?php echo TimeDate::millisecToSec(($data['gameInfos']['next']['ChatTime'] ?? 0) + 8000); ?>" />
				</td>
				<td class="preview"></td>
			</tr>
			<tr>
				<td class="key"><label for="NextFinishTimeout"><?php echo Utils::t('Round/lap end time'); ?> <span>(<?php echo Utils::t('sec'); ?>)</span></label></td>
				<?php if ($data['gameInfos']['curr'] != null && $data['gameInfos']['curr']['FinishTimeout'] !== null): ?>
					<td class="value">
						<input class="text width2" type="text" name="CurrFinishTimeout" id="CurrFinishTimeout" readonly="readonly" value="<?php
							if ($data['gameInfos']['curr']['FinishTimeout'] == 0): echo Utils::t('Default').' (15'.Utils::t('sec').')';
							elseif ($data['gameInfos']['curr']['FinishTimeout'] == 1): echo Utils::t('Auto (based on map)');
							else: echo TimeDate::millisecToSec($data['gameInfos']['curr']['FinishTimeout']);
							endif; ?>" />
					</td>
				<?php endif; ?>
				<td class="value next">
					<select class="width2" name="NextFinishTimeout" id="NextFinishTimeout"
						<?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) > 1): echo ' hidden="hidden"'; endif; ?>>
						<option value="0" <?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) == 0): echo 'selected="selected"'; endif; ?>><?php echo Utils::t('Default'); ?> (15<?php echo Utils::t('sec'); ?>)</option>
						<option value="1" <?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) == 1): echo 'selected="selected"'; endif; ?>><?php echo Utils::t('Auto (based on map)'); ?></option>
						<option value="more"><?php echo Utils::t('Choose time'); ?></option>
					</select>
					<input class="text width2" type="number" min="0" name="NextFinishTimeoutValue" id="NextFinishTimeoutValue"
						value="<?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) > 1): echo TimeDate::millisecToSec($data['gameInfos']['next']['FinishTimeout']); endif; ?>"
						<?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) < 2): echo ' hidden="hidden"'; endif; ?> />
				</td>
				<td class="preview"<?php if (($data['gameInfos']['next']['FinishTimeout'] ?? 0) < 2): echo ' hidden="hidden"'; endif; ?>>
					<a class="returnDefaultValue" href="?p=<?php echo USER_PAGE; ?>"><?php echo Utils::t('Return to the default value'); ?></a>
				</td>
			</tr>
		<?php endif; ?>

		<?php if (AdminServAdminLevel::hasPermission('gameinfos_general_warmup') && !isTM2020()): ?>
			<?php echo AdminServUI::getGameInfosField($data['gameInfos'], 'All WarmUp duration', 'AllWarmUpDuration'); ?>
		<?php endif; ?>

		<?php if (AdminServAdminLevel::hasPermission('gameinfos_general_options') && !isTM2020()): ?>
			<tr>
				<td class="key"><label for="NextDisableRespawn"><?php echo Utils::t('Respawn'); ?></label></td>
				<?php if ($data['gameInfos']['curr'] != null && $data['gameInfos']['curr']['DisableRespawn'] !== null): ?>
					<td class="value">
						<input class="text width2" type="text" name="CurrDisableRespawn" id="CurrDisableRespawn" readonly="readonly"
							value="<?php echo ($data['gameInfos']['curr']['DisableRespawn'] === false) ? Utils::t('Enable') : Utils::t('Disable'); ?>" />
					</td>
				<?php endif; ?>
				<td class="value">
					<input class="text" type="checkbox" name="NextDisableRespawn" id="NextDisableRespawn" value=""
						<?php if (($data['gameInfos']['next']['DisableRespawn'] ?? null) === false): echo ' checked="checked"'; endif; ?> />
				</td>
				<td class="preview"></td>
			</tr>
			<tr>
				<td class="key"><label for="NextForceShowAllOpponents"><?php echo Utils::t('Force show of all opponents'); ?></label></td>
				<?php if ($data['gameInfos']['curr'] != null && $data['gameInfos']['curr']['ForceShowAllOpponents'] !== null): ?>
					<td class="value">
						<input class="text width2" type="text" name="CurrForceShowAllOpponents" id="CurrForceShowAllOpponents" readonly="readonly" value="<?php
							if ($data['gameInfos']['curr']['ForceShowAllOpponents'] == 0): echo Utils::t('Let player choose');
							elseif ($data['gameInfos']['curr']['ForceShowAllOpponents'] == 1): echo Utils::t('All opponents');
							else: echo $data['gameInfos']['curr']['ForceShowAllOpponents'].' '.Utils::t('minimal opponents');
							endif; ?>" />
					</td>
				<?php endif; ?>
				<td class="value next">
					<select class="width2" name="NextForceShowAllOpponents" id="NextForceShowAllOpponents"
						<?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) > 1): echo ' hidden="hidden"'; endif; ?>>
						<option value="0" <?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) == 0): echo 'selected="selected"'; endif; ?>><?php echo Utils::t('Let player choose'); ?></option>
						<option value="1" <?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) == 1): echo 'selected="selected"'; endif; ?>><?php echo Utils::t('All opponents'); ?></option>
						<option value="more"><?php echo Utils::t('Choose opponents number'); ?></option>
					</select>
					<input class="text width2" type="text" name="NextForceShowAllOpponentsValue" id="NextForceShowAllOpponentsValue"
						value="<?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) > 1): echo $data['gameInfos']['next']['ForceShowAllOpponents']; endif; ?>"
						<?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) < 2): echo ' hidden="hidden"'; endif; ?> />
				</td>
				<td class="preview"<?php if (($data['gameInfos']['next']['ForceShowAllOpponents'] ?? 0) < 2): echo ' hidden="hidden"'; endif; ?>>
					<a class="returnDefaultValue" href="?p=<?php echo USER_PAGE; ?>"><?php echo Utils::t('Return to the default value'); ?></a>
				</td>
			</tr>
		<?php endif; ?>

		<?php if (isTM2020() && AdminServAdminLevel::isType('Admin')): ?>
			<tr>
				<td class="key"><?php echo Utils::t('Script manager'); ?></td>
				<td class="value" colspan="3">
					<a id="openScriptManager" href="">
						<?php echo Utils::t('Upload / Edit script'); ?>
					</a>
				</td>
			</tr>
		<?php endif; ?>
	</table>
</fieldset>

<?php if (isTM2020() && isMpApi()): ?>
<!-- Script Settings inline -- TM2020 (filled in via JS/AJAX) -->
<fieldset id="tm2020-script-settings" class="gameinfos_script_settings_inline" hidden="hidden">
	<legend>
		<img src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>images/16/options.png" alt="" />
		<?php echo Utils::t('Script settings'); ?>
		<span class="script-settings-name"></span>
	</legend>
	<div id="inlineScriptSettings"></div>
</fieldset>
<?php endif; /* isTM2020 && isMpApi */ ?>

<?php if (isTM2020() && AdminServAdminLevel::isType('Admin')): ?>
<!-- Dialog: Script Manager (TM2020) -->
<div id="scriptManagerDialog"
	data-title="<?php echo Utils::t('Script manager (TM2020)'); ?>"
	data-cancel="<?php echo Utils::t('Cancel'); ?>"
	data-saved="<?php echo Utils::t('Script saved.'); ?>"
	data-reloaded="<?php echo Utils::t('Script saved and reloaded.'); ?>"
	data-uploading="<?php echo Utils::t('Uploading…'); ?>"
	data-upload-ok="<?php echo Utils::t('Upload complete.'); ?>"
	data-upload-err="<?php echo Utils::t('Upload error.'); ?>"
	hidden="hidden">

	<div id="scriptManagerTabs">
		<ul>
			<li><a href="#scriptTab-upload"><?php echo Utils::t('Upload'); ?></a></li>
			<li><a href="#scriptTab-editor"><?php echo Utils::t('Editor'); ?></a></li>
		</ul>

		<!-- Tab: Upload -->
		<div id="scriptTab-upload">
			<p class="form-hint"><?php echo Utils::t('Upload a .Script.txt file to the server\'s Scripts/Modes/ directory.'); ?></p>
			<table class="game_infos">
				<tr>
					<td class="key"><label for="scriptUploadSubdir"><?php echo Utils::t('Subdirectory'); ?></label></td>
					<td class="value" colspan="2">
						<input class="text width2" type="text" id="scriptUploadSubdir" placeholder="e.g. TrackMania (optional)" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo Utils::t('File'); ?></td>
					<td class="value" colspan="2">
						<div id="scriptUploadZone" class="script-upload-zone">
							<span class="script-upload-hint"><?php echo Utils::t('Click or drop .Script.txt here'); ?></span>
							<input type="file" id="scriptUploadInput" accept=".txt" style="display:none" />
						</div>
						<div id="scriptUploadProgress" hidden="hidden">
							<span id="scriptUploadFilename"></span>
							<span id="scriptUploadStatus"></span>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo Utils::t('Options'); ?></td>
					<td class="value" colspan="2">
						<label>
							<input type="checkbox" id="scriptUploadActivate" value="1" checked="checked" />
							<?php echo Utils::t('Set as active script after upload'); ?>
						</label><br/>
						<label>
							<input type="checkbox" id="scriptUploadReload" value="1" />
							<?php echo Utils::t('Restart map after upload'); ?>
						</label>
					</td>
				</tr>
			</table>
			<div class="script-upload-actions">
				<button id="scriptUploadBtn" class="button dark"><?php echo Utils::t('Upload'); ?></button>
			</div>
			<div id="scriptUploadResult" class="script-result-msg" hidden="hidden"></div>
		</div>

		<!-- Tab: Editor -->
		<div id="scriptTab-editor">
			<p class="form-hint"><?php echo Utils::t('Edit a script file directly on the server (only available when AdminServ runs on the same machine as the dedicated server).'); ?></p>
			<table class="game_infos">
				<tr>
					<td class="key"><label for="scriptEditorSelect"><?php echo Utils::t('Script file'); ?></label></td>
					<td class="value" colspan="2">
						<select id="scriptEditorSelect" class="width2">
							<option value=""><?php echo Utils::t('— loading… —'); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<div id="scriptEditorNotLocal" class="script-result-msg script-warn" hidden="hidden">
				<?php echo Utils::t('Script editor is only available when AdminServ has direct access to the server filesystem.'); ?>
			</div>
			<div id="scriptEditorWrap" hidden="hidden">
				<textarea id="scriptEditorContent" class="script-editor-textarea" spellcheck="false" rows="20"></textarea>
				<div class="script-editor-actions">
					<label class="script-editor-reload-opt">
						<input type="checkbox" id="scriptEditorReload" value="1" />
						<?php echo Utils::t('Restart map after save'); ?>
					</label>
					<button id="scriptEditorSaveBtn" class="button dark"><?php echo Utils::t('Save'); ?></button>
				</div>
			</div>
			<div id="scriptEditorResult" class="script-result-msg" hidden="hidden"></div>
		</div>
	</div>
</div>
<?php endif; /* isTM2020 && Admin */ ?>
