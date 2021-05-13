/*
 * Main JavaScript file! Include in all pages
 * @RomainGueffier
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

var $ = require("jquery") // !!! todo -> drop completely jquery
const bootstrap = require('bootstrap')

// form input tag style
require('selectize');
import '../node_modules/selectize/dist/css/selectize.bootstrap3.css';

import BootstrapModal from './components/Modal';
import BootstrapNotification from './components/Notification'
import Browser from './components/Browser'

export default class App {

    constructor() {
        
        this.modal = new BootstrapModal()
        this.notification = new BootstrapNotification()
        this.bootstrap = bootstrap

        // Super function to listen events even on dom ajax content loaded async
        // https://flaviocopes.com/javascript-event-delegation/
        this.on = (selector, eventType, childSelector, eventHandler) => {
            const elements = document.querySelectorAll(selector)
            if (elements.length > 0) {
                for (let element of elements) {
                    element.addEventListener(eventType, eventOnElement => {
                        if (eventOnElement.target.matches(childSelector)) {
                            eventHandler(eventOnElement)
                        }
                    })
                }
            }
        }
    }

    // ajax call for get/post with data and files
    call(url, options = {}, callback) {
        
        var headers = new Headers()
        var params = {
            method: options.method || 'get',
            headers: headers,
            mode: 'cors',
            cache: 'default'
        }

        var request = new Request(url, params)

        fetch(request, params)
            .then(response => {
                // Handle data async
                response.text().then(function(data) {
                    callback(data)
                })
                return true
            }).catch(error => {
                console.log(error)
            })
    }

    isJson(str) {
        if (typeof str !== 'string') return false
        try {
            const result = JSON.parse(str)
            const type = Object.prototype.toString.call(result)
            return type === '[object Object]' || type === '[object Array]'
        } catch (error) {
            return false
        }
    }

    /**
     * Make an element shake over a period of time
     * Use CSS keyframes class
     * @param {DOM Element} element 
     * @param {Integer} delay is ms
     */
    shake(element, delay = 500) {
        // start shake
        element.classList.add('shake')
        // stop after delay achieved
        setTimeout(() => {
            element.classList.remove('shake')
        }, delay)
    }

    initBootstrapTooltips() {
        // vanilla js enable tooltip everywhere
        // https://getbootstrap.com/docs/5.0/components/tooltips/#example-enable-tooltips-everywhere
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(element) {
            return bootstrap.Tooltip.getInstance(element) || new bootstrap.Tooltip(element)
        })
    }

    initBootstrapCollapse() {
        var collapseTriggerList = [].slice.call(document.querySelectorAll('.collapse'))
        var collapseList = collapseTriggerList.map(function(element) {
            return bootstrap.Collapse.getInstance(element) || new bootstrap.Tooltip(element)
        })
    }
}

const app = new App()

document.addEventListener("DOMContentLoaded", () => {
    console.log('app started')
    app.initBootstrapTooltips()

    const changelogEl = document.getElementById('changelog')
    let changelogModal = bootstrap.Modal.getInstance(changelogEl) || new bootstrap.Modal(changelogEl)

    const browser = new Browser()
    if (browser.isIE || browser.isSafari) {
        app.notification.new("L'application n'est pas optimisée pour Safari et Internet Explorer, nous recommandons Chrome ou Firefox.")
    }
    
})