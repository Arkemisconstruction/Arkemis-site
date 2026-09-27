// Le traitement reste entièrement serveur ; ce script place le focus et vérifie la présence du jeton Turnstile.
(function () {
	function focusMessage() {
		const message = document.querySelector('.arkemis-form-errors, .arkemis-quote-confirmation');
		if (message) message.focus();
	}
	function showTurnstileError(text) {
		const error = document.querySelector('[data-turnstile-error]');
		if (!error) return;
		error.textContent = text;
		error.hidden = false;
		error.focus();
	}
	window.arkemisQuoteTurnstileSuccess = function () {
		const error = document.querySelector('[data-turnstile-error]');
		if (error) error.hidden = true;
	};
	window.arkemisQuoteTurnstileExpired = function () {
		showTurnstileError('La vérification a expiré. Confirmez-la de nouveau avant l’envoi.');
	};
	window.arkemisQuoteTurnstileError = function () {
		showTurnstileError('La vérification de sécurité est indisponible. Réessayez.');
	};
	function bindTurnstileGuard() {
		const form = document.querySelector('.arkemis-quote-form[data-turnstile-required="1"]');
		if (!form) return;
		form.addEventListener('submit', function (event) {
			const response = form.querySelector('[name="cf-turnstile-response"]');
			if (response && response.value.trim()) return;
			event.preventDefault();
			showTurnstileError('Veuillez confirmer la vérification de sécurité avant d’envoyer votre demande.');
		});
	}
	function initialize() {
		focusMessage();
		bindTurnstileGuard();
	}
	if (document.readyState === 'complete') initialize();
	else window.addEventListener('load', initialize, {once: true});
})();
