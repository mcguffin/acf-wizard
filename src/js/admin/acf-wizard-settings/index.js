const getChoices = () => {
	return acf.getFieldObjects({type:'wizard_step'}).map( field => {
		return {
			id: field.getKey(),
			text: field.getLabel(),
		}
	})
}

const renderSelects = () => {
	acf.getFieldObjects({type:'wizard_proceed'}).forEach( field => {
		acf.renderSelect(field.$el.find('[data-name="wizard_target"] select'), getChoices() );
	})
}

acf.addAction('ready_fields', renderSelects )
acf.addAction('removed_field_object', renderSelects )
acf.addAction('append_field_object', renderSelects )
acf.addAction('new_field_object', renderSelects )
acf.addAction('change_field_object_label', renderSelects )
