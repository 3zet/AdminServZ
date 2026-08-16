/**
 * AdminServ — settings-popover.js
 * Handles the gear button that opens a popover for theme and language selection.
 * Replaces the old hover behavior on #theme and #lang.
 */
$(document).ready(function(){

	var $btn    = $('#settings-btn');
	var $popover = $('#settings-popover');

	if(!$btn.length || !$popover.length) return;

	// --- Open / close on click ---
	$btn.on('click', function(e){
		e.stopPropagation();
		var isOpen = $popover.hasClass('open');
		if(isOpen){
			closePopover();
		} else {
			openPopover();
		}
	});

	// Close when clicking outside the popover
	$(document).on('click', function(e){
		if($popover.hasClass('open') && !$popover[0].contains(e.target)){
			closePopover();
		}
	});

	// Close on Escape
	$(document).on('keydown', function(e){
		if(e.key === 'Escape' && $popover.hasClass('open')){
			closePopover();
		}
	});

	function openPopover(){
		$popover.addClass('open');
		$btn.addClass('active');
		$btn.attr('aria-expanded', 'true');
	}

	function closePopover(){
		$popover.removeClass('open');
		$btn.removeClass('active');
		$btn.attr('aria-expanded', 'false');
	}

});


/**
 * Auto-resize textarea -- fits height to content
 * Used for the Comment field in Server Options on mobile
 */
$(document).ready(function(){
	function autoResize(el) {
		el.style.height = 'auto';
		el.style.height = (el.scrollHeight) + 'px';
	}
	$('textarea').each(function(){
		autoResize(this);
	}).on('input', function(){
		autoResize(this);
	});
});
