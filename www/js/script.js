var Dase = {};

$(document).ready(function() {
	Dase.initDelete('login');
	Dase.initDelete('set');
	Dase.initDelete('item_metadata');
	Dase.initToggle('target');
	Dase.initToggle('email');
	//Dase.initSortable('set');
	Dase.initSortableTable('set');
	Dase.initUserPrivs();
	Dase.initFormDelete();
	Dase.initLabSelector();
	Dase.initToggleCheck();
    Dase.initColorbox();
    Dase.initBulkAdd();
    Dase.initCsvUpload();
    Dase.initEditMetadataValue();
});

Dase.initCsvUpload = function() {
    $('a#csv').click(function() {
        var href = $(this).attr('href');
        $.colorbox({
            href:href,
            width: '480',
            opacity: 0.5,
            onComplete: function() {
                $('#closeColorbox').click(function() {$.colorbox.close();});
            }
        });
        return false;
    });
};

Dase.initBulkAdd = function() {
    $('#bulk_add').find('select[name="attribute_id"]').change(function() {
        var att_id = $('select[name="attribute_id"] option:selected').val();
        var url = 'content/attribute/'+att_id+'/input_form';
        $.get(url,function(data) { $('#att_input_form').html(data); });
    });
    $('#bulk_add').submit(function() {
        var items_input = $(this).find('input[name="items"]');
        var matches = [];
        $("#items input:checked").each(function() {
            matches.push(this.value);
        });
        items_input.attr('value',matches.join('|'));
    });
};

Dase.initEditMetadataValue = function() {
    $('#item_metadata').find('a.edit').click(function() {
        $(this).parents('tr').find('span.current_value').hide();
        var target = $(this).parents('tr').find('span.value_input_form');
        var url = $(this).attr('href');
        $.get(url,function(data) { target.html(data); });
        return false;
    });
};

Dase.initColorbox = function() {
    $("#loclink").colorbox(
            {iframe:true, innerWidth:800, innerHeight:500,onClosed: function() {location.reload()}}
            );
};

Dase.initToggleCheck = function() {
	var checked = false;
	$('#toggle_check').click(function() {
		if (checked) {
			$('table#items').find('input[type="checkbox"]').attr('checked',false);
			checked = false;
		} else {
			$('table#items').find('input[type="checkbox"]').attr('checked',true);
			checked = true;
		}
		return false;
	});
};

Dase.initLabSelector = function() {
	$('ul#lab_selector li').hover(function() {
		$('#banners img').hide();
		var id = $(this).attr('class');
		$('#'+id).show();
	});
	$('#banners').hover(function() {
		$('#banners img').hide();
		$('#front').show();
	});
};



Dase.initToggle = function(id) {
	$('#'+id).find('a[class="toggle"]').click(function() {
		var id = $(this).attr('id');
		var tar = id.replace('toggle','target');
		$('#'+tar).toggle();
		return false;
	});	
};

Dase.initFormDelete = function() {
	$("form[method='delete']").submit(function() {
		if (confirm('are you sure?')) {
			var del_o = {
				'url': $(this).attr('action'),
				'type':'DELETE',
				'success': function() {
					location.reload();
				},
				'error': function() {
					alert('sorry, cannot delete');
				}
			};
			$.ajax(del_o);
		}
		return false;
	});
};

Dase.initDelete = function(id) {
	$('#'+id).find("a.delete").click(function() {
		if (confirm('are you sure?')) {
			var del_o = {
				'url': $(this).attr('href'),
				'type':'DELETE',
				'success': function(resp) {
					if (resp.location) {
						location.href = resp.location;
					} else {
						location.reload();
					}
				},
				'error': function() {
					alert('sorry, cannot delete');
				}
			};
			$.ajax(del_o);
		}
		return false;
	});
};

Dase.initSortable = function(id) {
	$('#'+id).sortable({ 
		cursor: 'crosshair',
		opacity: 0.6,
		revert: true, 
		start: function(event,ui) {
			ui.item.addClass('highlight');
		},	
		stop: function(event,ui) {
			$('#proceed-button').addClass('hide');
			$('#unsaved-changes').removeClass('hide');
			$('#'+id).find("li").each(function(index){
				$(this).find('span.key').text(index+1);
			});	
			ui.item.removeClass('highlight');
		}	
	});
};

Dase.initSortableTable = function(id) {
	$('#'+id).sortable({ 
		cursor: 'crosshair',
		opacity: 0.6,
		revert: true, 
		start: function(event,ui) {
			ui.item.addClass('highlight');
		},	
		stop: function(event,ui) {
			var order_data = [];
			$('#'+id).find("tr").each(function(index){
				$(this).find('span.key').text(index);
				order_data[order_data.length] = $(this).attr('id');
			});	
			var url = $('link[rel="items_order"]').attr('href');
			var _o = {
				'url': url,
				'type':'POST',
				processData: false,
				data: order_data.join('|'),
				'success': function(resp) {
					//alert(resp);
					//location.reload();
				},
				'error': function() {
					alert('sorry, there was a problem');
				}
			};
			$.ajax(_o);
			ui.item.removeClass('highlight');
		}	
	});
};

Dase.initUserPrivs = function() {
	$('#user_privs').find('a').click( function() {
		var method = $(this).attr('data-method');
		var url = $(this).attr('href');
		var _o = {
			'url': url,
			'type':method,
			'success': function(resp) {
				alert(resp);
				location.reload();
			},
			'error': function() {
				alert('sorry, there was a problem');
			}
		};
		$.ajax(_o);
		return false;
	});
};

