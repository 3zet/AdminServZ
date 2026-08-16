/**
* General functions
*/
function getResourcesPath(){
	return $.trim( $('#path_resources').text() );
}
function getPath(){
	return $.trim( $('.path').text() );
}
function getMode(){
	return $.trim( $('#detailMode').data('statusmode') );
}
function getCurrentSort(){
	return $('#currentSort').val();
}
function setCurrentSort(sort){
	return $('#currentSort').val(sort);
}
function scrollTop(){
	$('html, body').animate({  
		scrollTop: 0
	}, 'slow');
}
function scrollBottom(){
	$('html, body').animate({  
		scrollTop: $(document).height() - $(window).height()
	}, 'slow');
}
function getUrlParams(key){
	var params = window.location.search.substring(1);
	if(!key){
		return params;
	}
	else{
		var index = params.indexOf(key);
		if(index !== -1){
			return params.substring(index).split('&')[0].substring(2);
		}
	}
}


/**
* Display the value converted from seconds -> minutes
*
* @param int sec -> The value in seconds
*/
function secToMin(sec){
	if(sec == '' || sec == undefined || isNaN(sec) ){
		sec = 0;
	}
	return round( (parseInt(sec) / 60), 1);
}


/**
* Math.round with precision
*
* @param int value     -> Value to round
* @param int precision -> Number of decimal digits
*/
function round(value, precision){
	power = Math.pow(10, precision);
	return (Math.ceil(value * power)) / power;
}


/**
* Display the error text
*/
function error(text, hide){
	var selector = $('#error');
	selector.fadeIn('fast').html(text);
	if( selector.attr('hidden') ){
		selector.removeAttr('hidden');
	}
	
	if(hide){
		setTimeout(function(){
			selector.attr('hidden', true).fadeOut('fast');
		}, 4000);
	}
}


/**
* Display the error text
*/
function info(text, hide){
	var selector = $('#info');
	selector.fadeIn('fast').html(text);
	if( selector.attr('hidden') ){
		selector.removeAttr('hidden');
	}
	
	if(hide){
		setTimeout(function(){
			selector.attr('hidden', true).fadeOut('fast');
		}, 4000);
	}
}


/**
* Quick administration (restart, next, endround)
*
* @param string cmd -> Name of the method to use
*/
function speedAdmin(cmd){
	$.post(getResourcesPath()+'ajax/speed_admin.php', {cmd: cmd}, function(response){
		if(response != 'true'){
			error('Error: '+response);
		}
		setTimeout(function(){
			var sort = getCurrentSort();
			if( $('body').hasClass('section-index') ){
				getCurrentServerInfo('', sort);
			}
			else if( $('body').hasClass('section-maps-list') ){
				getMapList('', sort);
			}
			$('.speed-admin a.locked').removeClass('locked');
		}, 2000);
	});
}


/**
* Get the list of admin levels for the selected server
*/
function getServerAdminLevel(){
	var serverName = $('#as_server').val();
	var adminLevelList = '';
	
	$.getJSON(getResourcesPath()+'ajax/get_server_adminlevel.php', {srv: serverName}, function(response){
		if(response.levels != null){
			$.each(response.levels, function(i, n){
				if(response.last != null && response.last == n){ var selected = ' selected="selected"'; }
				else{ var selected = ''; }
				adminLevelList += '<option value="'+n+'"'+selected+'>'+n+'</option>';
			});
			
			$('#as_adminlevel').html(adminLevelList);
		}
		else{
			$('#as_adminlevel').empty();
			error( $('#as_adminlevel').data('error') );
		}
	});
}


/**
* Get the current server's information (map, server, stats, players)
*/
function getCurrentServerInfo(mode, sort){
	var path_ressources = getResourcesPath();
	if(!mode){
		mode = getMode();
	}
	if(sort){
		setCurrentSort(sort);
	}
	var isTeamGameMode = $('#isTeamGameMode').val();
	
	$.getJSON(getResourcesPath()+'ajax/get_current_serverinfo.php', {mode: mode, sort: sort}, function(data){
		if(data != null){
			// Map
			if(data.map != null){
				$('#map_name').html(data.map.name);
				$('#map_author').html(data.map.author);
				$('#map_enviro').html(data.map.enviro+'<img src="'+path_ressources+'images/env/'+data.map.enviro.toLowerCase()+'.png" alt="" />');
				$('#map_uid').html(data.map.uid);
				if(data.srv.gameModeScriptName){
					var gameModeName = data.srv.gameModeScriptName+' <span class="scriptName">('+data.srv.gameModeName+')</span>';
				}else{
					var gameModeName = data.srv.gameModeName;
				}
				$('#map_gamemode').html(gameModeName).attr('class', '').addClass('value '+data.srv.gameModeName.toLowerCase() );
				if(data.map.thumb){
					$('#map_thumbnail').html('<img src="data:image/jpeg;base64,'+data.map.thumb+'" alt="'+$('#map_thumbnail').data('text-thumbnail')+'" />');
				}
				if(data.map.scores){
					$('#ScoreTeamBlue').val(data.map.scores.blue);
					$('#ScoreTeamRed').val(data.map.scores.red);
				}
			}
			
			// Server
			if(data.srv != null){
				$('#server_name').html(data.srv.name);
				$('#server_status').html(data.srv.status);
			}
			
			// Stats
			if(data.net != null){
				$('#network_uptime').html(data.net.uptime);
				$('#network_nbrconnection').html(data.net.nbrconnection);
				$('#network_meanconnectiontime').html(data.net.meanconnectiontime);
				$('#network_meannbrplayer').html(data.net.meannbrplayer);
				$('#network_recvnetrate').html(data.net.recvnetrate);
				$('#network_sendnetrate').html(data.net.sendnetrate);
				$('#network_totalreceivingsize').html(data.net.totalreceivingsize);
				$('#network_totalsendingsize').html(data.net.totalsendingsize);
			}
			
			// Players
			if(data.ply != null && !$('#playerlist').isChecked() ){
				var out = '';
				
				// Build the table
				out += '<tr class="table-separation"><td colspan="'; if(isTeamGameMode){ out += '6'; }else{ out += '5'; } out += '"></td></tr>';
				if( typeof(data.ply) == 'object' ){
					$.each(data.ply, function(i, player){
						out += '<tr class="'; if(i%2){ out += 'even'; }else{ out += 'odd'; } out += '">';
							if(isTeamGameMode && mode == 'detail'){
								out += '<td class="detailModeTd imgleft"><span class="team_'+player.TeamId+'" title="'+player.TeamName+'">&nbsp;</span>'+player.TeamName+'</td>';
							}
							out += '<td class="imgleft"><img src="'+path_ressources+'images/16/solo.png" alt="" />'+player.NickName+'</td>';
							if( !isTeamGameMode && mode == "detail" ){
								out += '<td class="imgleft"><img src="'+path_ressources+'images/16/leagueladder.png" alt="" />'+player.LadderRanking+'</td>';
							}
							out += '<td>'+player.Login+'</td>'
							+'<td>'+player.PlayerStatus+'</td>'
							+'<td class="checkbox"><input type="checkbox" name="player[]" value="'+player.Login+'" /></td>'
						+'</tr>';
					});
					
					if( $('#checkAll').attr('disabled') ){
						$('#checkAll').prop('disabled', false);
					}
				}
				else{
					if( !$('#checkAll').attr('disabled') ){
						$('#checkAll').prop('disabled', true);
					}
					out += '<tr class="no-line"><td class="center" colspan="'; if(isTeamGameMode){ out += '6'; }else{ out += '5'; } out += '">'+data.ply+'</td></tr>';
				}
				
				// HTML
				$('#playerlist table tbody').html(out);
				$('.cadre.right .options .nb-line').html(data.nbp);
				if( $('#playerlist').hasClass('loading') ){
					$('#playerlist').removeClass('loading');
				}
			}
		}
	});
}


/**
* Convert a color string to HTML
*
* @param string str
*/
(function($){
	$.fn.getColorHtml = function(str){
		var selector = $(this);
		
		$.getJSON(getResourcesPath()+'ajax/get_colorstr.php', {t: str}, function(data){
			if(data != null){
				selector.html(data.str);
			}
		});
	};
})(jQuery);


/**
* Get the config for the selected gameMode
*/
function getCurrentGameModeConfig(){
	var gameMode = $('#NextGameMode option:selected').text();
	var selector = $('#gameMode-'+gameMode.toLowerCase() );
	
	// Close all modes by default
	if( $('body').hasClass('section-maps-creatematchset') ){
		var section = '.section-maps-creatematchset';
	}else{
		var section = '.section-gameinfos';
	}
	$.each( $(section+' .content.gameinfos fieldset'), function(i, n){
		if( !$(this).hasClass('gameinfos_general') && !$(this).hasClass('gameinfos_teaminfos') && !$(this).hasClass('gameinfos_script_settings_inline') ){
			if( $(this).attr('hidden') ){
				$(this).hide();
			}
			else{
				$(this).hide();
				$(this).attr('hidden', true);
			}
		}
	});
	
	// Show the selected game mode
	if( selector.attr('hidden') ){
		selector.slideDown('fast').removeAttr('hidden');
	}
}


/**
* Get the settings for the current script
*/
function getScriptSettings(){
	$.post(getResourcesPath()+'ajax/script_settings.php', {method: 'get'}, function(data){
		if( !data ){ return; }

		var $section = $('#tm2020-script-settings');
		var $inner   = $('#inlineScriptSettings');

		if( data.Name ){
			var shortName = data.Name.replace(/\.Script\.txt$/i, '').replace(/.*[\/\\]/, '');
			$section.find('.script-settings-name').text('\u2014 ' + shortName);
		}

		if( !data.ParamDescs || typeof data.ParamDescs !== 'object' || Object.keys(data.ParamDescs).length === 0 ){
			$section.attr('hidden', true);
			return;
		}

		$.post(getResourcesPath()+'ajax/script_settings.php', {method: 'getcurrent'}, function(cur){
			var current = (cur && typeof cur === 'object') ? cur : {};
			renderInlineScriptSettings(data, current, $inner, $section);
		}, 'json').fail(function(){
			renderInlineScriptSettings(data, {}, $inner, $section);
		});
	}, 'json');
}


/**
 * Render inline script settings as a full table with descriptions and grouping (TM2020 desktop)
 */
function renderInlineScriptSettings(info, current, $inner, $section){
	var params = info.ParamDescs || [];
	var extraDescs = info._extraDescs || {};

	// Hidden parameters -- names can be added here to skip them
	var ignored = {};

	// --- group by S_ prefix ---
	var groups  = {};
	var gOrder  = [];

	$.each(params, function(i, param){
		if( ignored[param.Name] ){ return; } // skip
		// Prefix: "S_TimeLimit" -> "Time", "S_WarmUpNb" -> "WarmUp", no prefix -> "General"
		var m     = param.Name.match(/^S_([A-Z][a-z]+)/);
		var group = m ? m[1] : 'General';
		if( !groups[group] ){ groups[group] = []; gOrder.push(group); }
		groups[group].push(param);
	});

	// Strip ManiaPlanet style tags ($<$tXXXtext$>, $z, $s, $i, $l, $6F9 etc.)
	function stripMlTags(str){
		if( !str ){ return ''; }
		return str
			.replace(/\$[0-9a-fA-F]{3}/g, '')   // kolory $RGB
			.replace(/\$[lL]\[.*?\]/g, '')        // linki $l[url]
			.replace(/\$[lLhH]<.*?>/g, '')        // tagi <text>
			.replace(/\$[lLhH]/g, '')
			.replace(/\$[zZsSiIoObBnNtTuUmMkKgGwW]/g, '') // style
			.replace(/\$</g, '').replace(/\$>/g, '')       // remaining brackets
			.replace(/\$/g, '')
			.trim();
	}

	var scriptDesc = stripMlTags(info.Description || '');

	var html = (scriptDesc ? '<p class="sset-script-desc">' + $('<div/>').text(scriptDesc).html() + '</p>' : '')
		+ '<table class="sset-table">'
		+ '<thead><tr>'
		+ '<th class="sset-th sset-th-name">Parameter</th>'
		+ '<th class="sset-th sset-th-value">Value</th>'
		+ '<th class="sset-th sset-th-default">Default</th>'
		+ '<th class="sset-th sset-th-desc">Description</th>'
		+ '</tr></thead>'
		+ '<tbody>';

	$.each(gOrder, function(gi, group){
		$.each(groups[group], function(pi, param){
			var val     = (current[param.Name] !== undefined) ? current[param.Name] : param.Default;
			var defVal  = param.Default;
			// Description: first from extra, then from API, then empty
			var apiDesc = (param.Desc && param.Desc.trim()) ? param.Desc.trim() : '';
			var desc    = (extraDescs[param.Name] && extraDescs[param.Name].trim())
				? extraDescs[param.Name].trim()
				: apiDesc;

			// Changed indicator
			var changed = String(val) !== String(defVal) ? ' sset-changed' : '';

			// Input field
			var field = '';
			var typeLC = (param.Type || '').toLowerCase();
			if( typeLC === 'boolean' ){
				var sT = (val === true || val === 'True'  || val === 1 || val === '1') ? ' selected="selected"' : '';
				var sF = (!sT) ? ' selected="selected"' : '';
				field = '<select class="text sset-input" data-name="' + $('<div/>').text(param.Name).html()
					+ '" data-type="Boolean" data-default="' + defVal + '">'
					+ '<option value="True"' + sT + '>True</option>'
					+ '<option value="False"' + sF + '>False</option>'
					+ '</select>';
			} else if( typeLC === 'integer' ){
				field = '<input class="text sset-input" type="number" '
					+ 'data-name="' + $('<div/>').text(param.Name).html() + '" data-type="Integer" '
					+ 'data-default="' + defVal + '" value="' + $('<div/>').text(String(val)).html() + '" />';
			} else if( typeLC === 'real' ){
				field = '<input class="text sset-input" type="number" step="any" '
					+ 'data-name="' + $('<div/>').text(param.Name).html() + '" data-type="Real" '
					+ 'data-default="' + defVal + '" value="' + $('<div/>').text(String(val)).html() + '" />';
			} else {
				field = '<input class="text sset-input sset-text" type="text" '
					+ 'data-name="' + $('<div/>').text(param.Name).html() + '" data-type="Text" '
					+ 'data-default="' + defVal + '" value="' + $('<div/>').text(String(val)).html() + '" />';
			}

			// Default badge
			var defBadge = '<span class="sset-default" title="Default: ' + $('<div/>').text(String(defVal)).html() + '">'
				+ $('<div/>').text(String(defVal)).html()
				+ '</span>';

			// Reset link (shown only when value differs from default)
			var resetBtn = '<a class="sset-reset" href="#" title="Reset to default" '
				+ 'data-default="' + $('<div/>').text(String(defVal)).html() + '" '
				+ 'data-type="' + typeLC + '"'
				+ (changed ? '' : ' hidden="hidden"')
				+ '>\u21ba</a>';

			html += '<tr class="sset-row' + changed + '">'
				+ '<td class="sset-name" title="' + $('<div/>').text(param.Name).html() + '">'
				+ $('<div/>').text(param.Name.replace(/^S_/, '')).html()
				+ '</td>'
				+ '<td class="sset-value">' + field + resetBtn + '</td>'
				+ '<td class="sset-default-cell">' + defBadge + '</td>'
				+ '<td class="sset-desc">' + $('<div/>').text(desc).html() + '</td>'
				+ '</tr>';
		});
	});

	html += '</tbody></table>';
	html += '<div class="sset-actions">'
		+ '<button id="inlineScriptSettingsSaveBtn" class="button dark">Apply script settings</button>'
		+ '<a href="#" id="ssetResetAll" class="sset-reset-all">Reset all to default</a>'
		+ '<span id="inlineScriptSettingsResult" class="script-result-msg" hidden="hidden"></span>'
		+ '</div>';

	$inner.html(html);
	$section.removeAttr('hidden');

	// Reset a single field
	$inner.on('click', '.sset-reset', function(e){
		e.preventDefault();
		var $row  = $(this).closest('tr');
		var $inp  = $row.find('.sset-input');
		var def   = $(this).data('default');
		var type  = $(this).data('type');
		if( type === 'boolean' ){
			$inp.val( String(def) === 'True' || String(def) === '1' || def === true ? 'True' : 'False' );
		} else {
			$inp.val(def);
		}
		$row.removeClass('sset-changed');
		$(this).attr('hidden', true);
	});

	// Reset all
	$inner.on('click', '#ssetResetAll', function(e){
		e.preventDefault();
		$inner.find('.sset-input').each(function(){
			var def  = $(this).data('default');
			var type = $(this).data('type').toLowerCase();
			if( type === 'boolean' ){
				$(this).val( String(def) === 'True' || String(def) === '1' || def === true ? 'True' : 'False' );
			} else {
				$(this).val(def);
			}
			$(this).closest('tr').removeClass('sset-changed');
			$(this).siblings('.sset-reset').attr('hidden', true);
		});
	});

	// Changed highlight on input
	$inner.on('change input', '.sset-input', function(){
		var $row = $(this).closest('tr');
		var def  = String($(this).data('default'));
		var cur  = String($(this).val());
		var ch   = (cur !== def);
		$row.toggleClass('sset-changed', ch);
		$row.find('.sset-reset').attr('hidden', ch ? null : true);
	});
}


/**
 * Save inline script settings to the server (TM2020)
 */
function setScriptSettings(){
	var params = [];
	$('.sset-input').each(function(){
		params.push({ name: $(this).data('name'), value: $(this).val(), type: $(this).data('type') });
	});

	var $result = $('#inlineScriptSettingsResult');
	var $btn    = $('#inlineScriptSettingsSaveBtn');
	$result.attr('hidden', true);
	$btn.prop('disabled', true);

	$.post(getResourcesPath()+'ajax/script_settings.php', {method: 'set', params: params}, function(response){
		$btn.prop('disabled', false);
		if( response === true || response === 'true' || response === 1 ){
			$result.text('Script settings applied.').removeClass('script-err').addClass('script-ok').removeAttr('hidden');
			// Refresh changed indicators
			$('.sset-input').each(function(){
				$(this).closest('tr').removeClass('sset-changed');
				$(this).data('default', $(this).val());
				$(this).siblings('.sset-reset').attr('hidden', true);
			});
		} else {
			$result.text(response || 'Error').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
			error(response);
			scrollTop();
		}
	});
}




/**
* Get the server's chat lines
*
* @param bool hideServerLines -> Whether to show lines coming from a server manager
* @return string html
*/
function getChatServerLines(hideServerLines){
	$.getJSON(getResourcesPath()+'ajax/get_chatserverlines.php', {s: hideServerLines}, function(data){
		$('#chat').html(data);
	});
}


/**
* Add a line (nickname + message) to the server chat
*/
function addChatServerLine(){
	var nickname = $('#chatNickname').val();
	if( nickname == $('#chatNickname').data('default-value') ){
		nickname = '';
	}
	var color = $('#chatColor').val();
	var message = $('#chatMessage').val();
	var destination = $('#chatDestination').val();
	var hideServerLines = $('#checkServerLines').data('val');
	
	$.post(getResourcesPath()+'ajax/add_chatserverline.php', {nic: nickname, clr: color, msg: message, dst: destination}, function(response){
		if(response != null){
			getChatServerLines(hideServerLines);
			$('#chatMessage').val('');
		}
	});
}


/**
* Initialize the Ajax uploader
*/
function initializeUploader(){
	var formSelector = $('#formUpload');
	var statusFiles = [];
	var uploader = new qq.FileUploader({
		element: formSelector[0],
		action: getResourcesPath()+'ajax/upload.php',
		maxConnections: 2,
		params: getUploaderUserParams(),
		template:
		'<div class="qq-uploader">'
			+ '<div class="qq-upload-drop-area"><span>'+ formSelector.data('dropfiles') +'</span></div>'
			+ '<div class="qq-upload-button">'+ formSelector.data('uploadfile') +'</div>'
			+ '<ul class="qq-upload-list"></ul>'
		+ '</div>',
		fileTemplate:
		'<li>'
			+ '<span class="qq-upload-file"></span>'
			+ '<span class="qq-upload-spinner"><span class="qq-progress-bar"></span></span>'
			+ '<span class="qq-upload-size"></span>'
			+ '<a class="qq-upload-cancel" href="./">'+ formSelector.data('cancel') +'</a>'
			+ '<span class="qq-upload-failed-text">'+ formSelector.data('failed') +'</span>'
		+ '</li>',
		onProgress: function(id, fileName, loaded, total){
			window.onbeforeunload = function(){
				return formSelector.data('uploadnotfinished');
			}
			$.each( $('.qq-upload-list li'), function(key, value){
				var uploadSizeSelector = $(this).find('.qq-upload-size');
				var text = uploadSizeSelector.text();
				var newtext = text.replace(/from/gi, formSelector.data('from') );
				newtext = newtext.replace(/kb/gi, formSelector.data('kb') );
				newtext = newtext.replace(/mb/gi, formSelector.data('mb') );
				var lastpos = text.indexOf('%');
				var pourcent = text.substring(0, lastpos);
				
				uploadSizeSelector.text(newtext);
				$(this).find('.qq-progress-bar').css('width', pourcent+'px');
			});
		},
		onComplete: function(id, fileName, responseJSON){
			window.onbeforeunload = function(){}
			
			if(uploader._options.params.gtlm){
				if(responseJSON.success){
					var status = true;
				}
				else{
					var status = false;
				}
				statusFiles.push(status);
				
				if(uploader.getInProgress() == 0){
					var allowRedirect = true;
					for(var i = 0; i < statusFiles.length; i++){
						if(statusFiles[i] === false){
							allowRedirect = false;
							break;
						}
					}
					
					if(allowRedirect){
						location.href = '?p='+formSelector.data('mapspagename');
					}
				}
			}
		},
		onCancel: function(id, fileName){
			window.onbeforeunload = function(){}
		},
		messages: {
			typeError: formSelector.data('type-error'),
			sizeError: formSelector.data('size-error'),
			minSizeError: formSelector.data('minsize-error'),
			emptyError: formSelector.data('empty-error'),
			onLeave: formSelector.data('onleave')
		},
		showMessage: function(message){
			error(message);
		}
	});
	
	return uploader;
}


/**
* Get the uploader's user settings
*/
function getUploaderUserParams(){
	return {
		path: getPath(),
		type: $('.transferMode li.selected input').val(),
		mset: ( $('#SaveCurrentMatchSettings').attr('checked') ) ? true : false,
		gtlm: ( $('#GotoListMaps').attr('checked') ) ? true : false
	};
}


/**
* Get the server's map list
*/
function getMapList(mode, sort){
	var path_ressources = getResourcesPath();
	if(!mode){
		mode = getMode();
	}
	if(sort){
		setCurrentSort(sort);
	}
	
	$.getJSON(getResourcesPath()+'ajax/get_maplist.php', {mode: mode, sort: sort}, function(data){
		if(data != null){
			if(data.lst != null && !$('#maplist').isChecked() ){
				var out = '';
				
				// Build the table
				out += '<tr class="table-separation"><td colspan="6"></td></tr>';
				if( typeof(data.lst) == 'object' ){
					$.each(data.lst, function(i, map){
						out += '<tr'; if(data.cid == i){ out += ' id="currentMap"'; } out += ' class="'; if(i%2){ out += 'even'; }else{ out += 'odd'; } if(data.cid == i){ out += ' current'; } out += '">'
							+'<td class="imgleft"><img src="'+path_ressources+'images/16/map.png" alt="" />'
								+'<span title="'+map.FileName+'">'+map.Name+'</span>'
								if(mode == 'detail'){
									out += '<span class="detailModeTd">'+map.UId+'</span>';
								}
							out += '</td>'
							+'<td class="imgcenter"><img src="'+path_ressources+'images/env/'+map.Environment.toLowerCase()+'.png" alt="" />'+map.Environment+'</td>'
							+'<td>'+map.Author+'</td>';
							if(mode == 'detail'){
								out += '<td>'+map.GoldTime+'</td>'
								+'<td>'+map.CopperPrice+'</td>';
							}
							out += '<td class="checkbox">'; if(data.cid != i){ out += '<input type="checkbox" name="map[]" value="'+map.FileName+'" />'; } out += '</td>'
						+'</tr>';
					});
					
					if( $('#checkAll').attr('disabled') ){
						$('#checkAll').prop('disabled', false);
					}
				}
				else{
					if( !$('#checkAll').attr('disabled') ){
						$('#checkAll').prop('disabled', true);
					}
					out += '<tr class="no-line"><td class="center" colspan="6">'+data.lst+'</td></tr>';
				}
				
				// HTML
				$('#maplist table tbody').html(out);
				$('.cadre.right .options .nb-line').html(data.nbm.count+' '+data.nbm.title);
				if( $('#maplist').hasClass('loading') ){
					$('#maplist').removeClass('loading');
				}
			}
		}
	});
}


/**
* Get the list of map files to rename
*/
(function($){
	$.fn.getMapRenameList = function(){
		var out = '';
		var formSelector = $('#form-rename-map');
		var list = $(this);
		if( list.length > 0 ){
			out += '<ul>';
			list.each(function(){
				var mapName = $(this).val().replace(getUrlParams('d'), '');
				if(mapName.length > 36){
					var title = ' title="'+mapName+'"';
				}else{
					var title = '';
				}
				out += '<li>'
					+ '<span class="rename-map-name"'+title+'>'+mapName+'</span>'
					+ '<span class="rename-map-arrow">&nbsp;</span>'
					+ '<input class="text width3" type="text" id="renameMapList" name="renameMapList[]" value="'+mapName+'" />'
				+ '</li>';
			});
			out += '</ul>';
		}
		
		// HTML
		out += '<div class="form-input-submit">'
			+ '<input class="button dark" type="button" id="renameMapCancel" name="renameMapCancel" value="'+formSelector.data('cancel')+'" />'+"\n"
			+ '<input class="button dark" type="submit" id="renameMapValid" name="renameAutoValid" value="'+formSelector.data('autorename')+'" />'
			+ '<input class="button dark" type="submit" id="renameMapValid" name="renameMapValid" value="'+formSelector.data('rename')+'" />'
		+ '</div>';
		formSelector.html(out);
	};
})(jQuery);


/**
* Get the folder tree for moving
*/
function getMoveFolderList(nbFiles){
	$.getJSON(getResourcesPath()+'ajax/get_directory_list.php', function(data){
		if(data != null){
			var out = '';
			var formSelector = $('#form-move-map');
			if( nbFiles > 1 ){
				var nbName = nbFiles + ' maps';
			}else{
				var nbName = '1 map';
			}
			out += '<label for="moveDirectoryList">'+formSelector.data('move')+' '+nbName+' '+formSelector.data('inthefolder')+'</label>'
			+ '<select name="moveDirectoryList" id="moveDirectoryList">'
				+ '<option value=".">'+formSelector.data('root')+'</option>';
				$.each(data, function(i, n){
					out += '<option value="'+n.path+'">'+n.level+n.name+'</option>';
				});
			out += '</select>'
			+ '<div class="form-input-submit">'
				+ '<input class="button dark" type="button" id="moveMapCancel" name="moveMapCancel" value="'+formSelector.data('cancel')+'" />'+"\n"
				+ '<input class="button dark" type="submit" id="moveMapValid" name="moveMapValid" value="'+formSelector.data('move')+'" />'
			+ '</div>';
			
			// HTML
			formSelector.html(out);
		}
	});
}

/**
* Form display functions
*/
function slideDownRenameForm(){
	$('#form-rename-map').slideDown('fast');
	$('#renameMap').addClass('active');
	$('.options').addClass('form').find('.selected-files-label').addClass('optHover');
	$('#maplist table tbody tr.selected td.checkbox input').getMapRenameList();
}
function slideUpRenameForm(){
	$('#form-rename-map').slideUp('fast');
	$('#renameMap').removeClass('active');
	$('.options').removeClass('form').find('.selected-files-label').removeClass('optHover');
}
function slideDownMoveForm(){
	$('#form-move-map').slideDown('fast');
	$('#moveMap').addClass('active');
	$('.options').addClass('form').find('.selected-files-label').addClass('optHover');
	if( $('#form-move-map').text() == '' ){
		getMoveFolderList( $('#maplist table tbody tr.selected td.checkbox input').length );
	}
}
function slideUpMoveForm(){
	$('#form-move-map').slideUp('fast');
	$('#moveMap').removeClass('active');
	$('.options').removeClass('form').find('.selected-files-label').removeClass('optHover');
}
function slideDownNewFolderForm(){
	$('#form-new-folder').animate({
		height: '25px',
		marginTop: '6px',
		marginBottom: '6px'
	}, 'fast').removeAttr('hidden');
	$('#newfolder').text( $('#newfolder').data('cancel') );
	$('#newFolderName').select();
}
function slideUpNewFolderForm(){
	$('#form-new-folder').animate({
		height: '0',
		marginTop: '0',
		marginBottom: '0'
	}, 'fast', function(){
		$('#form-new-folder').attr('hidden', true);
		$('#newfolder').text( $('#newfolder').data('new') );
	});
}


/**
* Sort the map list for the "maps-order" page
*/
function setMapsOrderSort(sort, order){
	var resourcesPath = getResourcesPath();
	var list = $('#jsonlist').val();
	
	$.post(getResourcesPath()+'ajax/mapsorder_sort.php', {srt: sort, ord: order, lst: list}, function(data){
		if(data != null){
			var out = '';
			if( typeof(data.lst) == 'object' ){
				$.each(data.lst, function(id, map){
					out += '<li class="ui-state-default">'
						+ '<div class="ui-icon ui-icon-arrowthick-2-n-s"></div>'
						+ '<div class="order-map-name" title="'+map.FileName+'">'+map.Name+'</div>'
						+ '<div class="order-map-env"><img src="'+resourcesPath+'images/env/'+map.Environment.toLowerCase()+'.png" alt="" />'+map.Environment+'</div>'
						+ '<div class="order-map-author"><img src="'+resourcesPath+'images/16/mapauthor.png" alt="" />'+map.Author+'</div>'
					+ '</li>';
				});
			}
			
			// HTML
			$('#sortableMapList').html(out);
			if( $('#sortableMapList').hasClass('loading') ){
				$('#sortableMapList').removeClass('loading');
			}
		}
	}, 'json');
}


/**
* Set up detail-mode display
*/
(function($){
	$.fn.setDetailMode = function(){
		var sort = getCurrentSort();
		var type = $(this).attr('id');
		
		if( $('#detailMode').text() == $('#detailMode').data('textdetail') ){
			if(type == 'maplist'){
				getMapList('detail', sort);
			}
			else if(type == 'playerlist'){
				getCurrentServerInfo('detail', sort);
			}
			$('#detailMode').text( $('#detailMode').data('textsimple') ).data('statusmode', 'detail');
			$(this).addClass('loading').find('table th.detailModeTh').attr('hidden', false);
		}
		else{
			if(type == 'maplist'){
				getMapList('simple', sort);
			}
			else if(type == 'playerlist'){
				getCurrentServerInfo('simple', sort);
			}
			$('#detailMode').text( $('#detailMode').data('textdetail') ).data('statusmode', 'simple');
			$(this).addClass('loading').find('table th.detailModeTh').attr('hidden', true);
		}
	};
})(jQuery);


/**
* Check all checkboxes for a selector
*
* @param bool isChecked -> Whether the "checkAll" checkbox is checked
*/
(function($){
	$.fn.checkAll = function(isChecked){
		var lineSelector = $(this).find('tbody tr[class!="table-separation"]');
		var checkboxSelector = $(this).find('tbody td.checkbox input[type="checkbox"]');
		if(isChecked){
			checkboxSelector.prop('checked', true);
			lineSelector.addClass('selected');
		}
		else{
			checkboxSelector.prop('checked', false);
			lineSelector.removeClass('selected');
		}
	};
})(jQuery);

/**
* Update the "checkAll" checkbox based on whether all lines are checked
*
* @param string checkAllSelector -> Selector for the "checkAll" input
*/
(function($){
	$.fn.updateCheckAll = function(checkAllSelector){
		var nbSelected = $(this).find('tbody tr.selected').length;
		var nbAll = $(this).find('tbody tr[class!="table-separation"]').length;
		if( $('body').hasClass('section-maps-list') ){
			nbAll--;
		}
		
		if(nbSelected == nbAll){
			checkAllSelector.prop('checked', true);
		}
		else if(nbSelected == 0){
			if( checkAllSelector.prop('checked') ){
				checkAllSelector.prop('checked', false);
			}
		}
	};
})(jQuery);


/**
* Update the number of selected files
*/
(function($){
	$.fn.updateNbSelectedLines = function(){
		var nb = $(this).find('tbody tr.selected').length;
		
		if(nb > 0){
			$(this).find('.selected-files-label').removeClass('locked');
		}
		else{
			$(this).find('.selected-files-label').addClass('locked');
		}
		$(this).find('.selected-files-count').text('('+nb+')');
	};
})(jQuery);


/**
* Determine if at least one line is selected
*/
(function($){
	$.fn.isChecked = function(){
		var nb = $(this).find('tbody tr.selected').length;
		
		if(nb > 0){
			return true;
		}
		else{
			return false;
		}
	};
})(jQuery);



/**
* MATCHSETTINGS
*/

/**
* Check that the MatchSettings name doesn't already exist
*
* @param string filename -> The file name
*/
function matchset_getFileExists(filename){
	$.getJSON(getResourcesPath()+'ajax/get_matchset_fileexists.php', {path: getPath(), name: filename}, function(response){
		if(response){
			$('#matchSettingNameExists').attr('hidden', false).hide().fadeIn('fast');
		}
		else{
			$('#matchSettingNameExists').attr('hidden', true).fadeOut('fast');
		}
	});
}

/**
* Get the local map list for creating a matchSettings
*/
function matchset_mapImport(){
	var path = $('#mapsDirectoryList').val();
	var directory = getUrlParams('d');
	
	$.getJSON(getResourcesPath()+'ajax/get_matchset_mapimport.php', {path: path, d: directory}, function(data){
		if(data != null){
			matchset_setNbMapSelection(data.nbm.count);
			$('.creatematchset .maps').removeClass('loading');
		}
	});
}
function matchset_mapImportSelection(){
	var path = $('#mapsDirectoryList').val();
	var directory = getUrlParams('d');
	var resourcesPath = getResourcesPath();
	
	$.getJSON(resourcesPath+'ajax/get_matchset_mapimport.php', {path: path, d: directory, op: 'getSelection'}, function(data){
		if(data != null){
			var out = '';
			
			// Build the table
			out += '<tr class="table-separation"><td colspan="4"></td></tr>';
			if( typeof(data.lst) == 'object' ){
				$.each(data.lst, function(i, map){
					out += '<tr class="'; if(i%2){ out += 'even'; }else{ out += 'odd'; } out += '">'
						+'<td class="imgleft"><img src="'+resourcesPath+'images/16/map.png" alt="" />'
							+'<span title="'+map.FileName+'">'+map.Name+'</span>'
						out += '</td>'
						+'<td class="imgcenter"><img src="'+resourcesPath+'images/env/'+map.Environment.toLowerCase()+'.png" alt="" />'+map.Environment+'</td>';
						if(map.Type){
							out += '<td><span title="'+map.Type.FullName+'">'+map.Type.Name+'</span></td>';
						}
						else{
							out += '<td>-</td>';
						}
						out += '<td>'+map.Author+'</td>';
						out += '<td class="checkbox"><input type="checkbox" name="map[]" value="'+map.FileName+'" /></td>'
					+'</tr>';
				});
				
				if( $('#checkAllMapImport').attr('disabled') ){
					$('#checkAllMapImport').prop('disabled', false);
				}
			}
			else{
				if( !$('#checkAllMapImport').attr('disabled') ){
					$('#checkAllMapImport').prop('disabled', true);
				}
				out += '<tr class="no-line"><td class="center" colspan="4">'+data.lst+'</td></tr>';
			}
			
			// HTML
			$('#mapImportSelectionDialog').removeAttr('hidden').find('table tbody').html(out);
			$('.creatematchset .maps').removeClass('loading');
			$('#mapImportSelectionDialog').dialog({
				title: $('#mapImportSelectionDialog').data('title'),
				modal: true,
				minWidth: 800,
				minHeight: 400,
				buttons: [{
					text: $('#mapImportSelectionDialog').data('select'),
					click: function(){
						var listSelection = $('#mapImportSelectionDialog tbody tr.selected');
						var listSelectionId = [];
						if( listSelection.length > 0 ){
							$.each(listSelection, function(i, n){
								if( n.className.indexOf('selected') !== -1 ){
									listSelectionId.push( parseInt(n.sectionRowIndex - 1) );
								}
							});
						}
						$.getJSON(resourcesPath+'ajax/get_matchset_mapimport.php', {path: path, op: 'setSelection', select: listSelectionId}, function(data){
							if(data != null){
								matchset_setNbMapSelection(data.nbm.count);
								$('#mapImportSelectionDialog').dialog('close');
							}
						});
					}
				}]
			});
		}
	});
}


/**
* Update the number of selected maps
*
* @param int nb
*/
function matchset_setNbMapSelection(nb){
	$('#nbMapSelected').text(nb);
}
/**
* Get and display the MatchSettings selection
*/
function matchset_mapSelection(removeId){
	var path_ressources = getResourcesPath();
	if(removeId){
		var params = {remove: parseInt(removeId - 1)};
	}else{
		var params = '';
	}
	$.getJSON(getResourcesPath()+'ajax/get_matchset_mapselection.php', params, function(data){
		if(data != null){
			var out = '';
			
			// Build the table
			out += '<tr class="table-separation"><td colspan="4"></td></tr>';
			if( typeof(data.lst) == 'object' ){
				$.each(data.lst, function(i, map){
					out += '<tr class="'; if(i%2){ out += 'even'; }else{ out += 'odd'; } out += '">'
						+ '<td class="imgleft"><img src="'+path_ressources+'images/16/map.png" alt="" />'
							+ '<span title="'+map.FileName+'">'+map.Name+'</span>'
						+ '</td>'
						+ '<td class="imgcenter"><img src="'+path_ressources+'images/env/'+map.Environment.toLowerCase()+'.png" alt="" />'+map.Environment+'</td>';
						if(map.Type){
							out += '<td><span title="'+map.Type.FullName+'">'+map.Type.Name+'</span></td>';
						}
						else{
							out += '<td>-</td>';
						}
						out += '<td>'+map.Author+'</td>'
						+ '<td class="checkbox imgcenter"><a href="." title="'+$("#mapSelectionDialog").data("remove")+'"><img src="'+path_ressources+'images/16/delete.png" alt="" /></a></td>'
					+ '</tr>';
				});
			}
			else{
				out += '<tr class="no-line"><td class="center" colspan="4">'+data.lst+'</td></tr>';
			}
			
			// HTML
			matchset_setNbMapSelection(data.nbm.count);
			$('#mapSelectionDialog').removeAttr('hidden').find('table tbody').html(out);
			$('.creatematchset .maps').removeClass('loading');
			$('#mapSelectionDialog').dialog({
				title: $('#mapSelectionDialog').data('title'),
				modal: true,
				minWidth: 800,
				minHeight: 400,
				buttons: [{
					text: $('#mapSelectionDialog').data('close'),
					click: function(){
						$(this).dialog('close');
					}
				}]
			});
		}
	});
}


/* Folders */
function getRenameFolderForm(){
	$('#renameFolderForm').removeAttr('hidden');
	$('#renameFolderForm').dialog({
		title: $('#renameFolderForm').data('title'),
		modal: true,
		resizable: false,
		minWidth: 406,
		buttons: [
			{
				text: $('#renameFolderForm').data('cancel'),
				click: function(){
					$(this).dialog('close');
				}
			},
			{
				text: $('#renameFolderForm').data('rename'),
				click: function(){
					$('#optionFolderHiddenFieldValue').val( $('#renameFolderNewName').val() );
					$('#optionFolderForm').submit();
				}
			},
			
		]
	});
}
function getMoveFolderForm(){
	$('#moveFolderForm').removeAttr('hidden');
	$.getJSON(getResourcesPath()+'ajax/get_directory_list.php', function(data){
		if(data != null){
			var out = '';
			var formSelector = $('#moveFolderForm');
			out += '<label for="moveFormDirectoryList">'+formSelector.data('movethefolder')+'</label>'
			+ '<select name="moveFormDirectoryList" id="moveFormDirectoryList">'
				+ '<option value=".">'+formSelector.data('root')+'</option>';
				$.each(data, function(i, n){
					out += '<option value="'+n.path+'">'+n.level+n.name+'</option>';
				});
			out += '</select>';
			
			// HTML
			formSelector.html(out);
		}
	});
	$('#moveFolderForm').dialog({
		title: $('#moveFolderForm').data('title'),
		modal: true,
		resizable: false,
		minWidth: 406,
		buttons: [
			{
				text: $('#moveFolderForm').data('cancel'),
				click: function(){
					$(this).dialog('close');
				}
			},
			{
				text: $('#moveFolderForm').data('move'),
				click: function(){
					$('#optionFolderHiddenFieldValue').val( $('#moveFormDirectoryList').val() );
					$('#optionFolderForm').submit();
				}
			},
			
		]
	});
}

/**
* Fetch the current ranking (GetCurrentRanking) and refresh the table.
*/
function getResults(){
	$.getJSON(getResourcesPath()+'ajax/get_results.php', {}, function(data){
		if(data == null) return;

		var $resultsDiv = $('#resultsTable');
		var columns = data.columns || ['Rank', 'NickName', 'Login', 'Score'];
		var prevColumns = $resultsDiv.attr('data-columns');
		var newColumnsJson = JSON.stringify(columns);

		var colLabels = {
			'Rank': 'Rank', 'NickName': 'Nickname', 'Login': 'Login',
			'Score': 'Score', 'BestTime': 'Best time', 'NbrLapsFinished': 'Laps'
		};

		// If the game mode changed (different columns) -- rebuild the whole <thead>
		if(prevColumns !== newColumnsJson){
			var headHtml = '<tr>';
			$.each(columns, function(i, col){
				var cls = (i === 0 ? 'thleft ' : '') + (i === columns.length - 1 ? 'thright ' : '') + 'col-'+col.toLowerCase();
				headHtml += '<th class="'+cls+'">'+(colLabels[col] || col)+'</th>';
			});
			headHtml += '</tr>';
			$resultsDiv.find('thead').html(headHtml);
			$resultsDiv.attr('data-columns', newColumnsJson);
		}

		// Mode label in title-detail
		if(data.modeName){
			var $modeLabel = $('.results-mode-label');
			if($modeLabel.length){ $modeLabel.text(data.modeName); }
		}

		var colCount = columns.length;
		var $tbody = $('#resultsTable tbody');
		var html = '<tr class="table-separation"><td colspan="'+colCount+'"></td></tr>';

		if(data.count > 0 && typeof data.lst === 'object'){
			$.each(data.lst, function(idx, row){
				html += '<tr>';
				$.each(columns, function(i, col){
					if(col === 'Rank'){
						html += '<td class="results-rank">#'+row.Rank+'</td>';
					} else if(col === 'NickName'){
						html += '<td class="imgleft"><img src="'+getResourcesPath()+'images/16/solo.png" alt="" />'+(row.NickName || '\u2014')+'</td>';
					} else {
						var val = row[col];
						html += '<td>'+(val !== null && val !== undefined ? val : '\u2014')+'</td>';
					}
				});
				html += '</tr>';
			});
		} else {
			html += '<tr class="no-line"><td colspan="'+colCount+'">'+(typeof data.lst === 'string' ? data.lst : 'No player')+'</td></tr>';
		}

		$tbody.html(html);
		$('.section-results-main .nb-line').text(data.count + ' players');
	});
}


/**
 * ============================================================
 * SCRIPT MANAGER — TM2020
 * Upload and live-edit gamemode scripts
 * ============================================================
 */

/**
 * Open the Script Manager dialog and load data from the server
 */
function openScriptManager(){
	var $dialog = $('#scriptManagerDialog');

	// Initialize jQuery UI dialog (lazy)
	if( !$dialog.hasClass('ui-dialog-content') ){
		$dialog.dialog({
			title:     $dialog.data('title'),
			modal:     true,
			width:     780,
			minHeight: 500,
			resizable: true,
			open: function(){
				$('#scriptManagerTabs').tabs();
				scriptEditor_load();
			},
			buttons: [
				{
					text:  $dialog.data('cancel'),
					click: function(){ $(this).dialog('close'); }
				}
			]
		});
	}
	else{
		$dialog.dialog('open');
		scriptEditor_load();
	}
}

/**
 * Load the script list and the current file's content into the editor
 */
function scriptEditor_load(){
	var $sel    = $('#scriptEditorSelect');
	var $wrap   = $('#scriptEditorWrap');
	var $nolocal= $('#scriptEditorNotLocal');
	var $result = $('#scriptEditorResult');

	$sel.empty().append('<option value="">— loading… —</option>');
	$wrap.attr('hidden', true);
	$nolocal.attr('hidden', true);
	$result.attr('hidden', true);

	$.getJSON(getResourcesPath()+'ajax/script_editor.php?method=get', function(data){
		if( !data || data.error ){
			$result.text(data ? data.error : 'Error').removeAttr('hidden');
			return;
		}

		// Fill the select with the script list
		$sel.empty();
		if( data.scripts && data.scripts.length > 0 ){
			$.each(data.scripts, function(i, name){
				var selected = (name === data.scriptName || name.replace(/\\/g,'/') === (data.scriptName||'').replace(/\\/g,'/'));
				$sel.append('<option value="'+name+'"'+(selected?' selected="selected"':'')+'>'+name+'</option>');
			});
		}
		else{
			$sel.append('<option value="">— no scripts found —</option>');
		}

		// Show the editor or a message about missing local access
		if( data.content !== null ){
			$('#scriptEditorContent').val(data.content);
			$wrap.removeAttr('hidden');
		}
		else{
			$nolocal.removeAttr('hidden');
		}

		// Bind: select change -> load the file
		$sel.off('change.scriptmgr').on('change.scriptmgr', function(){
			scriptEditor_loadFile( $(this).val() );
		});
	}).fail(function(){
		$result.text('AJAX error loading script list').removeAttr('hidden');
	});
}

/**
 * Load the content of the selected script file into the editor
 */
function scriptEditor_loadFile(name){
	if( !name ) return;
	var $wrap   = $('#scriptEditorWrap');
	var $nolocal= $('#scriptEditorNotLocal');
	var $result = $('#scriptEditorResult');

	$result.attr('hidden', true);
	$wrap.attr('hidden', true);

	$.post(getResourcesPath()+'ajax/script_editor.php', {method: 'getfile', name: name}, function(data){
		if( !data || data.error ){
			$result.text(data ? data.error : 'Error').removeAttr('hidden');
			return;
		}
		$('#scriptEditorContent').val(data.content);
		if( data.editable ){
			$wrap.removeAttr('hidden');
		}
		else{
			$nolocal.removeAttr('hidden');
		}
	}, 'json').fail(function(){
		$result.text('AJAX error loading file').removeAttr('hidden');
	});
}

/**
 * Save the edited script (optionally restarting the map)
 */
function scriptEditor_save(){
	var $dialog = $('#scriptManagerDialog');
	var $result = $('#scriptEditorResult');
	var name    = $('#scriptEditorSelect').val();
	var content = $('#scriptEditorContent').val();
	var reload  = $('#scriptEditorReload').is(':checked') ? '1' : '0';

	if( !name ){
		$result.text('No script selected').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
		return;
	}

	$result.attr('hidden', true);
	$('#scriptEditorSaveBtn').prop('disabled', true);

	$.post(getResourcesPath()+'ajax/script_editor.php', {method: 'save', name: name, content: content, reload: reload}, function(data){
		$('#scriptEditorSaveBtn').prop('disabled', false);
		if( !data || data.error ){
			$result.text(data ? data.error : 'Save error').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
		}
		else{
			var msg = data.reloaded ? $dialog.data('reloaded') : $dialog.data('saved');
			if( data.reloadError ){ msg += ' (reload error: '+data.reloadError+')'; }
			$result.text(msg).removeClass('script-err').addClass('script-ok').removeAttr('hidden');
		}
	}, 'json').fail(function(){
		$('#scriptEditorSaveBtn').prop('disabled', false);
		$result.text('AJAX error saving script').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
	});
}

/**
 * Script upload via a hidden <input type="file">
 */
function scriptUpload_send(){
	var $result   = $('#scriptUploadResult');
	var $progress = $('#scriptUploadProgress');
	var $btn      = $('#scriptUploadBtn');
	var $input    = $('#scriptUploadInput');
	var file      = $input[0].files ? $input[0].files[0] : null;
	var $dialog   = $('#scriptManagerDialog');

	if( !file ){
		$result.text('No file selected').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
		return;
	}

	if( !/\.Script\.txt$/i.test(file.name) ){
		$result.text('Only .Script.txt files are allowed').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
		return;
	}

	var activate = $('#scriptUploadActivate').is(':checked') ? '1' : '0';
	var reload   = $('#scriptUploadReload').is(':checked')   ? '1' : '0';
	var subdir   = $('#scriptUploadSubdir').val().trim();

	var url = getResourcesPath()+'ajax/script_upload.php?activate='+activate+'&reload='+reload;
	if( subdir ){ url += '&subdir='+encodeURIComponent(subdir); }

	var formData = new FormData();
	formData.append('qqfile', file);

	$result.attr('hidden', true);
	$progress.removeAttr('hidden');
	$('#scriptUploadFilename').text(file.name);
	$('#scriptUploadStatus').text($dialog.data('uploading'));
	$btn.prop('disabled', true);

	$.ajax({
		url:         url,
		type:        'POST',
		data:        formData,
		processData: false,
		contentType: false,
		dataType:    'json',
		success: function(data){
			$btn.prop('disabled', false);
			$progress.attr('hidden', true);
			if( data && data.success ){
				var msg = $dialog.data('upload-ok')+' '+data.name;
				if( data.activated ){ msg += ' (active)'; }
				if( data.reloaded  ){ msg += ' (reloaded)'; }
				if( data.activateError ){ msg += ' — activate error: '+data.activateError; }
				if( data.reloadError   ){ msg += ' — reload error: '+data.reloadError; }
				$result.text(msg).removeClass('script-err').addClass('script-ok').removeAttr('hidden');
				// Refresh the editor's select
				scriptEditor_load();
			}
			else{
				$result.text(data ? (data.error || $dialog.data('upload-err')) : $dialog.data('upload-err'))
					.removeClass('script-ok').addClass('script-err').removeAttr('hidden');
			}
		},
		error: function(){
			$btn.prop('disabled', false);
			$progress.attr('hidden', true);
			$result.text('AJAX upload error').removeClass('script-ok').addClass('script-err').removeAttr('hidden');
		}
	});
}
