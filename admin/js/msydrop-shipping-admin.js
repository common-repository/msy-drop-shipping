(function( $ ) {
	'use strict';

	$(function() {

		//products list category drop-down
		$('#category').on('change', function (){
			if( $(this).val() ){
				$('#import_cat').removeAttr('disabled');
			} else {
				$('#import_cat').attr('disabled', true);
			}
		});

		$('.margin').on('change', function (){
			var base_price = $(this).closest('table').find('.base_price').val();
			base_price = base_price ? parseFloat(base_price) : 0;
			var margin = $(this).closest('table').find('.margin').val();
			margin = margin ? parseFloat(margin) : 0;
			var tax = $(this).closest('table').find('.tax').val();
			tax = tax ? parseFloat(tax) : 0;
			var sale_price = base_price + margin;
			if( tax > 0 ){
				sale_price += tax / 100 * sale_price;
			}
			$(this).closest('table').find('.sale_price').val(sale_price.toFixed(2));
		});

		$('.tax').on('change', function (){
			var base_price = $(this).closest('table').find('.base_price').val();
			base_price = base_price ? parseFloat(base_price) : 0;
			var margin = $(this).closest('table').find('.margin').val();
			margin = margin ? parseFloat(margin) : 0;
			var tax = $(this).closest('table').find('.tax').val();
			tax = tax ? parseFloat(tax) : 0;
			var sale_price = base_price + margin;
			if( tax > 0 ){
				sale_price += tax / 100 * sale_price;
			}
			$(this).closest('table').find('.sale_price').val(sale_price.toFixed(2));
		});

		$('.category_id').on('change', function (){
			var base_price = $(this).closest('table').find('.base_price').val();
			base_price = base_price ? parseFloat(base_price) : 0;
			var margin = $(this).find(":selected").data('margin');
			var shop_margin = parseInt($(this).closest('table').find('.shop_margin').val());
			margin = parseInt(margin) > 0 ? parseFloat(margin) : shop_margin;
			var margin_value = margin / 100 * base_price;
			$(this).closest('table').find('.margin').val(margin_value.toFixed(2)).trigger('change');
		});

		$('.msy_page_msy-base-products .dashicons-trash').on('click', function (event){
			if( confirm('Are you sure to remove from shop?') ){
				var product_id = parseInt($(this).data('product_id'));
				if( product_id > 0 ){
					$(this).hide();
					$('.loader').show();
					var postData = {
						action: 'remove_published_product',
						product_id: product_id,
						nonce: msy_settings.nonce
					};
					$.ajax({
						type: 'POST',
						url: msy_settings.ajaxurl,
						dataType: 'json',
						data: postData,
						success: function (response) {
							alert(response.msg);
							$('.loader').hide();
							window.location.reload();
						},
						error: function () {}
					});
				}
			}
		});

		$('.msy_page_msy-base-products .dashicons-database-add, #import_cat').on('click', function (event){
			var category_id, product_id;
			if( $(this).attr('id') === 'import_cat' ){
				category_id = parseInt($('#category').val());
				product_id = 0;
			} else {
				product_id = parseInt($(this).data('product_id'));
				category_id = 0;
			}
			if( product_id > 0 || category_id > 0 ){
				var postData = {
					action: 'msy_add_to_imports',
					category_id: category_id,
					product_id: product_id,
					nonce: msy_settings.nonce
				};
				$.ajax({
					type: 'POST',
					url: msy_settings.ajaxurl,
					dataType: 'json',
					data: postData,
					success: function (response) {
						alert(response.msg);
					},
					error: function () {}
				});
			}
		});

		$('#clear-all-imports').on('click', function (event){
			if( confirm('Are you sure to remove all imports?') ){
				var postData = {
					action: 'msy_clear_all_imports',
					nonce: msy_settings.nonce
				};
				$.ajax({
					type: 'POST',
					url: msy_settings.ajaxurl,
					dataType: 'json',
					data: postData,
					success: function (response) {
						alert(response.msg);
						window.location.reload();
					},
					error: function () {}
				});
			}
		});

		$('.delete-order').on('click', function (event){
			if( confirm('Are you sure to remove order?') ){
				var postData = {
					action: 'msy_delete_pending_order',
					order_id: $(this).data('order_id'),
					nonce: msy_settings.nonce
				};
				$.ajax({
					type: 'POST',
					url: msy_settings.ajaxurl,
					dataType: 'json',
					data: postData,
					success: function (response) {
						alert(response.msg);
						window.location.reload();
					},
					error: function () {}
				});
			}
		});

		$('.save-product, #save-products').on('click', function (event){
			var $this = $(this),
				multiple = false,
				product_ids = [],
				title = {},
				margin = {},
				tax = {},
				sale_price = {},
				category_id = {},
				description = {},
				prod_status = {};
			if( $(this).attr('id') === 'save-products' ){
				multiple = true;
			}
			if( multiple ){
				$('.product_ids').each(function (index, element){
					product_ids[index] = $(this).val();
					description[$(this).val()] = $('#description_'+$(this).val()).val();
				});
				$('.prod_title').each(function (){
					title[$(this).data('product_id')] = $(this).val();
				});
				$('.margin').each(function (){
					margin[$(this).data('product_id')] = $(this).val();
				});
				$('.tax').each(function (){
					tax[$(this).data('product_id')] = $(this).val();
				});
				$('.sale_price').each(function (){
					sale_price[$(this).data('product_id')] = $(this).val();
				});
				$('.category_id').each(function (){
					category_id[$(this).data('product_id')] = $(this).val();
				});
				$('.prod_status').each(function (){
					if( $(this).prop('checked') === true ){
						prod_status[$(this).data('product_id')] = 'draft';
					} else {
						prod_status[$(this).data('product_id')] = 'publish';
					}
				});
			} else {
				var prod_id = $(this).closest('.product-details').find('.product_ids').val();
				product_ids[0] = prod_id;
				title[prod_id] = $(this).closest('.product-details').find('.prod_title').val();
				margin[prod_id] = $(this).closest('.product-details').find('.margin').val();
				tax[prod_id] = $(this).closest('.product-details').find('.tax').val();
				sale_price[prod_id] = $(this).closest('.product-details').find('.sale_price').val();
				category_id[prod_id] = $(this).closest('.product-details').find('.category_id').val();
				description[prod_id] = $('#description_'+prod_id).val();
				prod_status[prod_id] = $(this).closest('.product-details').find('.prod_status').prop('checked') === true ? 'draft' : 'publish';
			}
			if( product_ids ){
				var postData = {
					action: 'msy_import_products',
					product_ids,
					title,
					margin,
					sale_price,
					category_id,
					prod_status,
					description,
					nonce: msy_settings.nonce
				};
				$this.text('Importing ...');
				$.ajax({
					type: 'POST',
					url: msy_settings.ajaxurl,
					dataType: 'json',
					data: postData,
					success: function (response) {
						alert(response.msg);
						if( multiple ){
							window.location.reload();
						} else {
							$this.closest('.product-details').remove();
						}
					},
					error: function () {}
				});
			}
		});

		$('.product-details .nav-tab').on('click', function (){
			$(this).closest('.product-details').find('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$(this).closest('.product-details').find('.tab-content').removeClass('tab-content-active');
			$(this).closest('.product-details').find('#'+$(this).data('tabid')).addClass('tab-content-active');
		});

		//import products from api
		$('#import_msy_products').on('click', function (){
			var postData = {
				action: 'msy_sync_api_products',
				nonce: msy_settings.nonce
			};
			$(this).attr('disabled', true);
			$(this).text('Syncing Products...');
			$.ajax({
				type: 'POST',
				url: msy_settings.ajaxurl,
				dataType: 'json',
				data: postData,
				success: function (response) {
					alert(response.msg);
					window.location.reload();
				},
				error: function () {}
			});
		});

		//Process All Orders
        $('#processAllOrders').on('click', function (){
            if( confirm('Are you sure to process all orders?') ){
                var postData = {
                    action: 'msy_process_all_orders',
                    nonce: msy_settings.nonce
                };
				$('.order-sync-message').html('Processing...');
                $.ajax({
                    type: 'POST',
                    url: msy_settings.ajaxurl,
                    dataType: 'json',
                    data: postData,
                    success: function (response) {
						$('.order-sync-message').html(response.msg);
						$('#processAllOrders, #processSelOrders').hide();
                    },
                    error: function () {}
                });
            }
        });

		$('#processSelOrders').on('click', function (){
			var order_ids = [];
			$('.msy_page_msy-pending-orders input[type="checkbox"]:checked').each(function() {
				order_ids.push($(this).val());
			});
			if( order_ids.length > 0 ){
				if( confirm('Are you sure to process selected orders?') ){
					var postData = {
						action: 'msy_process_all_orders',
						order_ids: order_ids,
						nonce: msy_settings.nonce
					};
					$('.order-sync-message').html('Processing...');
					$.ajax({
						type: 'POST',
						url: msy_settings.ajaxurl,
						dataType: 'json',
						data: postData,
						success: function (response) {
							$('.order-sync-message').html(response.msg);
							$('#processAllOrders, #processSelOrders').hide();
							for(var i=0; i<order_ids.length; i++){
								$('.msy_page_msy-pending-orders input[type="checkbox"][value="' + order_ids[i] + '"]').closest('tr').remove();
							}
						},
						error: function () {}
					});
				}
			} else {
				alert('You need to select some orders before process.');
			}
		});

	});

})( jQuery );
