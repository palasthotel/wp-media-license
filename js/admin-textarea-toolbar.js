(function () {
	function getSafeHref(href) {
		var value = (href || '').trim();

		if (
			value === '' ||
			value.indexOf('https://') === 0 ||
			value.indexOf('http://') === 0 ||
			value.indexOf('mailto:') === 0 ||
			value.indexOf('/') === 0 ||
			value.indexOf('#') === 0
		) {
			return value;
		}

		return '';
	}

	function appendSanitizedNodes(sourceNode, targetNode, doc) {
		Array.prototype.forEach.call(sourceNode.childNodes, function (node) {
			var element;
			var href;

			if (node.nodeType === Node.TEXT_NODE) {
				targetNode.appendChild(doc.createTextNode(node.textContent));
				return;
			}

			if (node.nodeType !== Node.ELEMENT_NODE) {
				return;
			}

			switch (node.tagName.toLowerCase()) {
				case 'strong':
				case 'em':
					element = doc.createElement(node.tagName.toLowerCase());
					appendSanitizedNodes(node, element, doc);
					targetNode.appendChild(element);
					break;
				case 'a':
					href = getSafeHref(node.getAttribute('href'));
					element = doc.createElement('a');
					if (href !== '') {
						element.setAttribute('href', href);
					}
					appendSanitizedNodes(node, element, doc);
					targetNode.appendChild(element);
					break;
				default:
					appendSanitizedNodes(node, targetNode, doc);
			}
		});
	}

	function updatePreview(textarea) {
		var preview = document.querySelector('.media-license-textarea-preview[data-source="' + textarea.id + '"]');
		var parser;
		var parsed;
		var wrapper;
		var container;

		if (!preview) {
			return;
		}

		parser = new DOMParser();
		parsed = parser.parseFromString('<div>' + textarea.value + '</div>', 'text/html');
		wrapper = parsed.body.firstChild;
		container = document.createElement('div');

		appendSanitizedNodes(wrapper, container, document);
		preview.replaceChildren(container);
	}

	function updateAllPreviews() {
		Array.prototype.forEach.call(document.querySelectorAll('.media-license-textarea-preview[data-source]'), function (preview) {
			var sourceId = preview.getAttribute('data-source');
			var textarea = sourceId ? document.getElementById(sourceId) : null;

			if (textarea) {
				updatePreview(textarea);
			}
		});
	}

	function wrapSelection(textarea, before, after, fallbackText) {
		var start = textarea.selectionStart || 0;
		var end = textarea.selectionEnd || 0;
		var value = textarea.value;
		var selected = value.slice(start, end) || fallbackText;
		var replacement = before + selected + after;

		textarea.value = value.slice(0, start) + replacement + value.slice(end);
		textarea.focus();
		textarea.selectionStart = start + before.length;
		textarea.selectionEnd = start + before.length + selected.length;
		textarea.dispatchEvent(new Event('change', { bubbles: true }));
		textarea.dispatchEvent(new Event('input', { bubbles: true }));
	}

	function getLinkForm(textarea) {
		return document.querySelector('.media-license-textarea-link-form[data-target="' + textarea.id + '"]');
	}

	function openLinkForm(textarea) {
		var form = getLinkForm(textarea);
		var input;

		if (!form) {
			return;
		}

		form.hidden = false;
		input = form.querySelector('.media-license-textarea-link-input');
		if (input) {
			input.focus();
			input.select();
		}
	}

	function closeLinkForm(textarea) {
		var form = getLinkForm(textarea);
		var input;

		if (!form) {
			return;
		}

		form.hidden = true;
		input = form.querySelector('.media-license-textarea-link-input');
		if (input && input.value.trim() === '') {
			input.value = 'https://';
		}
		textarea.focus();
	}

	function applyLink(textarea) {
		var form = getLinkForm(textarea);
		var input;
		var url;

		if (!form) {
			return;
		}

		input = form.querySelector('.media-license-textarea-link-input');
		url = input ? input.value.trim() : '';
		if (!url) {
			return;
		}

		wrapSelection(textarea, '<a href="' + url.replace(/"/g, '&quot;') + '">', '</a>', 'Link text');
		closeLinkForm(textarea);
	}

	function onClick(event) {
		var button = event.target.closest('.media-license-textarea-button');
		var applyButton = event.target.closest('.media-license-textarea-link-apply');
		var cancelButton = event.target.closest('.media-license-textarea-link-cancel');
		var toolbar;
		var form;
		var target;
		var targetId;

		if (!button && !applyButton && !cancelButton) {
			return;
		}

		event.preventDefault();

		if (button) {
			toolbar = button.closest('.media-license-textarea-toolbar');
			target = toolbar ? document.getElementById(toolbar.getAttribute('data-target')) : null;
		} else {
			form = (applyButton || cancelButton).closest('.media-license-textarea-link-form');
			targetId = form ? form.getAttribute('data-target') : null;
			target = targetId ? document.getElementById(targetId) : null;
		}

		if (!target) {
			return;
		}

		if (button) {
			switch (button.getAttribute('data-tag')) {
				case 'strong':
					wrapSelection(target, '<strong>', '</strong>', 'Bold text');
					break;
				case 'em':
					wrapSelection(target, '<em>', '</em>', 'Italic text');
					break;
				case 'link':
					openLinkForm(target);
					break;
			}
		} else if (applyButton) {
			applyLink(target);
		} else if (cancelButton) {
			closeLinkForm(target);
		}

		window.setTimeout(updateAllPreviews, 0);
	}

	function onKeyDown(event) {
		var input = event.target.closest('.media-license-textarea-link-input');
		var form;
		var targetId;
		var target;

		if (!input) {
			return;
		}

		form = input.closest('.media-license-textarea-link-form');
		targetId = form ? form.getAttribute('data-target') : null;
		target = targetId ? document.getElementById(targetId) : null;
		if (!target) {
			return;
		}

		if (event.key === 'Enter') {
			event.preventDefault();
			applyLink(target);
		} else if (event.key === 'Escape') {
			event.preventDefault();
			closeLinkForm(target);
		}
	}

	function onInput(event) {
		var textarea = event.target.closest('textarea');

		if (!textarea || !textarea.id || !document.querySelector('.media-license-textarea-preview[data-source="' + textarea.id + '"]')) {
			return;
		}

		updatePreview(textarea);
	}

	function onFocusIn(event) {
		var textarea = event.target.closest('textarea');

		if (!textarea || !textarea.id || !document.querySelector('.media-license-textarea-preview[data-source="' + textarea.id + '"]')) {
			return;
		}

		updatePreview(textarea);
	}

	document.addEventListener('click', onClick);
	document.addEventListener('input', onInput);
	document.addEventListener('focusin', onFocusIn);
	document.addEventListener('keydown', onKeyDown);
	document.addEventListener('DOMContentLoaded', updateAllPreviews);
}());
