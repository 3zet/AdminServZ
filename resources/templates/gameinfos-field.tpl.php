<?php
	// FIX: some fields (LapsTimeLimit) are DURATIONS in milliseconds
	// on the server side, but the process (getGameInfosStructFromPOST) expects a
	// value in SECONDS to call TimeDate::secToMillisec(). Without this
	// conversion on display, the raw value (ms) was sent back as-is
	// then re-multiplied by 1000 on save, causing a
	// 32-bit XML-RPC integer overflow (e.g. 60000000000).
	$timeFields = ['LapsTimeLimit'];
	$isTimeField = in_array($data['gameInfosField']['id'], $timeFields);
?>
<tr>
	<td class="key"><label for="Next<?php echo $data['gameInfosField']['id']; ?>"><?php echo Utils::t($data['gameInfosField']['name']); ?></label></td>
	<?php if ($data['gameInfosField']['gameInfos']['curr'] != null): ?>
		<td class="value">
			<input class="text width2" type="text" name="Curr<?php echo $data['gameInfosField']['id']; ?>" id="Curr<?php echo $data['gameInfosField']['id']; ?>" readonly="readonly" value="<?php
				if (isset($data['gameInfosField']['gameInfos']['curr'][$data['gameInfosField']['id']])){
					$v = $data['gameInfosField']['gameInfos']['curr'][$data['gameInfosField']['id']];
					echo $isTimeField ? TimeDate::millisecToSec($v) : $v;
				}
			?>" />
		</td>
	<?php endif; ?>
	<td class="value">
		<input class="text width2" type="<?php echo (isset($data['gameInfosField']['gameInfos']['next'][$data['gameInfosField']['id']]) && is_numeric($data['gameInfosField']['gameInfos']['next'][$data['gameInfosField']['id']])) ? 'number" min="0"' : 'text'; ?>" name="Next<?php echo $data['gameInfosField']['id']; ?>" id="Next<?php echo $data['gameInfosField']['id']; ?>" value="<?php
			if (isset($data['gameInfosField']['gameInfos']['next'][$data['gameInfosField']['id']])){
				$v = $data['gameInfosField']['gameInfos']['next'][$data['gameInfosField']['id']];
				echo $isTimeField ? TimeDate::millisecToSec($v) : $v;
			}
		?>" />
	</td>
	<td class="preview"></td>
</tr>
