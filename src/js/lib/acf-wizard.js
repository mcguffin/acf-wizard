
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

	get currentStep() {
		let cur = false
		this.parent
			.querySelectorAll('.acf-field-wizard-step')
			.forEach( el => cur = el.matches('.active') ? el : cur )
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
		let navKey
		this.parent
			.querySelector(`:scope > [data-key="${fieldKey}"]`)
			.classList.add('active')

		this.parent
			.querySelectorAll(`:scope > .acf-field-wizard-step:not([data-key="${fieldKey}"])`)
			.forEach( el => {
				el.classList.remove('active')
			})

		const navItem = this.currentNavItem
		const step = this.currentStep

		if ( navItem ) {
			navKey = navItem.getAttribute('data-wizard-target')

			navItem.classList.add('active')
			this.stepper
				.querySelectorAll(`[data-wizard-target]:not([data-wizard-target="${navKey}"])`)
				.forEach( el => {
					el.classList.remove('active')
				})
		}

		if ( step ) {
			this.parent.setAttribute('data-show-stepper', step.getAttribute('data-show-stepper') )
		}


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
