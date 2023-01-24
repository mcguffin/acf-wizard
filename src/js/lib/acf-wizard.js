
class Wizard {
	/** @var Array */
	static #instances = []

	/**
	 *	@return Wizard
	 */
	static factory(parent) {
		let wizard = Object.values( Wizard.#instances ).find( w => parent === w.parent )
		if ( undefined === wizard ) {
			wizard = new Wizard( parent )
		}
		return wizard
	}

	/**
	 *	@return Wizard
	 */
	static findByElement(el) {
		const idx = parseInt( el.closest('[data-wizard-container]').getAttribute('data-wizard-container') )
		return Wizard.#instances[idx]
	}

	/** @var MutationObserver */
	#observer

	/** @var DOMNode */
	parent

	/** @var DOMNode */
	stepper

	/** @var Array */
	steps = []

	get current() {
		let cur = false
		this.parent
			.querySelectorAll('.acf-field-wizard-step')
			.forEach( el => idx = el.matches('.active') ? el : cur )
		return cur
	}

	get currentNavItem() {
		let navItem = false
		try {
			this.parent
				.querySelectorAll('.acf-field-wizard-step')
				.forEach( el => {
					const testNavItem = this.stepper.querySelector(`[data-wizard-target="${el.getAttribute('data-key')}"]`)
					if ( testNavItem ) {
						navItem = testNavItem
					}
					if ( el.matches('.active') ) {
						throw ''
					}
				})

		} catch(err) {}
		return navItem
	}

	get currentIndex() {
		let idx = -1
		this.parent
			.querySelectorAll('.acf-field-wizard-step')
			.forEach( (el,i) => idx = el.matches('.active') ? i : idx )
		return idx
	}

	set currentIndex( idx ) {
		this.parent
			.querySelectorAll('.acf-field-wizard-step')
			.forEach( (el,i) => {
				if ( idx === i ) {
					this.goto(el.getAttribute('data-key'))
				}
			} )
	}

	/**
	 *	@param parent DOMNode
	 */
	constructor( parent ) {
		const idx = Wizard.#instances.length

		this.parent = parent
		this.stepper = document.createElement('div')

		this.parent.setAttribute('data-wizard-container',idx)
		this.stepper.classList.add('acf-wizard-stepper')
		this.parent.prepend( this.stepper )

		this.#observer = new MutationObserver( (mutations,observer) => {
			mutations.forEach( mutation => {

				const fieldKey = mutation.target.getAttribute('data-key')
				const isHidden = mutation.target.matches('.acf-hidden')

				// direct references
				document
					.querySelectorAll(`[data-wizard-action="goto"][data-wizard-target="${fieldKey}"]`)
					.forEach( el => {
						el.disabled = isHidden
					} )

			})
		})

		Wizard.#instances[idx] = this
	}

	destructor() {
		this.#observer.disconnect()
	}

	/**
	 *	@param el DOMNode
	 *	@return Wizard
	 */
	add(el) {

		const navType = el.getAttribute('data-wizard-nav')

		if ( 'none' === navType ) {
			return false
		}

		// add navigation element
		const btn = document.createElement('button');
		const fieldKey = el.getAttribute('data-key')

		btn.type = 'button'
		btn.setAttribute('data-wizard-action','goto')
		btn.setAttribute('data-wizard-target', fieldKey )
		if ( navType.includes('name') ) {
			btn.innerHTML = `<span class="acf-wizard-nav-item-name">${el.querySelector('.acf-label').textContent}</span>`
		}
		btn.classList.add('acf-wizard-nav-item')
		if ( navType.includes('number') ) {
			btn.classList.add('-numbered')
		}
		this.stepper.append(btn)

		if ( el.matches('.acf-field-wizard-step[data-conditions]:not([data-wizard-nav="none"])') ) {
			this.#observer.observe( el, { attributes: true } )
		}

		if ( this.currentIndex < 0 ) {
			this.goto(fieldKey)
		}

		return this
	}

	/**
	 *	@param fieldKey String
	 *	@return Wizard
	 */
	goto(fieldKey) {
		this.parent
			.querySelector(`:scope > [data-key="${fieldKey}"]`)
			.classList.add('active')

		this.parent
			.querySelectorAll(`:scope > .acf-field-wizard-step:not([data-key="${fieldKey}"])`)
			.forEach( el => {
				el.classList.remove('active')
			})
		const navItem = this.currentNavItem
		if ( ! navItem ) {
			return
		}
		const navKey = navItem.getAttribute('data-wizard-target')

		navItem.classList.add('active')
		this.stepper
			.querySelectorAll(`[data-wizard-target]:not([data-wizard-target="${navKey}"])`)
			.forEach( el => {
				el.classList.remove('active')
			})
		return this
	}

	static navigate(el) {
		const wizard = Wizard.findByElement(el)
		const action = el.getAttribute('data-wizard-action')
		if ( 'goto' === action ) {
			wizard.goto( el.getAttribute('data-wizard-target') )
		} else if ( 'forward' === action ) {
			wizard.currentIndex += parseInt( el.getAttribute('data-wizard-steps') )
		} else if ( 'back' === action ) {
			wizard.currentIndex -= parseInt( el.getAttribute('data-wizard-steps') )
		}
	}
}

document.addEventListener('click', e => {

	const btn = e.target.closest('[data-wizard-action]')
	if ( btn ) {
		Wizard.navigate(btn)
	}
})

module.exports = Wizard
//
//
//
// const wizards = []
//
// const findWizardByField = field => {
// 	let wizard = wizards.find( w => field.$el.parent().get(0) === w.parent.get(0) )
// 	if ( undefined === wizard ) {
// 		wizard = {
// 			parent: field.$el.parent(),
// 			fields: [],
// 			$stepper: false,
// 			$navItem: false
// 		}
// 		wizards.push(wizard)
// 	}
// 	return wizard
// }
//
// const findWizardByElement = el => {
// 	const parent = el.closest('[data-wizard-container="1"]')
// 	return wizards.find( w => parent === w.parent.get(0) )
// }
//
//
// //
// // const wizardManager = new acf.Model({
// // 	addPage: function( field ) {
// // 		const wizard = findWizardByField( field )
// // 		const isInitial = ! wizard.$stepper
// // 		let $stepper
// //
// // 		if ( isInitial ) {
// // 			wizard.$stepper = $('<div class="acf-wizard-stepper" />')
// // 			wizard.$stepper
// // 				.insertBefore( field.$el )
// // 			wizard.parent
// // 				.attr( 'data-wizard-container', 1 )
// // 		}
// // 		const idx = wizard.parent.find('.acf-wizard').length
// // 		// field.$el.attr('data-wizard-index',idx )
// // 		wizard.$navItem = this.createNavItem( field, wizard.$stepper.find('.acf-wizard-nav-item').length + 1 )
// //
// // 		if ( wizard.$navItem ) {
// // 			wizard.$stepper.append( wizard.$navItem )
// // 		}
// // 		wizard.fields.push( field )
// //
// // 		if ( isInitial ) {
// // 			this.goto( field.get('key') )
// // 		}
// // 		if ( field.$el.is('.acf-field-wizard-step[data-conditions]:not([data-wizard-nav="none"])') ) {
// // 			hiddenObserver.observe( field.$el.get(0), { attributes: true } )
// // 		}
// //
// // 		return wizard
// // 	},
// // 	createNavItem: function( field, num ) {
// // 		const navType = field.$el.attr('data-wizard-nav')
// // 		const fieldKey = field.get('key')
// // 		let name = ''
// // 		let className = ''
// // 		let disabled = ''
// // 		let $btn;
// // 		if ( 'none' === navType ) {
// // 			return false
// // 		}
// // 		if ( navType.includes('name') ) {
// // 			name = `<span class="acf-wizard-nav-item-name">${field.$el.find('.acf-label').text()}</span>`;
// // 		}
// // 		if ( navType.includes('number') ) {
// // 			className = '-numbered';
// // 		}
// // 		$btn = $( `<button type="button" class="acf-wizard-nav-item ${className}" data-wizard-action="goto" data-wizard-target="${fieldKey}">${name}</button>`)
// // 		if ( field.$el.is('.acf-hidden') ) {
// // 			$btn.prop('disabled',true)
// // 		}
// // 		return $btn
// // 	},
// // 	goto: function( fieldKey ) {
// // 		// $(`[data-key="${fieldKey}"]`)
// // 		// 	.addClass('active')
// // 		// 	.parent()
// // 		// 	.find(`.acf-field-wizard-step:not([data-key="${fieldKey}"])`)
// // 		// 	.removeClass('active')
// // 		// $(`[data-wizard-target="${fieldKey}"]`)
// // 		// 	.addClass('active')
// // 		// 	.parent()
// // 		// 	.find(`[data-wizard-target]:not([data-wizard-target="${fieldKey}"])`)
// // 		// 	.removeClass('active')
// // 	}
// // })
//
// $(document)
// 	.on('click','button[data-wizard-action="goto"]', e => {
// 		e.preventDefault()
// 		wizardManager.goto( $(e.currentTarget).attr('data-wizard-target') )
// 	})
// 	.on('click','button[data-wizard-action="forward"]', e => {
// 		const steps = parseInt( $(e.currentTarget).attr('data-wizard-steps') )
// 		e.preventDefault()
// 		findWizardByElement(e.currentTarget).fields.indexOf()
//
// 	})
// 	.on('click','button[data-wizard-action="back"]', e => {
// 		const steps = parseInt( $(e.currentTarget).attr('data-wizard-steps') ) * -1
// 		wizardManager.stepBy( $(e.currentTarget).attr('data-wizard-steps') * -1 )
// 	})
//
//
//
// // module.exports = wizardManager;
