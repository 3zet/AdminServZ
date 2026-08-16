<?php
	// Column labels -- GetCurrentRanking field key => display label
	$colLabels = [
		'Rank'            => Utils::t('Rank'),
		'NickName'        => Utils::t('Nickname'),
		'Login'           => Utils::t('Login'),
		'Score'           => Utils::t('Score'),
		'BestTime'        => Utils::t('Best time'),
		'NbrLapsFinished' => Utils::t('Laps'),
	];
	$columns = $data['results']['columns'] ?? ['Rank', 'NickName', 'Login', 'Score'];
?>
<section class="cadre section-results-main">

	<h1><?php echo Utils::t('Results'); ?></h1>
	<div class="title-detail">
		<ul>
			<?php if( !empty($data['results']['modeName']) ): ?>
				<li><span class="results-mode-label"><?php echo $data['results']['modeName']; ?></span></li>
			<?php endif; ?>
			<li class="last">
				<span id="resultsAutoRefresh" data-on="<?php echo Utils::t('Auto-refresh: on'); ?>" data-off="<?php echo Utils::t('Auto-refresh: off'); ?>">
					<?php echo Utils::t('Auto-refresh: on'); ?>
				</span>
			</li>
		</ul>
	</div>

	<div id="resultsTable" data-columns="<?php echo htmlspecialchars(json_encode($columns)); ?>">
		<table>
			<thead>
				<tr>
					<?php foreach( $columns as $i => $col ): ?>
						<th class="<?php
							if($i === 0){ echo 'thleft '; }
							if($i === count($columns) - 1){ echo 'thright '; }
							echo 'col-'.strtolower((string) $col);
						?>"><?php echo $colLabels[$col] ?? $col; ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<tr class="table-separation"><td colspan="<?php echo count($columns); ?>"></td></tr>
				<?php if( $data['results']['count'] > 0 ): ?>
					<?php foreach( $data['results']['lst'] as $row ): ?>
						<tr>
							<?php foreach( $columns as $col ): ?>
								<?php if($col === 'Rank'): ?>
									<td class="results-rank">#<?php echo $row['Rank']; ?></td>
								<?php elseif($col === 'NickName'): ?>
									<td class="imgleft">
										<img src="<?php echo AdminServConfig::$PATH_RESOURCES; ?>images/16/solo.png" alt="" />
										<?php echo $row['NickName'] ?? '—'; ?>
									</td>
								<?php else: ?>
									<td><?php echo $row[$col] !== null ? $row[$col] : '—'; ?></td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr class="no-line">
						<td colspan="<?php echo count($columns); ?>"><?php echo is_string($data['results']['lst']) ? $data['results']['lst'] : Utils::t('No player'); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="options">
		<div class="fleft">
			<span class="nb-line"><?php echo $data['results']['count']; ?> <?php echo Utils::t('players'); ?></span>
		</div>
	</div>

</section>
