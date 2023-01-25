import Wizard from 'acf-wizard.js';

const WizardStep = acf.Field.extend({
	type: 'wizard_step',
	events: {
		removeField: 'onRemove'
	},
	initialize: function() {

		if ( !! this.wizard ) {
			return;
		}

		this.wizard = Wizard.factory( this.$el.parent().get(0) )
		this.wizard.add( this.$el.get(0) )

		// collect, init, ...
	},
	onRemove: function() {
		this.wizard.destructor()
	}
})

acf.registerFieldType(WizardStep);
