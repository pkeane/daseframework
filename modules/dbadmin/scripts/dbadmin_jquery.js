jQuery(function() {
		$('ul.tableSet li').find('ul').hide().end().click(function() {
			$('ul',this).toggle();
			return false;
			});
		});
