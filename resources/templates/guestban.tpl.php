<section class="cadre left section-guestban">
	<form method="post" action="?p=<?php echo USER_PAGE; ?>">

	<?php
		$guestbanLists = [
			'banlist'    => ['label' => 'Banlist',    'checkName' => 'checkAllBanlist',    'cols' => 4, 'hasIp' => true, 'hasClient' => true],
			'blacklist'  => ['label' => 'Blacklist',  'checkName' => 'checkAllBlacklist',  'cols' => 2, 'hasIp' => false, 'hasClient' => false],
			'guestlist'  => ['label' => 'Guestlist',  'checkName' => 'checkAllGuestlist',  'cols' => 2, 'hasIp' => false, 'hasClient' => false],
			'ignorelist' => ['label' => 'Ignorelist', 'checkName' => 'checkAllIgnorelist', 'cols' => 2, 'hasIp' => false, 'hasClient' => false],
		];
		// TMO / TMS / TMN-ESWC have no ignore list
		if (isTmClassic()) {
			unset($guestbanLists['ignorelist']);
		}
	?>
	<?php foreach ($guestbanLists as $listKey => $listMeta): ?>

		<div id="<?php echo $listKey; ?>">
			<h1><?php echo $listMeta['label']; ?><?php if ($data[$listKey]['count'] > 0): echo ' ('.$data[$listKey]['count'].')'; endif; ?></h1>
			<div class="title-detail">
				<ul>
					<li>
						<a class="cleanList"
							href="?p=<?php echo USER_PAGE; ?>&amp;clean=<?php echo $listKey; ?>"
							data-empty="<?php echo Utils::t('The list is already empty.'); ?>">
							<?php echo Utils::t('Clean the list'); ?>
						</a>
					</li>
					<li class="last">
						<input type="checkbox" name="<?php echo $listMeta['checkName']; ?>" id="<?php echo $listMeta['checkName']; ?>" value=""<?php if ($data[$listKey]['count'] == 0): echo ' disabled="disabled"'; endif; ?> />
					</li>
				</ul>
			</div>
			<table>
				<thead>
					<tr>
						<th class="thleft"><?php echo Utils::t('Login'); ?></th>
						<?php if ($listMeta['hasIp']): ?><th><?php echo Utils::t('IP address'); ?></th><?php endif; ?>
						<?php if ($listMeta['hasClient']): ?><th><?php echo Utils::t('Client'); ?></th><?php endif; ?>
						<th class="thright"></th>
					</tr>
				</thead>
				<tbody>
					<tr class="table-separation"><td colspan="<?php echo $listMeta['cols']; ?>"></td></tr>
					<?php if ($data[$listKey]['count'] > 0): ?>
						<?php foreach ($data[$listKey]['list'] as $player): ?>
							<tr>
								<td class="imgleft">
									<img src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>images/16/solo.png" alt="" />
									<?php echo $player['Login']; ?>
								</td>
								<?php if ($listMeta['hasIp']): ?><td><?php echo $player['IPAddress']; ?></td><?php endif; ?>
								<?php if ($listMeta['hasClient']): ?><td><?php echo $player['ClientName']; ?></td><?php endif; ?>
								<td class="checkbox"><input type="checkbox" name="<?php echo $listKey; ?>[]" value="<?php echo $player['Login']; ?>" /></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="no-line"><td colspan="<?php echo $listMeta['cols']; ?>"><?php echo Utils::t('No player'); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

	<?php endforeach; ?>

	<div class="options">
		<div class="fright">
			<div class="selected-files-label locked">
				<span class="selected-files-title"><?php echo Utils::t('For the selection'); ?></span>
				<span class="selected-files-count">(0)</span>
				<div class="selected-files-option">
					<?php if (AdminServAdminLevel::hasPermission('guestban_addplayer')): ?>
						<input class="button dark" type="submit" name="blackListPlayer" id="blackListPlayer" value="<?php echo Utils::t('Blacklist'); ?>" />
					<?php endif; ?>
					<?php if (AdminServAdminLevel::hasPermission('guestban_removeplayer')): ?>
						<input class="button dark" type="submit" name="removeList" id="removeList" value="<?php echo Utils::t('Remove from the list'); ?>" />
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	</form>
</section>

<section class="cadre right section-guestban">

	<?php if (AdminServAdminLevel::hasPermission('guestban_addplayer')): ?>
		<h1><?php echo Utils::t('Add'); ?></h1>
		<div class="content last addPlayer">
			<form method="post" action="?p=<?php echo USER_PAGE; ?>">
				<div>
					<select class="width2" name="addPlayerList" id="addPlayerList"<?php if ($data['players']['count'] == 0): echo ' hidden="hidden"'; endif; ?>>
						<option value="none"><?php echo Utils::t('Select a player'); ?></option>
						<?php echo $data['players']['listOptions']; ?>
						<option value="more"><?php echo Utils::t('Enter another login'); ?></option>
					</select>
					<input class="text width2" type="text" name="addPlayerLogin" id="addPlayerLogin"
						data-default-value="<?php echo Utils::t('Player login'); ?>"
						value="<?php echo Utils::t('Player login'); ?>"
						<?php if ($data['players']['count'] != 0): echo ' hidden="hidden"'; endif; ?> />
					<select class="addPlayerTypeList" name="addPlayerTypeList" id="addPlayerTypeList">
						<option value="none"><?php echo Utils::t('Add in the'); ?></option>
						<option value="guestlist">Guestlist</option>
						<option value="blacklist">Blacklist</option>
					</select>
					<input class="button dark" type="submit" name="addPlayer" id="addPlayer" value="<?php echo Utils::t('Add'); ?>" />
				</div>
			</form>
		</div>
	<?php endif; ?>

	<?php if (isset($data['playlistDirectory']) && $data['playlistDirectory'] != 'Not directory'): ?>
		<div id="playlists">
			<form method="post" action="?p=<?php echo USER_PAGE; ?>">
				<h1>
					<?php echo Utils::t('Playlists'); ?>
					<?php if (AdminServAdminLevel::hasPermission('guestban_playlist_new')): ?>
						<span id="form-new-playlist" hidden="hidden">
							<select name="createPlaylistType" id="createPlaylistType">
								<option value="none"><?php echo Utils::t('Type'); ?></option>
								<option value="guestlist">Guestlist</option>
								<option value="blacklist">Blacklist</option>
							</select>
							<input class="text" type="text" name="createPlaylistName" id="createPlaylistName"
								data-playlistname="<?php echo Utils::t('Playlist name'); ?>"
								value="<?php echo Utils::t('Playlist name'); ?>" />
							<input class="button dark" type="submit" name="createPlaylistValid" id="createPlaylistValid" value="<?php echo Utils::t('Create'); ?>" />
						</span>
					<?php endif; ?>
				</h1>
			</form>

			<div class="title-detail">
				<ul>
					<?php if (AdminServAdminLevel::hasPermission('guestban_playlist_new')): ?>
						<li>
							<a id="clickNewPlaylist" href=""
								data-cancel="<?php echo Utils::t('Cancel'); ?>"
								data-newplaylist="<?php echo Utils::t('New playlist'); ?>">
								<?php echo Utils::t('New playlist'); ?>
							</a>
						</li>
					<?php endif; ?>
					<li class="last"><input type="checkbox" name="checkAllPlaylists" id="checkAllPlaylists" value="" /></li>
				</ul>
			</div>

			<form method="post" action="?p=<?php echo USER_PAGE; ?>">
			<table>
				<thead>
					<tr>
						<th class="thleft"><?php echo Utils::t('Playlist'); ?></th>
						<th><?php echo Utils::t('Type'); ?></th>
						<th><?php echo Utils::t('Contains'); ?></th>
						<th><?php echo Utils::t('Modified'); ?></th>
						<th class="thright"></th>
					</tr>
				</thead>
				<tbody>
					<tr class="table-separation"><td colspan="5"></td></tr>
					<?php if (isset($data['playlistDirectory']['files']) && !empty($data['playlistDirectory']['files'])): ?>
						<?php
							$defaultFilename = ['guestlist.txt','blacklist.txt','guestlist.xml','blacklist.xml'];
						?>
						<?php foreach ($data['playlistDirectory']['files'] as $file): ?>
							<?php if (in_array($file['filename'], $defaultFilename) || ($isDoubleExt = in_array(File::getDoubleExtension($file['filename']), AdminServConfig::$PLAYLIST_EXTENSION))): ?>
								<?php
									$playlistData = AdminServ::getPlaylistData($data['gameDataDirectory'].'Config/'.$file['filename']);
									if (isset($playlistData['logins'])){
										$countDataLogins = count($playlistData['logins']);
										$nbPlayers = ($countDataLogins > 1) ? $countDataLogins.' '.Utils::t('players') : '1 '.Utils::t('player');
									} else {
										$nbPlayers = '0 '.Utils::t('player');
									}
									$parseExtIndex = ($isDoubleExt) ? -13 : -4;
									$filename = substr((string)$file['filename'], 0, $parseExtIndex);
								?>
								<tr>
									<td class="imgleft">
										<img src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>images/16/finishgrey.png" alt="" />
										<span title="<?php echo $file['filename']; ?>"><?php echo $filename; ?></span>
									</td>
									<td><?php echo ucfirst((string)$playlistData['type']); ?></td>
									<td><?php echo $nbPlayers; ?></td>
									<td><?php echo date('d-m-Y', $file['mtime']); ?></td>
									<td class="checkbox">
										<input type="checkbox" name="playlist[]" value="<?php echo $playlistData['type'].'|'.$file['filename']; ?>" />
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php else: ?>
						<tr class="no-line"><td colspan="5"><?php echo Utils::t('No playlist'); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if (AdminServAdminLevel::hasPermission(['guestban_playlist_save','guestban_playlist_load','guestban_playlist_delete'])): ?>
				<div class="options">
					<div class="fright">
						<div class="selected-files-label locked">
							<span class="selected-files-title"><?php echo Utils::t('For the selection'); ?></span>
							<span class="selected-files-count">(0)</span>
							<div class="selected-files-option">
								<?php if (AdminServAdminLevel::hasPermission('guestban_playlist_delete')): ?>
									<input class="button dark" type="submit" name="deletePlaylist" id="deletePlaylist" value="<?php echo Utils::t('Delete'); ?>" />
								<?php endif; ?>
								<?php if (AdminServAdminLevel::hasPermission('guestban_playlist_load')): ?>
									<input class="button dark" type="submit" name="loadPlaylist" id="loadPlaylist" value="<?php echo Utils::t('Load'); ?>" />
								<?php endif; ?>
								<?php if (AdminServAdminLevel::hasPermission('guestban_playlist_save')): ?>
									<input class="button dark" type="submit" name="savePlaylist" id="savePlaylist" value="<?php echo Utils::t('Save'); ?>" />
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
			</form>
		</div>
	<?php endif; ?>

</section>
