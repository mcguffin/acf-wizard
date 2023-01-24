import Wizard from 'acf-wizard.js';

const WizardProceed = acf.Field.extend({
	type: 'wizard_step',
	initialize: function() {

		if (this.$el.hasClass('acf-wizard')) {
			return;
		}
		// collect, init, ...
	}
})

acf.registerFieldType(WizardProceed);
