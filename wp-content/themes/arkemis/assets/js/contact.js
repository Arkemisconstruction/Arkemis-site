// Le traitement reste entièrement serveur ; ce script place seulement le focus.
(function () {
	function focusMessage() {
		const message = document.querySelector('.arkemis-form-errors, .arkemis-quote-confirmation');
		if (message) message.focus();
	}
	if (document.readyState === 'complete') focusMessage();
	else window.addEventListener('load', focusMessage, {once: true});
})();
