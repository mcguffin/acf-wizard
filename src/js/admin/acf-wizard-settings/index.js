import $ from 'jquery';
import WizardPrefill from 'acf-wizard-prefill';

const { i18n } = acf_wizard_field_groups
// i18n.noneChoice

const getTargetChoices = () => {
	return acf.getFieldObjects({type:'wizard_step'})
		.map( field => {
			return {
				id: field.getKey(),
				text: field.getLabel(),
			}
		})
}

const getPrefillFieldChoices = () => {
	const supportedTypes = [
		// basic
		'text', 'number', 'range', 'email', 'url',
		// choice
		'select', 'checkbox', 'radio', 'button_group', 'true_false',
		// relational
		// 'link', 'page_link', 'taxonomy',
		// jQuery
		// 'date_picker', 'date_time_picker', 'time_picker', 'color_picker',
	];
	return [ { id:'', text: i18n.noneChoice } ].concat( acf.getFieldObjects()
		.filter( field => supportedTypes.includes( field.getType() ) )
		.map( field => {
			return {
				id: field.getKey(),
				text: field.getLabel(),
			}
		} ) )

}

const renderSelects = () => {
	// render navigation target selects
	acf.getFieldObjects({type:'wizard_proceed'}).forEach( field => {

		acf.renderSelect(field.$el.find('[data-name="wizard_target"] select'), getTargetChoices() );

		const prefillChoices = getPrefillFieldChoices()

		field.$el.find('tr.prefill td.field select').each( (i,el) => {
			acf.renderSelect( $(el), prefillChoices );
			// TODO: disable duplicates
			WizardPrefill.factory(field).update()
		})

	})
}



const renderPrefillValues = () => {
	acf.getFieldObjects({type:'wizard_proceed'}).forEach( field => {
		WizardPrefill.factory(field).update()
	})
}

acf.addAction('render_field_settings/type=wizard_proceed', fieldEl => {
	const field = acf.getFieldObjects({key: fieldEl.attr('data-key')})[0]
	WizardPrefill.factory(field).update()
} )

acf.addAction('ready_fields', renderSelects )
acf.addAction('removed_field_object', renderSelects )
acf.addAction('append_field_object', renderSelects )
acf.addAction('new_field_object', renderSelects )
acf.addAction('change_field_object_label', renderSelects )

// acf.addAction('ready_fields', renderPrefillValues )
acf.addAction('removed_field_object', renderPrefillValues )
acf.addAction('change_field_object_choices', renderPrefillValues )
acf.addAction('change_field_type', renderPrefillValues )
acf.addAction('new_field_object', field => {
	if ( field.getType()==='wizard_proceed' ) {
		field.$el
			.on('change', '.field select', e => {
				WizardPrefill.factory(field).update()
			})
			.on('change', 'td.value *,[data-name="wizard_target"] select', e => {
				field.save()
			})
	}
} )
// min
// max
// step


// update_field_parent
$(document).on('click','.remove-prefill-value', e => e.target.closest('tr').remove())
