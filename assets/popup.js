/* Newsletter Popup — substitueix window.alert per un banner inline dins del popup de Benchmark */
(function () {
	var originalAlert = window.alert;

	var translations = {
		'Please enter a valid email address': 'Introdueix un correu electrònic vàlid.',
		'Please enter your email': 'Introdueix el teu correu electrònic.',
		'Please enter an email address': 'Introdueix un correu electrònic.',
		'Invalid email': 'El correu electrònic no és vàlid.',
		'This email is already subscribed': 'Aquest correu ja està subscrit.',
		'Please fill all the required fields': 'Omple tots els camps obligatoris.'
	};

	function translate(msg) {
		msg = String(msg).trim();
		if (translations[msg]) return translations[msg];
		var m = msg.match(/^Please\s+(select|enter|check)\s+(?:the\s+)?(.+?)\.?$/i);
		if (m) {
			var action = m[1].toLowerCase();
			var field = m[2].replace(/\s+/g, ' ').trim();
			if (action === 'select' || action === 'check') return 'Cal acceptar: ' + field + '.';
			return 'Introdueix: ' + field + '.';
		}
		return msg;
	}

	function findVisibleBenchmarkContainer() {
		var containers = document.querySelectorAll('[id^="signupFormContainer_"]');
		for (var i = 0; i < containers.length; i++) {
			var c = containers[i];
			var style = window.getComputedStyle(c);
			if (style.display !== 'none' && style.visibility !== 'hidden') {
				return c;
			}
		}
		return null;
	}

	function showInlineError(container, msg) {
		var suffix = container.id.replace('signupFormContainer_', '');
		var target = container.querySelector('#formbox_screen_subscribe_' + suffix)
			|| container.querySelector('[id^="formbox_screen_subscribe_"]')
			|| container.querySelector('.formbox-editor_' + suffix)
			|| container;

		var existing = target.querySelector('.anp-inline-error');
		if (existing) existing.remove();

		var err = document.createElement('div');
		err.className = 'anp-inline-error';
		err.setAttribute('role', 'alert');

		var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		icon.setAttribute('class', 'anp-inline-error__icon');
		icon.setAttribute('viewBox', '0 0 20 20');
		icon.setAttribute('fill', 'currentColor');
		icon.setAttribute('aria-hidden', 'true');
		icon.innerHTML = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';

		var text = document.createElement('span');
		text.className = 'anp-inline-error__text';
		text.textContent = translate(msg);

		err.appendChild(icon);
		err.appendChild(text);
		target.insertBefore(err, target.firstChild);

		setTimeout(function () {
			if (err.parentNode) err.parentNode.removeChild(err);
		}, 6000);
	}

	window.alert = function (msg) {
		var container = findVisibleBenchmarkContainer();
		if (container) {
			showInlineError(container, msg);
			return;
		}
		return originalAlert.apply(window, arguments);
	};
})();
