require(['elgg'], function(elgg) {
	
	// unregister handlers in case they were registered to prevent duplicate binding
	var selector;
	var $items = $(selector);
	if (typeof $.fn.die != 'undefined') {
		$items.die('click');
	}
	$items.off('click');
	$(document).off('click', selector);
	
	elgg.ui.registerTogglableMenuItems('feature', 'unfeature');
});
