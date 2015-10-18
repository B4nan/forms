/* =========================================================
 *
 * jQuery phone prefix plugin v0.2
 * by Ondřej Exner, Martin Adamek
 *
 * Required js: jquery.dd.min.js
 * Required css: jquery.dd.css, jquery.dd.flags.css
 * Required img: dd_arrow.png, flags.png, flags_blank.gif
 *
 * =========================================================
 * Usage
 * =========================================================
 *
 * $(input_element).phonePrefix({
 * 		baseUrl: 'nette baseUrl variable',
 * 		nameAppendPrefix: true|false, // should be prefix shown in name of country? eg. "USA (+1)"
 * 		prefixes: {
 *			'country code': {
 *				name: 'Name of country',
 *				prefix: 'phone prefix'
 *			},
 *			...
 * 		}
 * });
 *
 * =========================================================
 */

(function($) {
	$.fn.phonePrefix = function(options) {
		options = $.extend({
			baseUrl: '',
			nameAppendPrefix: true,
			default: 'cz',
			prefixes: {
				'cz': { name: 'Česká republika', 					prefix: '+420' },
				'sk': { name: 'Slovenská republika',				prefix: '+421' },
				'us': { name: 'USA', 								prefix: '+1' },
				'ro': { name: 'România', 							prefix: '+40' },
				'ch': { name: 'Schweizerische Eidgenossenschaft', 	prefix: '+41' },
				'li': { name: 'Fürstentum Liechtenstein', 			prefix: '+423' },
				'at': { name: 'Republik Österreich', 				prefix: '+43' },
				'gb': { name: 'United Kingdom', 					prefix: '+44' },
				'dk': { name: 'Danmark',							prefix: '+45' },
				'se': { name: 'Sverige', 							prefix: '+46' },
				'no': { name: 'Kongeriket Norge', 					prefix: '+47' },
				'pl': { name: 'Polska', 							prefix: '+48' },
				'de': { name: 'Bundesrepublik Deutschland', 		prefix: '+49' },
				'gr': { name: 'Ελλάδα', 							prefix: '+30' },
				'nl': { name: 'Nederland', 							prefix: '+31' },
				'be': { name: 'België', 							prefix: '+32' },
				'fr': { name: 'République française', 				prefix: '+33' },
				'es': { name: 'Reino de España', 					prefix: '+34' },
				'pt': { name: 'República Portuguesa', 				prefix: '+351' },
				'lu': { name: 'Groussherzogtum Lëtzebuerg', 		prefix: '+352' },
				'ie': { name: 'Ireland', 							prefix: '+353' },
				'is': { name: 'Ísland', 							prefix: '+354' },
				'mt': { name: 'Repubblika ta’ Malta', 				prefix: '+356' },
				'fi': { name: 'Suomen Tasavalta', 					prefix: '+358' },
				'bg': { name: 'България', 							prefix: '+359' },
				'hu': { name: 'Magyarország', 						prefix: '+36' },
				'lt': { name: 'Lietuvos Respublika', 				prefix: '+370'},
				'lv': { name: 'Latvija', 							prefix: '+371' },
				'ee': { name: 'Eesti', 								prefix: '+372' },
				'am': { name: 'Հայաստանի Հանրապետություն', 			prefix: '+374' },
				'by': { name: 'Рэспубліка Беларусь', 				prefix: '+375' },
				'ad': { name: 'Principat d\'Andorra', 				prefix: '+376' },
				'mc': { name: 'Monaco', 							prefix: '+377' },
				'hr': { name: 'Hrvatska', 							prefix: '+385' },
				'si': { name: 'Slovenija', 							prefix: '+386' },
				'ba': { name: 'Bosna i Hercegovina', 				prefix: '+387' },
				'mk': { name: 'Македонија', 						prefix: '+389' },
				'it': { name: 'Italia', 							prefix: '+39' }
			}
		}, options);

		this.each(function() {
			var $input = $(this);

			var phone = $input.val();
			if (phone.charAt(0) === '+') { // current input has prefix
				for (var code in options.prefixes) {
					if (phone.indexOf(options.prefixes[code].prefix) === 0) {
						$input.data('pp-phone-prefix', code);
						break;
					}
				}
			} else { // prepend first prefix
				var val = options.prefixes[options.default].prefix + $input.val();
				$input.data('pp-phone-prefix', options.default).val(val);
			}

			// create structure
			var wrap = $('<div />').addClass('pp-phone').css({
				position: 'relative',
				display: 'inline-block',
				width: $input.outerWidth(true)
			});
			wrap = $input.wrap(wrap).parent();

			$input.addClass('pp-phone-prefix-input').css({
				'padding-left': '40px',
				'-webkit-box-sizing': 'border-box',
				'-moz-box-sizing': 'border-box',
				'box-sizing': 'border-box',
				'width': $input.outerWidth() + 'px',
				'height': $input.outerHeight() + 'px'
			});

			$input.after('<select class="pp-phone-prefix" style="position:absolute;top:0;left:0;width:25px;height:11px;"></select>');
			var $select = $input.siblings('.pp-phone-prefix');

			for (var code in options.prefixes) {
				var data = options.prefixes[code];
				var name = data.name;
				if (options.nameAppendPrefix) {
					name = name + ' (' + data.prefix + ')';
				}
				var option = $('<option />').val(code).data('image', options.baseUrl + '/img/flags_blank.gif').data('imagecss', 'flag ' + code).text(name);
				if ($input.data('pp-phone-prefix') && $input.data('pp-phone-prefix') === code) {
					option.prop('selected', true);
				}
				$select.append(option);
			}

			// init msDropdown plugin
			$select.msDropdown({
				animStyle: 'none'
			});
			$select.on('change', function() {
				var phone = $input.val();
				if (phone.charAt(0) === '+') { // current input has prefix
					var code = $input.data('pp-phone-prefix');
					var prefix = options.prefixes[code].prefix;
					phone = phone.substr(prefix.length);
				}
				var code = $(this).val();
				var prefix = options.prefixes[code].prefix;
				$input.val(prefix + phone);
				$input.data('pp-phone-prefix', code);
			});
			var dd = wrap.find('.dd');
			dd.css({
				'position': 'absolute',
				'top': Math.round(($input.outerHeight() - dd.outerHeight()) / 2) + 'px',
				'left': '10px'
			});

			$input.on('change keyup', function() {
				var i = 0;
				for (var code in options.prefixes) {
					if ($(this).val().indexOf(options.prefixes[code].prefix) === 0) {
						$input.data('pp-phone-prefix', code);
						$select.msDropdown().data('dd').set('selectedIndex', i);
						break;
					}
					i++;
				}
			});
		});

		return this;
	};
}(jQuery));