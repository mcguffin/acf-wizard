class WizardPrefill {

	static #instances = {}

	static factory(field) {
		if ( ! WizardPrefill.#instances[field.getKey()] ) {
			WizardPrefill.#instances[field.getKey()] = new WizardPrefill(field)
		}
		return WizardPrefill.#instances[field.getKey()]
	}

	get prefillRows() {
		return this.field.$el.get(0).querySelectorAll('tr.prefill:not(.acf-wizard-prefill-template)')
	}

	constructor(field) {
		this.field = field
		this.table = this.field.$el.get(0).querySelector('.acf-wizard-prefill-table')

	}

	update() {
		this.prefillRows.forEach( row => this.setupRow(row) )
		this.maybeAppendRow()
		this.field.save();
	}

	maybeAppendRow() {
		const rows        = this.prefillRows
		let newRow = false,
			rowTemplate,
			uid
		// console.log(rows.length,rows.length ? getRowFieldKey( rows[rows.length-1] ):'', ! rows.length || '' !== getRowFieldKey( rows[rows.length-1] )  )
		if ( ! rows.length || '' !== this.getRowFieldKey( rows[rows.length-1] ) ) {
			rowTemplate = this.table.querySelector('.acf-wizard-prefill-template')
			uid         = acf.uniqid()
			newRow      = rowTemplate.cloneNode(true)
			this.table.querySelector('tbody').insertBefore( newRow, rowTemplate )
			newRow.setAttribute( 'data-index', uid )
			newRow.querySelector('td.field select').setAttribute('name',`${this.field.getInputName()}[prefill_values][${uid}][field_key]`)
			newRow.classList.remove('acf-wizard-prefill-template','acf-hidden')
		}

	}

	setupRow(row) {
		const fieldKey = this.getRowFieldKey( row )
		let field, val = '', el
		if ( undefined !== fieldKey ) {
			field = acf.getFieldObjects( { key: fieldKey } )[0]

			// on type change
			if ( field.getType() !== row.getAttribute('data-type') ) {
				// hidden value
				el = row.querySelector('td.value [type="hidden"]:not([name$="[]"])')
				if ( el ) {
					val = el.value
				} else {
					val = Array.from(row.querySelectorAll('td.value [type="hidden"][name$="[]"]')).map( hidden => hidden.value )
				}

				row.querySelector('td.value').innerHTML = this.getPrefillInput(
					field,
					row.getAttribute('data-index'),
					val
				)
				row.setAttribute('data-type',field.getType())
			}

		} else {
			row.remove()
		}

	}

	getPrefillInput( fillField, index, value = '' ) {

		const choices = () => fillField.$el
			.find('[data-name="choices"] textarea').val().split('\n')
			.map( c => {
				const [ id, text ] = c.split(/ : (.*)/s)
				if ( ! id.trim() ) {
					return false
				}
				return {
					id,
					text: text.trim() ? text.trim() : id
				}
			})
			.filter( el => el !== false )

		const select = ( multiple = false ) => [
			`<select ${multiple?'multiple':''} name="${this.field.getInputName()}[prefill_values][${index}][val]${multiple?'[]':''}" value="${value}">`,
			choices().map( c => `<option value="${c.id}" ${(!multiple && c.id===value ) || (multiple && value.includes(c.id) )?'selected':''}>${c.text}</option>` ),
			'</select>'
		].join('')
		const numeric = (forceMinMax = false) => {

			const $min  = fillField.$el.find('[data-name="min"] input')
			const $max  = fillField.$el.find('[data-name="max"] input')
			const $step = fillField.$el.find('[data-name="step"] input')

			let min  = fillField.$el.find('[data-name="min"] input').val()
			let max  = fillField.$el.find('[data-name="max"] input').val()
			let step = fillField.$el.find('[data-name="step"] input').val()

			if ( forceMinMax && ! min ) {
				min = $min.attr('placeholder')
			}
			if ( forceMinMax && ! max ) {
				max = $max.attr('placeholder')
			}
			if ( forceMinMax && ! step ) {
				step = $step.attr('placeholder')
			}

			return `<input type="number" name="${this.field.getInputName()}[prefill_values][${index}][val]" value="${value}" min="${min}" max="${max}" step="${step}" />`
		}
		const uis = {
			'text': () => `<input type="text" name="${this.field.getInputName()}[prefill_values][${index}][val]" value="${value}" />`,
			// TODO: min, max, step
			'number': () => `<input type="number" name="${this.field.getInputName()}[prefill_values][${index}][val]" value="${value}" />`,
			// TODO: min, max, step
			'range': () => numeric(true),
			'email': () => `<input type="email" name="${this.field.getInputName()}[prefill_values][${index}][val]" value="${value}" />`,
			'url': () => `<input type="text" name="${this.field.getInputName()}[prefill_values][${index}][val]" value="${value}" />`,
			// choice
			'select': () => select( fillField.$el.find('[data-name="multiple"] [type="checkbox"]').is(':checked') ),
			'checkbox': () => select(true),
			'radio': () => select(false),
			'button_group': () => select(false),
			'true_false': () => `<input type="hidden" value="0" name="${this.field.getInputName()}[prefill_values][${index}][val]" /><input type="checkbox" value="1" name="${this.field.getInputName()}[prefill_values][${index}][val]" />`,

		}

		return uis[ fillField.getType() ]()
	}
	getRowFieldKey(row) {
		return Array.from(row.querySelector('.field select').selectedOptions)
			.map( opt => {
				return opt.value
			})
			.find( el => el.indexOf('field_') !== -1 )
	}
}
module.exports = WizardPrefill
