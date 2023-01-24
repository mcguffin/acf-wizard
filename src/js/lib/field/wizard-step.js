import Wizard from 'acf-wizard.js';

const WizardStep = acf.Field.extend({
	type: 'wizard_step',

	initialize: function() {

		if ( this.$el.hasClass('acf-wizard')) {
			return;
		}

		this.$el.addClass('acf-wizard')

		this.wizard = Wizard.factory( this.$el.parent().get(0) )
		this.wizard.add( this.$el.get(0) )

		// collect, init, ...
	}
})

acf.registerFieldType(WizardStep);
