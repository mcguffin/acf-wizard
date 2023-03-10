
import { isElementInViewport } from 'viewport'

class Wizard extends EventTarget {
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

	/** @var int */
	stepCounter = 0

	/** @var Array */
	navigationElements = []

	/** @return NodeList */
	get steps() {
		return this.parent.querySelectorAll(':scope > .acf-field-wizard-step');
	}

	get isReady() {
		return this.stepCounter > 0 && this.steps.length === this.stepCounter
	}

	get currentStep() {
		let cur = false
		this.steps.forEach( el => cur = el.matches('.active') ? el : cur )
		return cur
	}

	get currentNavItem() {
		let navItem = false
		Array.from(this.steps).every( el => {
			const testNavItem = this.stepper.querySelector(`[data-wizard-target="${el.getAttribute('data-key')}"]`)
			if ( testNavItem ) {
				navItem = testNavItem
			}
			if ( el.matches('.active') ) {
				return false
			}
			return true
		})
		return navItem
	}

	get currentIndex() {
		let idx = -1
		this.steps.forEach( (el,i) => idx = el.matches('.active') ? i : idx )
		return idx
	}

	set currentIndex( idx ) {
		Array.from(this.steps).every( (el,i) => {
			if ( idx === i ) {
				this.goto(el.getAttribute('data-key'))
				return false;
			}
			return true;
		} )
	}

	/**
	 *	@param parent DOMNode
	 */
	constructor( parent ) {

		super()

		const idx = Wizard.#instances.length

		this.parent = parent
		this.stepper = document.createElement('div')

		this.parent.setAttribute('data-wizard-container',idx)
		this.stepper.classList.add('acf-wizard-stepper')
		this.parent.prepend( this.stepper )

		// disable navigation elements for hidden groups
		this.#observer = new MutationObserver( (mutations,observer) => {
			mutations.forEach( mutation => {

				const fieldKey = mutation.target.getAttribute('data-key')
				const isHidden = mutation.target.matches('.acf-hidden')

				// direct references
				document
					.querySelectorAll(`[data-wizard-action="goto"][data-wizard-target="${fieldKey}"][data-wizard-disable="1"]`)
					.forEach( el => {
						el.disabled = isHidden
					} )
			})
			this.dispatchEvent( new Event('acf_wizard/navigated') )
		})

		Wizard.#instances[idx] = this
	}

	destructor() {
		this.#observer.disconnect()
	}

	scrollToStepper() {
		if ( isElementInViewport( this.stepper ) ) {
			return;
		}
		this.stepper.scrollIntoView( { behavior: 'smooth' } )
	}

	/**
	 *	@param el DOMNode
	 *	@return Wizard
	 */
	add(el) {

		const navType = el.getAttribute('data-wizard-nav')

		this.stepCounter++

		if ( this.isReady ) {
			// todo: save in user prefs
			this.currentIndex = 0
		}

		if ( 'none' === navType ) {
			return false
		}

		// add navigation element
		const btn = document.createElement('button');
		const fieldKey = el.getAttribute('data-key')

		btn.type = 'button'
		btn.setAttribute('data-wizard-action','goto')
		btn.setAttribute('data-wizard-target', fieldKey )
		btn.setAttribute('data-wizard-disable', '1' )
		if ( navType.includes('name') ) {
			btn.classList.add('-named')
			btn.innerHTML = `<span class="acf-wizard-nav-item-name">${el.querySelector('.acf-label').innerHTML}</span>`
		}
		btn.classList.add('acf-wizard-nav-item')
		if ( navType.includes('number') ) {
			btn.classList.add('-numbered')
		}
		this.stepper.append(btn)

		if ( el.matches('.acf-field-wizard-step[data-conditions]:not([data-wizard-nav="none"])') ) {
			this.#observer.observe( el, { attributes: true } )
		}

		return this
	}

	/**
	 *	@param fieldKey String
	 *	@return Wizard
	 */
	goto(fieldKey) {
		let navKey

		const navigateEvent = new Event('acf_wizard/navigate', { cancelable: true })
		this.dispatchEvent( navigateEvent )

		if ( navigateEvent.defaultPrevented ) {
			return;
		}

		this.parent
			.querySelector(`:scope > [data-key="${fieldKey}"]`)
			.classList.add('active')

		this.parent
			.querySelectorAll(`:scope > .acf-field-wizard-step:not([data-key="${fieldKey}"])`)
			.forEach( el => {
				el.classList.remove('active')
			})

		this.updateIndex()

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

		this.dispatchEvent( new Event('acf_wizard/navigated') )

		this.scrollToStepper()

		return this
	}

	updateIndex() {
		this.parent.setAttribute('data-wizard-current-index', this.currentIndex )
	}

	canNavigateSteps(steps) {
		const wizardSteps = this.steps
		const idx = this.currentIndex
		const newIdx = Math.min(
			Math.max( 0, idx + steps),
			wizardSteps.length -1
		)
		return !! wizardSteps[newIdx] && wizardSteps[newIdx].matches(':not(.acf-hidden)')

	}

	canNavigate(fieldKey) {
		return this.parent.querySelector(`[data-key="${fieldKey}"]`).matches(':not(.acf-hidden)')
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
