jQuery(document).ready(function ($) {

	var pqb_stock_qty           = pqb_params.stock_qty            ? parseInt(pqb_params.stock_qty, 10) : 0;
	var pqb_cart_qty            = pqb_params.cart_qty             ? parseInt(pqb_params.cart_qty, 10)  : 0;
	var pqb_variation_cart_qtys = pqb_params.variation_cart_qtys  || {};
	var pqb_current_variation   = null;
	var pqb_pending_qty         = 0;


	// --- SIMPLE PRODUCTS: re-evaluate button states based on remaining stock -----------

	function pqb_update_simple_buttons() {
		if (pqb_stock_qty === 0) {
			return; // stock not managed, nothing to evaluate
		}
		var available = pqb_stock_qty - pqb_cart_qty;
		$('.pqb-btn').not('.pqb-variable .pqb-btn').each(function () {
			var qty      = parseInt($(this).data('quantity'), 10);
			var disabled = qty > available;
			$(this).prop('disabled', disabled).toggleClass('disabled', disabled);
		});
	}


	// --- VARIABLE PRODUCTS: re-evaluate button states for the current variation --------

	function pqb_update_variable_buttons() {
		if (!pqb_current_variation || !pqb_current_variation.is_purchasable || !pqb_current_variation.is_in_stock) {
			$('.pqb-variable .pqb-btn').prop('disabled', true).addClass('disabled');
			return;
		}

		var vid     = pqb_current_variation.variation_id;
		var cartQty = pqb_variation_cart_qtys[vid] || 0;
		var maxQty  = pqb_current_variation.max_qty;

		$('.pqb-variable .pqb-btn').each(function () {
			var qty          = parseInt($(this).data('quantity'), 10);
			// max_qty is '' when stock is not managed or backorders are allowed — no limit
			var exceedsStock = !pqb_current_variation.backorders_allowed
				&& maxQty !== ''
				&& qty > (parseInt(maxQty, 10) - cartQty);
			$(this).prop('disabled', exceedsStock).toggleClass('disabled', exceedsStock);
		});
	}


	// --- CLICK HANDLER: set quantity and trigger Add to Cart -------------------------

	$(document).on('click', '.pqb-btn:not([disabled])', function () {
		pqb_pending_qty = parseInt($(this).data('quantity'), 10);
		$('input.qty').val(pqb_pending_qty).trigger('change');
		$('.single_add_to_cart_button').trigger('click');
	});


	// --- AFTER ADD TO CART: update cart tracking and re-evaluate buttons -------------

	$(document.body).on('added_to_cart', function () {
		if (pqb_pending_qty <= 0) {
			return;
		}

		if (pqb_current_variation) {
			var vid = pqb_current_variation.variation_id;
			pqb_variation_cart_qtys[vid] = (pqb_variation_cart_qtys[vid] || 0) + pqb_pending_qty;
			pqb_update_variable_buttons();
		} else {
			pqb_cart_qty += pqb_pending_qty;
			pqb_update_simple_buttons();
		}

		pqb_pending_qty = 0;
	});


	// --- VARIABLE PRODUCTS: respond to variation selection ---------------------------

	$('form.variations_form').on('found_variation', function (event, variation) {
		pqb_current_variation = variation;
		pqb_update_variable_buttons();
	});

	$('form.variations_form').on('reset_data', function () {
		pqb_current_variation = null;
		$('.pqb-variable .pqb-btn').prop('disabled', true).addClass('disabled');
	});

});
