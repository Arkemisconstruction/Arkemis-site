(function (blocks, element, editor, components) {
	blocks.registerBlockType('arkemis/quote-form', {
		apiVersion: 3, title: 'Demande de soumission', icon: 'email-alt', category: 'widgets',
		edit: function () {
			return element.createElement('div', editor.useBlockProps(), element.createElement(components.Placeholder, {
				label: 'Demande de soumission', instructions: 'Formulaire traité par Arkemis Core. Prévisualisez la Page Contact pour afficher les champs et tester leur présentation.'
			}));
		},
		save: () => null
	});
})(wp.blocks, wp.element, wp.blockEditor, wp.components);
