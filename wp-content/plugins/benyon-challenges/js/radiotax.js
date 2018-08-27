jQuery(document).ready(function($) {
      var taxonomy = 'difficulty'; //Or set manually.

		// var taxonomy = radio_tax.slug; //Or set manually.

		$('#' + taxonomy + 'checklist li :radio, #' + taxonomy + 'checklist-pop :radio').on( 'click', function(){
				var t = $(this);
				var c = t.is(':checked');
				var id = t.val();
				console.log(c);
				console.log(id);
				$('#' + taxonomy + 'checklist li :radio, #' + taxonomy + 'checklist-pop :radio').prop('checked',false);
				$('#in-' + taxonomy + '-' + id + ', #in-popular-' + taxonomy + '-' + id).prop( 'checked', c );
		});

		$('#' + taxonomy +'-add .radio-tax-add').on( 'click', function(){
			var term = $('#' + taxonomy+'-add #new'+taxonomy).val();
      var nonce =$('#' + taxonomy+'-add #_wpnonce_radio-add-tag').val();
      
			$.post(ajaxurl, {
				action: 'radio_tax_add_taxterm',
				term: term,
				'_wpnonce_radio-add-tag':nonce,
				taxonomy: taxonomy
			}, function(r){
				$('#' + taxonomy + 'checklist').append(r.html).find('li#'+taxonomy+'-'+r.term+' :radio').attr('checked', true);
			},'json');
	});
});
