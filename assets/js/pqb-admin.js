jQuery(document).ready(function ($) {

	var MAX_BEFORE_WARNING = 10;

	function pqb_count_quantities(val) {
		return val.trim() === '' ? 0 : val.split(',').filter(function (v) { return v.trim() !== ''; }).length;
	}

	// --- Product page: show/hide sold-individually notice in real time --------------
	var $soldCheckbox = $('#_sold_individually');
	if ($soldCheckbox.length) {
		$soldCheckbox.on('change', function () {
			$('.pqb-sold-notice').toggle($(this).is(':checked'));
		});
	}


	// --- Product page: warning element already exists in the HTML
	var $productInput = $('#pqb_quantities');
	if ($productInput.length) {
		$productInput.on('input', function () {
			$('#pqb-too-many-warning').toggle(pqb_count_quantities($productInput.val()) > MAX_BEFORE_WARNING);
		});
		$('#pqb-too-many-warning').toggle(pqb_count_quantities($productInput.val()) > MAX_BEFORE_WARNING);
	}

	// --- Product page: disable per-product fields when "use global" is checked
	var $useGlobal = $('#pqb_use_global');
	if ($useGlobal.length) {
		var $perProductFields = $('#pqb_quantities, #pqb_hide_standard, #pqb_button_prefix, #pqb_button_suffix');

		function pqb_toggle_per_product_fields() {
			var disabled = $useGlobal.is(':checked');
			$perProductFields.closest('.options_group').toggleClass('pqb-disabled-group', disabled);
		}

		$useGlobal.on('change', pqb_toggle_per_product_fields);
		pqb_toggle_per_product_fields();
	}

	// --- Prefix/suffix live preview -------------------------------------------------
	$('.pqb-btn-preview').each(function () {
		var $preview   = $(this);
		var $container = $preview.closest('td, .form-field');
		var $prefix    = $container.find('[name="pqb_button_prefix"]');
		var $suffix    = $container.find('[name="pqb_button_suffix"]');
		var num        = $preview.data('preview-num');

		function pqb_refresh_preview() {
			var pre = $prefix.val().trim();
			var suf = $suffix.val().trim();
			$preview.text((pre ? pre + ' ' : '') + num + (suf ? ' ' + suf : ''));
		}

		$prefix.on('input', pqb_refresh_preview);
		$suffix.on('input', pqb_refresh_preview);
	});


	// --- Product page: replace warning when quantities empty + replace mode --------
	var $hideStandard = $('#pqb_hide_standard');
	if ($productInput.length && $hideStandard.length) {
		var $replaceWarning = $('<p class="form-field pqb-warning" style="display:none"></p>').text(pqb_admin_params.replace_warning);
		$hideStandard.closest('.options_group').append($replaceWarning);

		function pqb_check_replace_warning() {
			var isEmpty    = pqb_count_quantities($productInput.val()) === 0;
			var isReplace  = $hideStandard.val() === 'yes';
			var isGlobal   = $('#pqb_use_global').is(':checked');
			$replaceWarning.toggle(isEmpty && isReplace && !isGlobal);
		}

		$productInput.on('input', pqb_check_replace_warning);
		$hideStandard.on('change', pqb_check_replace_warning);
		$('#pqb_use_global').on('change', pqb_check_replace_warning);
		pqb_check_replace_warning();
	}


	// --- Global settings page: move bulk action buttons below Save Changes ----------
	var $bulkActions = $('#pqb-bulk-actions');
	if ($bulkActions.length) {
		$('p.submit').after($bulkActions);
		$bulkActions.show();
	}


	// --- Global settings page: inject warning element after the field description
	var $globalInput = $('#pqb_global_quantities');
	if ($globalInput.length) {
		var $globalWarning = $('<p class="pqb-warning" style="display:none;"></p>').text(pqb_admin_params.too_many_warning);
		$globalInput.closest('td').append($globalWarning);

		$globalInput.on('input', function () {
			$globalWarning.toggle(pqb_count_quantities($globalInput.val()) > MAX_BEFORE_WARNING);
		});
		$globalWarning.toggle(pqb_count_quantities($globalInput.val()) > MAX_BEFORE_WARNING);

		var $globalHideStd      = $('#pqb_global_hide_standard');
		var $globalReplaceWarning = $('<p class="pqb-warning" style="display:none;margin-top:5px"></p>').text(pqb_admin_params.replace_warning);
		$globalHideStd.closest('td').append($globalReplaceWarning);

		function pqb_check_global_replace_warning() {
			var isEmpty   = pqb_count_quantities($globalInput.val()) === 0;
			var isReplace = $globalHideStd.val() === 'yes';
			$globalReplaceWarning.toggle(isEmpty && isReplace);
		}

		$globalInput.on('input', pqb_check_global_replace_warning);
		$globalHideStd.on('change', pqb_check_global_replace_warning);
		pqb_check_global_replace_warning();
	}

});
