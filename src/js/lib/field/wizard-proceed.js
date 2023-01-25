import Wizard from 'acf-wizard.js';

const WizardProceed = acf.Field.extend({
	type: 'wizard_proceed',
	events: {
		removeField: 'onRemove'
	},
	$button: function() {
		return this.$el.find('button[data-wizard-action]')
	},
	initialize: function() {
		if ( !! this.wizard ) {
			return;
		}

		this.wizard = Wizard.findByElement( this.$el.get(0) )
		this.wizard.addEventListener( 'acf_wizard/navigated', e => {

			this.onWizardNavigate(e)
		} )

		// collect, init, ...
	},
	onWizardNavigate: function(e) {
		// get target page
		let active = false
		const action = this.$button().attr('data-wizard-action')
		const steps  = parseInt( this.$button().attr('data-wizard-steps') )
		const target = this.$button().attr('data-wizard-target')

		if ( 'goto' === action ) {
			active = e.target.canNavigate( target )
		} else if ( 'forward' === action ) {
			active = e.target.canNavigateSteps( steps )
			console.log(e.target,active,steps)

		} else if ( 'back' === action ) {
			active = e.target.canNavigateSteps( steps * -1 )
		}
		this.$button().prop( 'disabled', ! active )
	},
	onRemove: function() {
		// this.wizard.destructor()
	}
})

acf.registerFieldType(WizardProceed);
