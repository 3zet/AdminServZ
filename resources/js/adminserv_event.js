$(document).ready(function(){
	
	/**
	* Global
	*/
	// Theme/lang: handled by settings-popover.js
	$('#footer-inner a').click(function(event){
		event.preventDefault();
		window.open( $(this).attr('href') );
	});
	
	
	/**
	* Config
	*/
	if( $('body').hasClass('config') ){
		/**
		* Serveurs
		*/
		if( $('body').hasClass('section-config-servers') ){
			// Click on lines
			$('#serverList').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$('#serverList tr').removeClass('selected');
						$(this).find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$('#serverList tr').removeClass('selected');
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
				}
				
				// Update the selected-lines count
				$('.cadre').updateNbSelectedLines();
			});
			// Delete a server
			$('#deleteserver').click(function(){
				if( !confirm( $(this).data('confirm-text') ) ){
					return false;
				}
			});
		}
		else if( $('body').hasClass('section-config-addserver') ){
			$('#addServerAdmLvlSA, #addServerAdmLvlADM, #addServerAdmLvlUSR').blur(function(){
				if( $(this).val() == '' ){
					$(this).val('all');
				}
			});
		}
		else if( $('body').hasClass('section-config-serversorder') ){
			// Manual sort
			$('#sortableServersList').sortable({
				placeholder: 'ui-state-highlight',
				revert: true,
				zIndex: 9999
			}).disableSelection();
			$('#reset').click(function(){
				location.href = $('.section-config-serversorder .cadre form').attr('action');
			});
			$('#save').click(function(){
				var listStr = '';
				var list = $('#sortableServersList li .order-server-name');
				if( list.length > 0 ){
					$.each(list, function(i, n){
						listStr += n.textContent+',';
					});
					$('#list').val(listStr.substring(0, listStr.length-1));
				}
			});
		}
		else if( $('body').hasClass('section-config-adminlevel') ){
			// Click on lines
			$('#levelList').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$('#levelList tr').removeClass('selected');
						$(this).find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$('#levelList tr').removeClass('selected');
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
				}
				
				// Update the selected-lines count
				$('.cadre').updateNbSelectedLines();
			});
			// Delete an admin level
			$('#deletelevel').click(function(){
				if( !confirm( $(this).data('confirm-text') ) ){
					return false;
				}
			});
		}
		else if( $('body').hasClass('section-config-addlevel') ){
			// Access
			$('#defaultAccess, #selectedAccess').sortable({
				connectWith: '.adminlevelAccessList',
				revert: true
			}).disableSelection();
			// Permission
			$('#defaultPermission, #selectedPermission').sortable({
				connectWith: '.adminlevelPermissionList',
				revert: true
			}).disableSelection();
			
			// Submit
			$('#savelevel').click(function(){
				var lists = {
					access: {
						list: '#selectedAccess li',
						target: '#selectedAccessSortList'
					},
					permission: {
						list: '#selectedPermission li',
						target: '#selectedPermissionSortList'
					}
				};
				$.each(lists, function(i, n){
					var out = '';
					var selector = $(n.list);
					if(selector.length > 0){
						selector.each(function(){
							out += $(this).text()+',';
						});
						$(n.target).val(out.substring(0, out.length-1));
					}
				});
			});
		}
		else if( $('body').hasClass('section-config-levelsorder') ){
			// Manual sort
			$('#sortableLevelsList').sortable({
				placeholder: 'ui-state-highlight',
				revert: true,
				zIndex: 9999
			}).disableSelection();
			$('#reset').click(function(){
				location.href = $('.section-config-levelsorder .cadre form').attr('action');
			});
			$('#save').click(function(){
				var listStr = '';
				var list = $('#sortableLevelsList li .order-server-name');
				if( list.length > 0 ){
					$.each(list, function(i, n){
						listStr += n.textContent+',';
					});
					$('#list').val(listStr.substring(0, listStr.length-1));
				}
			});
		}
	}
	/**
	* Front
	*/
	else if( $('body').hasClass('front') ){
		// Adminlevel
		getServerAdminLevel();
		$('#as_server').change(function(){
			getServerAdminLevel();
			if( $('#error').css('display') == 'block' ){
				$('#error').attr('hidden', true).fadeOut('fast');
			}
		});
		$('#as_adminlevel').click(function(){
			if( $(this).html() == '' ){
				getServerAdminLevel();
			}
		});
		
		// Connection
		$(document).keypress(function(event){
			if(event.keyCode == 13){
				$('#connection form').submit();
			}
		});
	}
	/**
	* Not front
	*/
	else{
		/**
		* New folder
		*/
		$('#newfolder').click(function(event){
			event.preventDefault();
			if( $('#form-new-folder').prop('hidden') ){
				slideDownNewFolderForm();
			}
			else{
				slideUpNewFolderForm();
			}
		});
		$('#newFolderName').keypress(function(event){
			if(event.keyCode == 13){
				if( $(this).val() != '' ){
					$('#createFolderForm').submit();
				}
				else{
					slideUpNewFolderForm();
				}
			}
		});
		$('#newFolderValid').click(function(){
			if( $('#newFolderName').val() != '' ){
				return true;
			}
			else{
				slideUpNewFolderForm();
				return false;
			}
		});
		
		
		/**
		* Folder option
		*/
		$('.path').scrollLeft(1000);
		$('.folders .option-folder-list h3').click(function(){
			var selector = $(this).parent().find('ul');
			if( selector.attr('hidden') ){
				selector.slideDown('fast').removeAttr('hidden');
				$(this).find('span').removeClass('arrow-down').addClass('arrow-up');
			}
			else{
				selector.slideUp('fast').attr('hidden', true);
				$(this).find('span').removeClass('arrow-up').addClass('arrow-down');
			}
		});
		$('#renameFolder').click(function(event){
			event.preventDefault();
			$('#optionFolderHiddenFieldAction').val('rename');
			getRenameFolderForm();
		});
		$('#moveFolder').click(function(event){
			event.preventDefault();
			$('#optionFolderHiddenFieldAction').val('move');
			getMoveFolderForm();
		});
		$('#deleteFolder').click(function(event){
			event.preventDefault();
			if( confirm( $(this).data('confirm-text') ) ){
				$('#optionFolderHiddenFieldAction').val('delete');
				$('#optionFolderForm').submit();
			}
		});
		
		
		/**
		* SpeedAdmin
		*/
		$('.speed-admin a').click(function(event){
			event.preventDefault();
			if( !$(this).hasClass('locked') ){
				$(this).addClass('locked');
				speedAdmin( $(this).text() );
			}
		});
		
		
		/**
		* SwitchServer
		*/
		$('#switchServerList').change(function(){
			var page = getUrlParams('p');
			var option = $(this).find('option:selected').val();
			if(page){
				location.href = '?p='+page+'&switch='+option;
			}
			else{
				location.href = '?switch='+option;
			}
		});
		
		
		/**
		* Smooth scroll
		*/
		$('a[href^="#"]').click(function(event){
			event.preventDefault();
			var target = $(this).attr('href');
			$('html, body').animate({  
				scrollTop: $(target).offset().top - 100
			}, 'slow');
		});
		
		
		/**
		* General
		*/
		if( $('body').hasClass('section-index') ){
			// Server info
			setInterval(function(){
				getCurrentServerInfo(getMode(), getCurrentSort() );
			}, 10000);
			
			// Checkbox
			$('#checkAll').click(function(){
				$('#playerlist').checkAll( $(this).attr('checked') );
				
				// Update the selected-lines count
				$('.cadre.right').updateNbSelectedLines();
			});
			
			// Click on lines
			$('#playerlist').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('.cadre.right').updateNbSelectedLines();
					$('.cadre.right').updateCheckAll( $('#checkAll') );
				}
			});
			
			// Detail mode
			$('#detailMode').click(function(event){
				event.preventDefault();
				$('#playerlist').setDetailMode();
			});
			
			// Sorts
			$('#playerlist table th a').click(function(event){
				event.preventDefault();
				$('#playerlist').addClass('loading');
				getCurrentServerInfo(getMode(), $(this).attr('href').split('=')[1]);
			});
		}
		/**
		* Server Options
		*/
		else if( $('body').hasClass('section-srvopts') ){
			// ServerName
			$('#ServerName').keyup(function(event){
				var key = event.keyCode;
				if(key != 13 && key != 37 && key != 39){
					$('#serverNameHtml').getColorHtml( $(this).val() );
				}
			});
			$('#ServerName').blur(function(){
				$('#serverNameHtml').getColorHtml( $(this).val() );
			});
			
			// ServerComment
			$('#ServerComment').keyup(function(event){
				var key = event.keyCode;
				if(key != 37 && key != 39){
					$('#serverCommentHtml').getColorHtml('$i'+ $(this).val() );
				}
			});
			
			// VoteRato
			$('#callVoteRatioDisabled').change(function(){
				if( $(this).val() == '0' ){
					$(this).hide();
					$('#CallVoteRatio')
						.fadeIn('fast').removeAttr('hidden')
						.parent('td').find('.returnDefaultValue').fadeIn('fast').removeAttr('hidden');
					if( $('#CallVoteRatio').val() == -1 ){
						$('#CallVoteRatio').val('0.5');
					}
				}
			});
			// Disable the ratio vote
			$('#resetCallVoteRatio').click(function(event){
				event.preventDefault();
				$(this).fadeOut().attr('hidden', true);
				$('#CallVoteRatio').val('-1').hide().attr('hidden', true);
				$('#callVoteRatioDisabled').fadeIn('fast').removeAttr('hidden').find('option').removeAttr('selected');
				$('#callVoteRatioDisabled option:first').select();
			});
			
			// ClientInputsMaxLatency
			$('#ClientInputsMaxLatency').change(function(){
				if( $(this).val() == 'more' ){
					$(this).hide();
					$('#ClientInputsMaxLatencyValue').fadeIn('fast').removeAttr('hidden').parent('td').find('.returnDefaultValue').fadeIn('fast').removeAttr('hidden');
				}
			});
			// Return to the default value
			$('#resetClientInputsMaxLarency').click(function(event){
				event.preventDefault();
				$(this).fadeOut().attr('hidden', true);
				$('#ClientInputsMaxLatencyValue').hide().attr('hidden', true).val('');
				$('#ClientInputsMaxLatency').fadeIn('fast').removeAttr('hidden').find('option').removeAttr('selected');
				$('#ClientInputsMaxLatency option:first').select();
			});
			
			
			// Server options management
			$('.srvopts_importexport input[name="srvoptsImportExport"]').click(function(){
				var type = $(this).val();
				
				if(type == 'Import'){
					$('#srvoptsExportName').fadeOut().attr('hidden', true);
				}
				else{
					$('#srvoptsImportName').fadeOut().attr('hidden', true);
				}
				
				$('#srvopts'+type+'Name').fadeIn('fast').removeAttr('hidden');
			});
		}
		/**
		* Game Infos
		*/
		else if( $('body').hasClass('section-gameinfos') || $('body').hasClass('section-maps-creatematchset') ){
			// GameMode
			getCurrentGameModeConfig();
			$('#NextGameMode').change(function(){
				getCurrentGameModeConfig();
			});
			
			// FinishTimeout
			$('#NextFinishTimeout').change(function(){
				if( $(this).val() == 'more' ){
					$(this).hide();
					$('#NextFinishTimeoutValue').fadeIn('fast').removeAttr('hidden').val('15').parent('td').parent('tr').find('.returnDefaultValue').fadeIn('fast').parent('td').removeAttr('hidden');
				}
			});
			
			// ForceShowAllOpponents
			$('#NextForceShowAllOpponents').change(function(){
				if( $(this).val() == 'more' ){
					$(this).hide();
					$('#NextForceShowAllOpponentsValue').fadeIn('fast').removeAttr('hidden').val('2').parent('td').parent('tr').find('.returnDefaultValue').fadeIn('fast').parent('td').removeAttr('hidden');
				}
			});
			
			// Return to the default value
			$('.returnDefaultValue').click(function(event){
				event.preventDefault();
				$(this).fadeOut().attr('hidden', true);
				$(this).parent('td').parent('tr').find('td.next input').hide().attr('hidden', true).val('');
				$(this).parent('td').parent('tr').find('td.next select').fadeIn('fast').find('option').removeAttr('selected').find('option:first').select().removeAttr('hidden');
			});
			
			// Team info
			$('#colorPickerTeam1').css('backgroundColor', $('#teamInfo1ColorHex').val());
			$('#colorPickerTeam1').ColorPicker({
				color: $('#teamInfo1ColorHex').val(),
				onShow: function (colpkr) {
					$(colpkr).slideDown('fast');
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).slideUp('fast');
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$('#colorPickerTeam1').css('backgroundColor', '#'+hex);
					$('#teamInfo1ColorHex').val('#'+hex);
					var val = $('#colorPickerTeam1 .colorpicker_hue div').css('top');
					$('#teamInfo1Color').val(round(1 - val.substring(0, val.length-2)/150, 3));
				}
			});
			$('#colorPickerTeam2').css('backgroundColor', $('#teamInfo2ColorHex').val());
			$('#colorPickerTeam2').ColorPicker({
				color: $('#teamInfo2ColorHex').val(),
				onShow: function (colpkr) {
					$(colpkr).slideDown('fast');
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).slideUp('fast');
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$('#colorPickerTeam2').css('backgroundColor', '#'+hex);
					$('#teamInfo2ColorHex').val('#'+hex);
					var val = $('#colorPickerTeam2 .colorpicker_hue div').css('top');
					$('#teamInfo2Color').val(round(1 - val.substring(0, val.length-2)/150, 3));
				}
			});
			
			// Script Settings -- inline (TM2020): load on page start
			getScriptSettings();

			// Script Settings -- inline save
			$(document).on('click', '#inlineScriptSettingsSaveBtn', function(){
				setScriptSettings();
			});

			// Script Manager (TM2020)
			$('#openScriptManager').click(function(event){
				event.preventDefault();
				openScriptManager();
			});

			// Script Manager — upload button
			$(document).on('click', '#scriptUploadBtn', function(){
				scriptUpload_send();
			});

			// Script Manager -- click on the upload zone
			$(document).on('click', '#scriptUploadZone', function(){
				$('#scriptUploadInput').trigger('click');
			});

			// Script Manager -- drag & drop on the upload zone
			$(document).on('dragover', '#scriptUploadZone', function(e){
				e.preventDefault();
				$(this).addClass('dragover');
			});
			$(document).on('dragleave drop', '#scriptUploadZone', function(e){
				e.preventDefault();
				$(this).removeClass('dragover');
				if( e.type === 'drop' ){
					var files = e.originalEvent.dataTransfer.files;
					if( files && files.length > 0 ){
						$('#scriptUploadInput')[0].files = files;
						$('#scriptUploadZone .script-upload-hint').text(files[0].name);
					}
				}
			});

			// Script Manager -- file change in input
			$(document).on('change', '#scriptUploadInput', function(){
				var f = this.files && this.files[0];
				if( f ){ $('#scriptUploadZone .script-upload-hint').text(f.name); }
			});

			// Script Manager -- save in editor
			$(document).on('click', '#scriptEditorSaveBtn', function(){
				scriptEditor_save();
			});

			// Script Manager -- Ctrl+S in textarea
			$(document).on('keydown', '#scriptEditorContent', function(e){
				if( (e.ctrlKey || e.metaKey) && e.key === 's' ){
					e.preventDefault();
					scriptEditor_save();
				}
			});

			// Display sec -> min
			$('#NextTimeAttackLimit, #NextLapsTimeLimit, #hotSeatTimeLimit').click(function(){
				$(this).parent('td').parent('tr').find('td.preview').html('['+secToMin( $(this).val() )+' min]');
			});
			$('#NextTimeAttackLimit, #NextLapsTimeLimit, #hotSeatTimeLimit').change(function(){
				$(this).parent('td').parent('tr').find('td.preview').html('['+secToMin( $(this).val() )+' min]');
			});
			$('#NextTimeAttackLimit, #NextLapsTimeLimit, #hotSeatTimeLimit').keyup(function(){
				$(this).parent('td').parent('tr').find('td.preview').html('['+secToMin( $(this).val() )+' min]');
			});
			$('#NextTimeAttackLimit, #NextLapsTimeLimit, #hotSeatTimeLimit').blur(function(){
				$(this).parent('td').parent('tr').find('td.preview').html('');
			});
			
			
			/**
			* Create MatchSettings
			*/
			if( $('body').hasClass('section-maps-creatematchset') ){
				// MatchSetting name
				$('#matchSettingName').keyup(function(event){
					var key = event.keyCode;
					if(key != 13 && key != 37 && key != 39){
						matchset_getFileExists( $(this).val() );
					}
				});
				
				// Import the whole folder
				$('#mapImport').click(function(){
					$('.creatematchset .maps').addClass('loading');
					matchset_mapImport();
				});
				
				// Make a selection
				$('#mapImportSelection').click(function(){
					$('.creatematchset .maps').addClass('loading');
					matchset_mapImportSelection();
				});
				
				// Checkbox
				$('#checkAllMapImport').click(function(){
					var dialog = $('#mapImportSelectionDialog');
					dialog.checkAll( $(this).attr('checked') );
					if( dialog.find('tr.current').hasClass('selected') ){
						dialog.find('tr.current').removeClass('selected');
					}
				});
				
				// Click on lines
				$('#mapImportSelectionDialog').on('click', 'tr', function(){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					// Update the CheckAll
					$('#mapImportSelectionDialog').updateCheckAll( $('#checkAllMapImport') );
				});
				
				// View the matchsettings selection
				$('#mapSelection').click(function(){
					$('.creatematchset .maps').addClass('loading');
					matchset_mapSelection();
				});
				
				// Remove a map from the selection
				$('#mapSelectionDialog').on('click', 'tr a', function(event){
					event.preventDefault();
					matchset_mapSelection( parseInt($(this).parent('td').parent('tr')[0].sectionRowIndex) );
				});
				
				// MatchSettings name
				$('#matchSettingName').click(function(){
					$(this).select();
				});
				$('#matchSettingName').blur(function(){
					if( $(this).val() == '' ){
						$(this).val('match_settings');
					}
				});
				
				// Submit
				$('#savematchsetting').click(function(){
					if( $('#nbMapSelected').text() == '0' ){
						scrollTop();
						error( $(this).data('nomap'), true);
						return false;
					}
					else{
						return true;
					}
				});
			}
		}
		/**
		* Results (live standings)
		*/
		else if( $('body').hasClass('section-results') ){
			var resultsInterval = setInterval(function(){
				getResults();
			}, 5000);
			
			// Toggle auto-refresh on status click
			$('#resultsAutoRefresh').click(function(){
				var $el = $(this);
				if(resultsInterval){
					clearInterval(resultsInterval);
					resultsInterval = null;
					$el.text( $el.data('off') );
				} else {
					resultsInterval = setInterval(function(){ getResults(); }, 5000);
					$el.text( $el.data('on') );
				}
			});
		}
		/**
		* Chat
		*/
		else if( $('body').hasClass('section-chat') ){
			var hideServerLines = 0;
			
			// Click on "Hide server lines"
			$('.title-detail a').click(function(event){
				event.preventDefault();
				// Value
				hideServerLines = $(this).data('val');
				if(hideServerLines == 0){
					hideServerLines = 1;
				}
				else{
					hideServerLines = 0;
				}
				getChatServerLines(hideServerLines);
				$(this).data('val', hideServerLines);
				
				var text = $(this).text();
				$(this).text( $(this).data('txt') ).data('txt', text);
			});
			
			// Display every 3s
			setInterval(function(){
				getChatServerLines(hideServerLines);
			}, 3000);
			
			// Add a message
			$('#chatNickname, #chatMessage').click(function(){
				var text = $(this).val();
				var defaultText = $(this).data('default-value');
				
				if(text == defaultText){
					$(this).val('');
				}
			});
			$('#chatNickname, #chatMessage').blur(function(){
				var text = $(this).val();
				var defaultText = $(this).data('default-value');
				
				if(text == ''){
					$(this).val(defaultText);
				}
			});
			$('#chatSend').click(function(){
				var msg = $('#chatMessage').val();
				if( msg != $('#chatMessage').data('default-value') && msg != '' ){
					addChatServerLine();
				}
			});
			$('#chatMessage').keypress(function(event){
				var msg = $(this).val();
				if( msg != $(this).data('default-value') && msg != '' ){
					if( event.keyCode == 13 ){
						addChatServerLine();
					}
				}
			});
		}
		/**
		* Maps-list
		*/
		else if( $('body').hasClass('section-maps-list') ){
			var mapList = $('#maplist');
			// Update the list
			setInterval(function(){
				getMapList(getMode());
			}, 10000);
			
			// Checkbox
			$('#checkAll').click(function(){
				mapList.checkAll( $(this).prop('checked') );
				if( mapList.find('tr.current').hasClass('selected') ){
					mapList.find('tr.current').removeClass('selected');
				}
				
				// Update the selected-lines count
				$('.maps .list').updateNbSelectedLines();
			});
			
			// Click on lines
			mapList.on('click', 'tr', function(){
				if( !$(this).hasClass('current') && !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('.maps .list').updateNbSelectedLines();
					$('.maps .list').updateCheckAll( $('#checkAll') );
				}
			});
			
			// Detail mode
			$('#detailMode').click(function(event){
				event.preventDefault();
				mapList.setDetailMode();
			});
		}
		/**
		* Maps-upload
		*/
		else if( $('body').hasClass('section-maps-upload') ){
			//Upload
			var uploader = initializeUploader();
			
			// Transfer mode
			$('.transferMode li').click(function(){
				$('.transferMode li').removeClass('selected');
				$('.options-checkbox li')
					.removeClass('disabled')
					.find('input').prop('disabled', false);
				$('#GotoListMaps').prop('checked', true);
				
				$(this)
					.addClass('selected')
					.find('input').prop('checked', true);
				
				if( $(this).find('input').val() == 'local' ){
					$('#SaveCurrentMatchSettings')
						.prop('disabled', true)
						.parent().addClass('disabled');
					$('#GotoListMaps').prop('checked', false);
				}
				uploader.setParams( getUploaderUserParams() );
			});
			
			// Options
			$('.options-checkbox input, .options-checkbox label').click(function(){
				uploader.setParams( getUploaderUserParams() );
			});
		}
		/**
		* Maps-local
		*/
		else if( $('body').hasClass('section-maps-local') ){
			// Checkbox
			$('#checkAll').click(function(){
				$('#maplist').checkAll( $(this).prop('checked') );
				
				// Update the selected-lines count
				$('.maps .local').updateNbSelectedLines();
			});
			
			// Click on lines
			$('#maplist').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('.maps .local').updateNbSelectedLines();
					$('.maps .local').updateCheckAll( $('#checkAll') );
					
					// Check if any forms are still open and close them when 0 lines are selected
					if( $('#maplist tr.selected').length == 0 ){
						if( $('.selected-files-label').hasClass('optHover') ){
							slideUpRenameForm();
							slideUpMoveForm();
						}
					}
				}
			});
			
			// Rename
			$('#renameMap').click(function(){
				if( !$('#maplist tr.selected').hasClass('onserver') ){
					if( $(this).hasClass('active') ){
						slideUpRenameForm();
					}
					else{
						if( $('#moveMap').hasClass('active') ){
							slideUpMoveForm();
						}
						slideDownRenameForm();
					}
				}
				else{
					var mapTextError = $('.local .options').data('mapisused').split(',');
					var mapName = $('#maplist tr.selected td span').html();
					error(mapTextError[0]+' '+mapName+' '+mapTextError[1]);
					scrollTop();
				}
			});
			$('#form-rename-map').on('click', '#renameMapCancel', function(){
				slideUpRenameForm();
			});
			
			// Move
			$('#moveMap').click(function(){
				if( !$('#maplist tr.selected').hasClass('onserver') ){
					if( $(this).hasClass('active') ){
						slideUpMoveForm();
					}
					else{
						if( $('#renameMap').hasClass('active') ){
							slideUpRenameForm();
						}
						slideDownMoveForm();
					}
				}
				else{
					var mapTextError = $('.local .options').data('mapisused').split(',');
					var mapName = $('#maplist tr.selected td span').html();
					error(mapTextError[0]+' '+mapName+' '+mapTextError[1]);
					scrollTop();
				}
			});
			$('#form-move-map').on('click', '#moveMapCancel', function(){
				slideUpMoveForm();
			});
			
			// Delete
			$('#deleteMap').click(function(){
				if( !$('#maplist tr.selected').hasClass('onserver') ){
					return confirm( $(this).data('confirm') );
				}
				else{
					var mapTextError = $('.local .options').data('mapisused').split(',');
					var mapName = $('#maplist tr.selected td span').html();
					error(mapTextError[0]+' '+mapName+' '+mapTextError[1]);
					scrollTop();
					return false;
				}
			});
		}
		/**
		* Maps-matchset
		*/
		else if( $('body').hasClass('section-maps-matchset') ){
			// Checkbox
			$('input#checkAll').click(function(){
				$('#matchsetlist').checkAll( $(this).prop('checked') );
				
				// Update the selected-lines count
				$('.maps .matchset').updateNbSelectedLines();
			});
			
			// Click on lines
			$('#matchsetlist').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('.maps .matchset').updateNbSelectedLines();
					$('.maps .matchset').updateCheckAll( $('input#checkAll') );
					
					// Edit if a single line is selected
					if( $('.selected-files-count').text().replace('(', '').replace(')', '') > 1 ){
						$('#editMatchset').attr('hidden', true);
					}
					else{
						if( $('#editMatchset').attr('hidden') ){
							$('#editMatchset').removeAttr('hidden');
						}
					}
				}
			});
		}
		/**
		* Maps-order
		*/
		else if( $('body').hasClass('section-maps-order') ){
			// Automatic sort
			$('.autoSortMode li').click(function(){
				$('#sortableMapList').addClass('loading');
				$(this).children('input').prop('checked', true);
				$('.autoSortMode li').removeClass('selected').find('span.ui-icon').removeClass('active');
				$(this).addClass('ui-state-default selected').find('.icon .ui-icon-arrowthick-1-n').addClass('active');
				
				// Sort
				setMapsOrderSort($(this).children('input').val(), 'asc');
			});
			$('.autoSortMode li span.ui-icon').click(function(){
				$('#sortableMapList').addClass('loading');
				$('.autoSortMode li').removeClass('selected').find('span.ui-icon').removeClass('active');
				$(this).parents('li').addClass('selected').children('input').prop('checked', true);
				$(this).addClass('active');
				
				if( $(this).hasClass('ui-icon-arrowthick-1-n') ){
					var order = 'asc';
				}
				else{
					var order = 'desc';
				}
				
				setMapsOrderSort($(this).parent('.icon').parent('li').children('input').val(), order);
				
				return false;
			});
			
			// Manual sort
			$('#sortableMapList').sortable({
				placeholder: 'ui-state-highlight',
				revert: true,
				zIndex: 9999
			});
			$('#reset').click(function(){
				location.href = $('.section-maps-order .cadre.order form').attr('action');
			});
			$('#save').click(function(){
				var listStr = '';
				var list = $('#sortableMapList li .order-map-name');
				if( list.length > 0 ){
					$.each(list, function(i, n){
						listStr += n.title+',';
					});
					$('#list').val(listStr.substring(0, listStr.length-1));
				}
			});
		}
		/**
		* Guest-Ban
		*/
		else if( $('body').hasClass('section-guestban') ){
			$('a.cleanList').click(function(){
				var out = false;
				var lines = $(this).parents('.title-detail').parent('div').find('tbody tr:not(.no-line)');
				
				if(lines.length > 1){
					out = true;
				}
				else{
					error( $('a.cleanList').data('empty'), true);
				}
				
				return out;
			});
			
			// Checkbox
			$('#checkAllBanlist').click(function(){
				$('#banlist').checkAll( $(this).prop('checked') );
				$('.cadre.left').updateNbSelectedLines();
			});
			$('#checkAllBlacklist').click(function(){
				$('#blacklist').checkAll( $(this).prop('checked') );
				$('.cadre.left').updateNbSelectedLines();
			});
			$('#checkAllGuestlist').click(function(){
				$('#guestlist').checkAll( $(this).prop('checked') );
				$('.cadre.left').updateNbSelectedLines();
			});
			$('#checkAllIgnorelist').click(function(){
				$('#ignorelist').checkAll( $(this).prop('checked') );
				$('.cadre.left').updateNbSelectedLines();
			});
			$('#playlists #checkAllPlaylists').click(function(){
				$('#playlists').checkAll( $(this).prop('checked') );
				$('#playlists').updateNbSelectedLines();
			});
			
			// Click on lines
			$('#banlist, #blacklist, #guestlist, #ignorelist').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('.cadre.left').updateNbSelectedLines();
					$('.cadre.left').updateCheckAll( $(this).parent('tbody').parent('table').parent('div').find('input[type=checkbox]') );
				}
			});
			$('#playlists').on('click', 'tr', function(){
				if( !$(this).hasClass('no-line') && !$(this).hasClass('table-separation') ){
					// If the line is already selected, remove it
					if( $(this).hasClass('selected') ){
						$(this).removeClass('selected').find('td.checkbox input').prop('checked', false);
					}
					// Otherwise, add it
					else{
						$(this).addClass('selected').find('td.checkbox input').prop('checked', true);
					}
					
					// Update the selected-lines count
					$('#playlists').updateNbSelectedLines();
					$('#playlists').updateCheckAll( $('#playlists #checkAllPlaylists') );
				}
			});
			
			// Add
			$('#addPlayerList').change(function(){
				if( $(this).val() == 'more' ){
					$(this).hide().attr('hidden', true);
					$('#addPlayerLogin').fadeIn('fast').removeAttr('hidden');
				}
			});
			$('#addPlayerLogin').click(function(){
				if( $(this).val() == $(this).data('default-value') ){
					$(this).val('');
				}
			});
			$('#addPlayerLogin').blur(function(){
				if( $(this).val() == '' ){
					$(this).val( $(this).data('default-value') );
				}
			});
			
			// Create a playlist
			$('#clickNewPlaylist').click(function(event){
				event.preventDefault();
				var selector = $('#form-new-playlist');
				if( selector.attr('hidden') ){
					selector.animate({
						height: '25px',
						marginTop: '6px',
						marginBottom: '6px'
					}, 'fast').removeAttr('hidden');
					$(this).text( $(this).data('cancel') );
				}
				else{
					selector.animate({
						height: '0',
						marginTop: '0',
						marginBottom: '0'
					}, 'fast', function(){
						$(this).attr('hidden', true);
						$('#clickNewPlaylist').text( $('#clickNewPlaylist').data('newplaylist') );
					});
				}
			});
			$('#createPlaylistName').click(function(){
				if( $(this).val() == $(this).data('playlistname') ){
					$(this).val('');
				}
			});
			$('#createPlaylistName').blur(function(){
				var val = $(this).val();
				if( val == '' ){
					$(this).val( $(this).data('playlistname') );
				}
				else{
					var extTXT = val.indexOf('.playlist.txt');
					if(extTXT === -1){
						$(this).val(val+'.playlist.txt');
					}
				}
			});
		}
	}
});