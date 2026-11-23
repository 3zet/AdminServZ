/* ============================================================
   AdminServ — displayserv.js  (redesign)
   ============================================================ */
(function($){

  $.fn.displayServ = function(options){
    var selector = $(this);

    var settings = {
      config:         'config/servers.cfg.php',
      resources:      'resources/',
      timeout:        3,
      refresh:        30,
      color:          '',
      links: {
        join:         true,
        spectate:     true,
        addfavourite: false
      }
    };
    $.extend(true, settings, options);
    settings.refresh = parseInt(settings.refresh * 1000);
    if(settings.refresh < 10000){ settings.refresh = 10000; }

    selector.initialize(settings);
    setInterval(function(){ selector.initialize(settings); }, settings.refresh);
  };

})(jQuery);


(function($){

  $.fn.initialize = function(settings){
    var selector = $(this);

    $.getJSON(settings.resources + 'ajax/ds_initialize.php', {cfg: settings.config}, function(data){
      if(!data) return;

      var html = '<ul class="ds-servers-list loading">';

      if(data.servers){
        for(var i = 0; i < data.servers; i++){

          var footerHtml = '';
          if(settings.links.join || settings.links.spectate || settings.links.addfavourite){
            footerHtml += '<div class="ds-footer">';
            if(settings.links.join){
              footerHtml += '<a class="ds-action ds-server-join" href="#" title="'+data.label.accessserverplayer+'">'
                + '<span class="ds-action-icon">&#9654;</span>'
                + '<span class="ds-action-label">'+data.label.accessserverplayer+'</span>'
                + '</a>';
            }
            if(settings.links.spectate){
              footerHtml += '<a class="ds-action ds-server-spectate" href="#" title="'+data.label.accessserverspectate+'">'
                + '<span class="ds-action-icon">&#9673;</span>'
                + '<span class="ds-action-label">'+data.label.accessserverspectate+'</span>'
                + '</a>';
            }
            if(settings.links.addfavourite){
              footerHtml += '<a class="ds-action ds-server-favourite" href="#" title="'+data.label.addfavourite+'">'
                + '<span class="ds-action-icon">&#9733;</span>'
                + '<span class="ds-action-label">'+data.label.addfavourite+'</span>'
                + '</a>';
            }
            footerHtml += '</div>';
          }

          html += '<li id="ds-server-' + i + '" class="ds-server">'
            + '<div class="ds-card-head">'
              + '<span class="ds-dot offline"></span>'
              + '<span class="ds-title">'+data.label.server+' '+(i+1)+'</span>'
              + '<span class="ds-version-badge">...</span>'
            + '</div>'
            + '<div class="ds-body">'
              + '<div class="ds-info">'
                + '<div class="ds-row"><span class="ds-label">'+data.label.login+'</span><span class="ds-val mono ds-server-login">&mdash;</span></div>'
                + '<div class="ds-row"><span class="ds-label">'+data.label.status+'</span><span class="ds-val ds-server-status">&mdash;</span></div>'
                + '<div class="ds-row"><span class="ds-label">'+data.label.gamemode+'</span><span class="ds-val ds-server-gamemode">&mdash;</span></div>'
                + '<div class="ds-row"><span class="ds-label">'+data.label.currentmap+'</span><span class="ds-val ds-server-currentmap">&mdash;</span></div>'
              + '</div>'
              + '<hr class="ds-divider">'
              + '<div class="ds-players">'
                + '<div class="ds-players-header">'
                  + '<span class="ds-players-label">'+data.label.players+'</span>'
                  + '<span class="ds-players-count ds-server-players-count">&mdash;</span>'
                + '</div>'
                + '<div class="ds-player-list"></div>'
              + '</div>'
            + '</div>'
            + footerHtml
          + '</li>';
        }
      }
      html += '</ul>';

      // Replace content only if something changed, or on first load
      if(selector.find('.ds-servers-list').length === 0){
        selector.html(html);
      } else {
        selector.find('.ds-servers-list').addClass('loading');
      }

      // Fetch data
      $.getJSON(settings.resources + 'ajax/ds_getservers.php', {cfg: settings.config, rsc: settings.resources}, function(data){
        if(!data) return;

        if(data.servers){
          for(var i = 0; i < data.servers.length; i++){
            var $card = $('#ds-server-' + i);
            var srv   = data.servers[i];

            if(srv.error){
              $card.addClass('ds-server-offline');
              $card.find('.ds-dot').removeClass('online').addClass('offline');
              $card.find('.ds-title').text(srv.error);
              $card.find('.ds-version-badge').text('offline').removeClass().addClass('ds-version-badge');
              $card.find('.ds-body').html(
                '<div class="ds-info ds-info-offline">'
                + '<span class="ds-offline-msg">' + (srv.error || 'Offline') + '</span>'
                + '</div>'
              );
              $card.find('.ds-footer').remove();
              continue;
            }

            $card.removeClass('ds-server-offline');

            // --- Version ---
            var vname  = (srv.version && srv.version.name)     ? srv.version.name     : '';
            var vtitle = (srv.version && srv.version.title)    ? srv.version.title    : '';
            var vproto = (srv.version && srv.version.protocol) ? srv.version.protocol : 'maniaplanet';

            var vLabel = vname;
            var vClass = vname.toLowerCase().replace(/[^a-z0-9]/g, '');
            if(vname === 'Trackmania')   { vLabel = 'TM 2020'; }
            else if(vname === 'ManiaPlanet'){ vLabel = 'ManiaPlanet'; }
            else if(vname === 'TmForever'){ vLabel = 'TmForever'; }

            $card.find('.ds-dot').removeClass('offline').addClass('online');
            $card.find('.ds-title').html(srv.name || '');
            $card.find('.ds-version-badge')
              .text(vLabel)
              .removeClass()
              .addClass('ds-version-badge ' + vClass);

            // --- Info ---
            $card.find('.ds-server-login').text(srv.serverlogin || '');
            $card.find('.ds-server-status').html('<span class="ds-status-dot"></span>' + (srv.status || ''));

            // GameMode -- color by mode
            var gm    = srv.gamemode || '';
            var gmLow = gm.toLowerCase();
            var gmClass = 'ds-mode-other';
            if(gmLow.indexOf('timeattack') >= 0)  gmClass = 'ds-mode-ta';
            else if(gmLow.indexOf('rounds') >= 0) gmClass = 'ds-mode-rounds';
            else if(gmLow.indexOf('team') >= 0)   gmClass = 'ds-mode-team';
            else if(gmLow.indexOf('laps') >= 0)   gmClass = 'ds-mode-laps';
            else if(gmLow.indexOf('stunts') >= 0) gmClass = 'ds-mode-stunts';
            else if(gmLow.indexOf('cup') >= 0)    gmClass = 'ds-mode-cup';
            else if(gmLow.indexOf('script') >= 0) gmClass = 'ds-mode-script';

            $card.find('.ds-server-gamemode').text(gm).attr('class', 'ds-val ds-server-gamemode ' + gmClass);

            // Map + environment
            var envHtml = '';
            if(srv.map && srv.map.env){
              if(srv.map.env.filename){
                envHtml = '<img class="ds-env-img" src="' + srv.map.env.filename
                  + '" alt="' + srv.map.env.name + '" title="' + srv.map.env.name + '">';
              } else if(srv.map.env.name){
                envHtml = '<span class="ds-map-env">' + srv.map.env.name + '</span>';
              }
            }
            $card.find('.ds-server-currentmap').html((srv.map && srv.map.name ? srv.map.name : '') + envHtml);

            // Join / spectate / favourite links (pre-Forever servers report no login: hide them)
            var titleSuffix = vtitle ? '@' + vtitle : '';
            var $links = $card.find('.ds-server-join, .ds-server-spectate, .ds-server-favourite');
            if(!srv.serverlogin){
              $links.hide();
            }
            else{
              $links.show();
              if(settings.links.join){
                $card.find('.ds-server-join').attr('href', vproto + '://#join=' + srv.serverlogin + titleSuffix);
              }
              if(settings.links.spectate){
                $card.find('.ds-server-spectate').attr('href', vproto + '://#spectate=' + srv.serverlogin + titleSuffix);
              }
              if(settings.links.addfavourite){
                $card.find('.ds-server-favourite').attr('href', vproto + '://#addfavourite=' + srv.serverlogin + titleSuffix);
              }
            }

            // --- Players ---
            var pl = data.players[i];
            if(pl){
              $card.find('.ds-server-players-count').text(pl.count.current + ' / ' + pl.count.max);

              var listHtml = '';
              if(pl.count.current > 0 && typeof pl.list === 'object'){
                $.each(pl.list, function(idx, p){
                  var teamDot = '';
                  if(gm === 'Team'){
                    var dotClass = 'ds-team-dot ds-team-' + (p.teamId !== undefined ? p.teamId : -1);
                    teamDot = '<span class="' + dotClass + '" title="' + (p.teamName || '') + '"></span>';
                  }
                  var specBadge = (p.status && p.status.toLowerCase().indexOf('spec') >= 0)
                    ? '<span class="ds-player-spec">spec</span>' : '';
                  listHtml += '<div class="ds-player-row">'
                    + teamDot
                    + '<span class="ds-player-name">' + p.name + '</span>'
                    + specBadge
                    + '</div>';
                });
              } else if(typeof pl.list === 'string'){
                listHtml = '<div class="ds-no-player">' + pl.list + '</div>';
              }
              $card.find('.ds-player-list').html(listHtml);
            }
          }
        }

        selector.find('.ds-servers-list').removeClass('loading');
      });
    });
  };

})(jQuery);
