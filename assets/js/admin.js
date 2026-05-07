(function ($) {
	'use strict';

	var unique = function () {
		return Date.now().toString() + Math.floor(Math.random() * 10000).toString();
	};

	var refreshBuilderTitles = function () {
		$('.cfl-builder-row').each(function () {
			var $row = $(this);
			var label = $row.find('> .cfl-builder-row-body input[name$="[label]"]').first().val();
			$row.find('> .cfl-builder-row-header .cfl-builder-title').text(label || 'New Field');
		});
	};

	var initSortable = function ($scope) {
		$scope.find('.cfl-builder-rows, .cfl-subfield-rows, .cfl-repeater-rows').sortable({
			handle: '.cfl-sort-handle',
			items: '> .cfl-builder-row, > .cfl-repeater-row',
			placeholder: 'cfl-sort-placeholder'
		});
	};

	var updateLocationVisibility = function () {
		$('.cfl-location-row').each(function () {
			var $row = $(this);
			var param = $row.find('.cfl-location-param').val();
			$row.find('.cfl-location-value').hide().prop('disabled', true);
			if (param === 'page') {
				$row.find('.cfl-value-page').show().prop('disabled', false);
			} else if (param === 'template') {
				$row.find('.cfl-value-template').show().prop('disabled', false);
			} else {
				$row.find('.cfl-value-post-type').show().prop('disabled', false);
			}
		});
	};

	var updateFieldTypeVisibility = function () {
		$('.cfl-builder-row').each(function () {
			var $row = $(this);
			var type = $row.find('> .cfl-builder-row-body .cfl-field-type').first().val();
			var choices = ['select', 'checkbox', 'radio'].indexOf(type) !== -1;
			$row.find('> .cfl-builder-row-body .cfl-choice-setting').toggle(choices);
			$row.find('> .cfl-builder-row-body > .cfl-subfields').toggle(type === 'repeater');
		});
	};

	var conditionValueFor = function (fieldName) {
		var $field = $('[data-field-name="' + fieldName + '"]').first();
		var $input = $field.find(':input').not('button').first();

		if ($input.is(':checkbox')) {
			return $input.is(':checked') ? $input.val() : '';
		}

		return $input.val();
	};

	var updateConditions = function () {
		$('[data-cfl-condition-field]').each(function () {
			var $field = $(this);
			var conditionField = $field.data('cfl-condition-field');
			var conditionValue = String($field.data('cfl-condition-value'));
			$field.toggle(String(conditionValueFor(conditionField)) === conditionValue);
		});
	};

	var cloneFieldTemplate = function (baseName) {
		var html = $('#cfl-field-template').html();
		return html.replace(/cfl_group_fields\[__INDEX__\]/g, baseName);
	};

	var rowBaseName = function ($row) {
		var name = $row.find('> .cfl-builder-row-body input, > .cfl-builder-row-body select, > .cfl-builder-row-body textarea').first().attr('name') || '';
		return name.replace(/\[(label|name|type|default_value|instructions|choices|required|conditional)\].*$/, '');
	};

	var escapeRegExp = function (text) {
		return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	};

	var updateRepeaterRowNames = function ($source, $clone, index) {
		var sourceName = $source.find(':input[name]').first().attr('name') || '';
		var match = sourceName.match(/^(.*)\[([^\]]+)\]\[[^\]]+\]$/);

		if (!match) {
			return;
		}

		var rowPrefix = match[1];
		var rowIndex = match[2];
		var pattern = new RegExp(escapeRegExp(rowPrefix + '[' + rowIndex + ']'), 'g');

		$clone.find(':input').each(function () {
			var name = $(this).attr('name');
			if (!name) {
				return;
			}
			$(this).attr('name', name.replace(pattern, rowPrefix + '[' + index + ']'));
		});
	};

	var initMediaField = function ($button) {
		var $field = $button.closest('.cfl-media-field');
		var type = $field.data('type') || 'file';
		var frame = wp.media({
			title: type === 'image' ? cflAdmin.i18n.selectImage : cflAdmin.i18n.selectFile,
			button: { text: cflAdmin.i18n.useFile },
			library: type === 'image' ? { type: 'image' } : {},
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$field.find('input[type="hidden"]').val(attachment.id).trigger('change');
			if (type === 'image') {
				$field.find('.cfl-media-preview').html('<img src="' + attachment.sizes.thumbnail.url + '" alt="">');
			} else {
				$field.find('.cfl-file-name').text(attachment.filename || attachment.url);
			}
		});

		frame.open();
	};

	$(function () {
		initSortable($(document));
		updateLocationVisibility();
		updateFieldTypeVisibility();
		updateConditions();
		refreshBuilderTitles();

		$(document).on('click', '.cfl-tab', function () {
			var tab = $(this).data('tab');
			$('.cfl-tab').removeClass('is-active');
			$(this).addClass('is-active');
			$('.cfl-tab-panel').removeClass('is-active');
			$('[data-panel="' + tab + '"]').addClass('is-active');
		});

		$(document).on('input', '.cfl-builder-row input[name$="[label]"]', refreshBuilderTitles);

		$(document).on('change', '.cfl-field-type', updateFieldTypeVisibility);

		$(document).on('click', '.cfl-add-field', function () {
			var $builder = $(this).closest('.cfl-field-builder');
			var index = $builder.data('next-index') || 0;
			$builder.data('next-index', index + 1);
			$builder.find('> .cfl-builder-rows').append(cloneFieldTemplate('cfl_group_fields[' + index + ']'));
			initSortable($builder);
			updateFieldTypeVisibility();
			refreshBuilderTitles();
		});

		$(document).on('click', '.cfl-add-subfield', function () {
			var $subfields = $(this).closest('.cfl-subfields');
			var parentBase = rowBaseName($subfields.closest('.cfl-builder-row'));
			var index = $subfields.data('subfield-next') || 0;
			$subfields.data('subfield-next', index + 1);
			$subfields.find('> .cfl-subfield-rows').append(cloneFieldTemplate(parentBase + '[sub_fields][' + index + ']'));
			initSortable($subfields);
			updateFieldTypeVisibility();
			refreshBuilderTitles();
		});

		$(document).on('click', '.cfl-builder-toggle', function () {
			$(this).closest('.cfl-builder-row').toggleClass('is-collapsed');
		});

		$(document).on('click', '.cfl-builder-remove', function () {
			$(this).closest('.cfl-builder-row').remove();
		});

		$(document).on('change', '.cfl-location-param', updateLocationVisibility);

		$(document).on('click', '.cfl-add-location', function () {
			var $wrap = $(this).closest('.cfl-location-rules');
			var index = $wrap.data('next-index') || 0;
			$wrap.data('next-index', index + 1);
			$wrap.find('.cfl-location-rows').append($('#cfl-location-template').html().replace(/__INDEX__/g, index));
			updateLocationVisibility();
		});

		$(document).on('click', '.cfl-remove-location', function () {
			$(this).closest('.cfl-location-row').remove();
		});

		$(document).on('click', '.cfl-media-select', function () {
			initMediaField($(this));
		});

		$(document).on('click', '.cfl-media-clear', function () {
			var $field = $(this).closest('.cfl-media-field');
			$field.find('input[type="hidden"]').val('').trigger('change');
			$field.find('.cfl-media-preview, .cfl-file-name').empty();
		});

		$(document).on('click', '.cfl-repeater-add', function () {
			var $repeater = $(this).closest('.cfl-repeater');
			var index = unique();
			var $template = $repeater.find('> .cfl-repeater-template');
			var token = $template.data('index-token') || '__INDEX__';
			var html = $template.html().split(token).join(index);
			$repeater.find('> .cfl-repeater-rows').append(html);
			initSortable($repeater);
		});

		$(document).on('click', '.cfl-repeater-remove', function () {
			$(this).closest('.cfl-repeater-row').remove();
		});

		$(document).on('click', '.cfl-repeater-collapse', function () {
			$(this).closest('.cfl-repeater-row').toggleClass('is-collapsed');
		});

		$(document).on('click', '.cfl-repeater-duplicate', function () {
			var $row = $(this).closest('.cfl-repeater-row');
			$.post(cflAdmin.ajaxUrl, {
				action: 'cfl_duplicate_repeater_row',
				nonce: cflAdmin.nonce
			}).done(function () {
				var $clone = $row.clone(false, false);
				updateRepeaterRowNames($row, $clone, unique());
				$row.after($clone);
			});
		});

		$(document).on('change input', '.cfl-meta-box :input', updateConditions);
	});
})(jQuery);
