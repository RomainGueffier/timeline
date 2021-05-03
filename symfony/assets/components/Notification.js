/**
 * Class to use easyly Bootstrap Toast
 * @RomainGueffier
 */

const bootstrap = require('bootstrap')

export default class BootstrapNotification {

    constructor() {
        this.model = document.getElementById('toast-model')
        this.container = document.getElementById('toasts-container')
    }

    /**
     * show a new notification with Bootstrap Toast
     * @param {String} message 
     * @param {Object} options :
     *    @property {Integer} delay default 5s (in ms)
     *    @property {String} style bootstrap default styles (danger, warning, light, dark, etc..)
     */
    new(message, options) {

        const delay = options.delay || 5000
        const style = options.style || 'default'

        // cloning default toast model
        let newToast = this.model.cloneNode(true)
        newToast.removeAttribute('id')
        newToast.classList.remove('d-none')

        // add message
        newToast.querySelector('.toast-body').innerHTML = message
        // append to toasts container
        this.container.insertBefore(newToast, this.model)
        // if set, add special styles
        if (style !== 'default') {
            let className = 'bg-' + style
            newToast.querySelector('.toast-header').classList.add(className, 'text-light')
        }

        // init toast
        const toastInstance = new bootstrap.Toast(newToast, {'delay': delay})
        // show container and fire Toast
        this.container.classList.remove('d-none')
        toastInstance.show()

        // after timeout also delete html clone
        setTimeout(() => {
            toastInstance.dispose()
            this.container.removeChild(newToast)
            this.container.classList.add('d-none')
        }, delay + 1000)
    }

}