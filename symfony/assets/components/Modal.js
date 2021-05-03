/**
 * Tool to construct Bootstrap's Modal
 * @RomainGueffier
 */

const bootstrap = require('bootstrap')

export default class BootstrapModal {

    constructor () {
        this.alertHtml = `
            <div class="modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Es-tu vraiment sûr de vouloir supprimer ce contenu ? Cette action sera définitive !</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-submit" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        `
        this.confirmHtml = `
            <div class="modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Es-tu vraiment sûr de vouloir supprimer ce contenu ? Cette action sera définitive !</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary btn-submit">Confirmer</button>
                        </div>
                    </div>
                </div>
            </div>
        `
    }

    /**
     * Show a confirm modal with callback
     * @param {String} message 
     * @param {Object} options (title, button text, colors...)
     */
    alert(message, options = {}) {

        // create node element from model and add to dom
        let node = document.createElement('div') // is a node
        // unique id
        const id = Date.now()
        node.setAttribute('id', id)
        node.innerHTML = this.alertHtml
        document.body.appendChild(node);
        // get new dom element
        let html = document.getElementById(id).firstElementChild

        // new instance of modal
        let modal = new bootstrap.Modal(html)

        // fill content with message and other options
        html.querySelector('.modal-body').innerHTML = message

        // show modal
        modal.show()

        // attach event listener on button
        html.addEventListener('hidden.bs.modal', function(e) {
            // remove modal from dom
            html.remove()
        })
    }

    /**
     * Show a confirm modal with callback
     * @param {String} message 
     * @param {Object} options (title, button text, colors...)
     * @param {Function} onSubmitClickCallback 
     */
    confirm(message, options = {}, onSubmitClickCallback) {

        // create node element from model and add to dom
        let node = document.createElement('div') // is a node
        // unique id
        const id = Date.now()
        node.setAttribute('id', id)
        node.innerHTML = this.confirmHtml
        document.body.appendChild(node);
        // get new dom element
        let html = document.getElementById(id).firstElementChild

        // new instance of modal
        let modal = new bootstrap.Modal(html)

        // fill content with message and other options
        html.querySelector('.modal-body').innerHTML = message
        let submit = html.querySelector('.btn-submit')
        submit.innerHTML = options.submit || 'Confirmer'

        // show modal
        modal.show()

        // attach event listener on button
        submit.addEventListener('click', function(e) {
            // hide modal
            modal.hide()
            // remove modal from dom
            html.remove()
            // fire callback
            onSubmitClickCallback()
        })
    }
}