/**
 * Dashboard js file
 */

import App from './app'
import { characterForm } from './character_form'
import { eventForm } from './event_form'
import { categoryForm } from './category_form'

const bootstrap = require('bootstrap')

class Dashboard {

    constructor() {
        // used to avoid multiple ajax call on short period
        this.pending = false
        this.root = document.getElementById('root')
        // last dashboard urls stored here for back action
        this.referer = []
        this.backButton = document.getElementById('dashboard-back-link')
    }

    /**
     * Retrieve anchor from browser uri
     * @return {String} anchor
     */
    getUrlAnchor() {
        return (document.URL.split('#').length > 1) ? document.URL.split('#')[1] : null
    }

    /**
     * Set actual page link in dashboard navigation active
     * @param {DomElement} element 
     */
    setNavLinkActive(element) {  
        // reset all active links
        const navigation = document.getElementById('dashboard-nav')
        for (let element of navigation.querySelectorAll('li')) {
            element.querySelector('a').classList.remove('active')
        }

        // add active to param's element
        element.classList.add('active')
    }

    /**
     * Set Dashboard main page content title
     * @param {string} title 
     */
    setPageTitle(title) {
        document.getElementById('dashboard-nav-title').innerText = title
        this.clearSubPageTitle()
    }

    /**
     * Set Dashboard main page content sub title
     * @param {string} title 
     */
    setSubPageTitle(title) {
        document.getElementById('dashboard-nav-subtitle').innerHTML = ' <i class="fas fa-angle-right"></i> ' + title
    }

    clearSubPageTitle() {
        document.getElementById('dashboard-nav-subtitle').innerHTML = ''
    }

    typeToTitle(type) {
        switch (type) {
            case 'timeline':
                return 'Frises chronologiques'
                break
            case 'category':
                return 'Catégories'
                break
            case 'character':
                return 'Personnages'
                break
            case 'event':
                return 'Évènements'
                break
            default:
                return 'Accueil'
        }
    }

    typeToSentence(type) {
        switch (type) {
            case 'timeline':
                return 'la frise chronologique'
                break
            case 'category':
                return 'la catégorie'
                break
            case 'character':
                return 'le personnage'
                break
            case 'event':
                return 'l\'évènement'
                break
            case 'user':
                return 'ton compte'
                break
            default:
                return 'le contenu'
        }
    }

    /**
     * Call url async
     * @param {string} url
     * @param {object} options 
     */
    load(url, options = {}) {
    
        if (!self.pending) {
            self.pending = true

            // loading content
            self.root.innerHTML = `
                <div class="d-flex justify-content-center h-100">
                    <div class="spinner-border my-auto" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            `

            // call url
            var params = {
                method: options.method || 'get',
                headers: options.headers || new Headers()
            }

            if (params.method === 'post') {
                params.body = options.formData || null
            }

            var request = new Request(url, params)

            fetch(request, params)
                .then(response => {
                    // Handle data async
                    response.text().then(function(data) {
                        self.root.innerHTML = data
                    })
                    self.pending = false
                    return true
                }).catch(error => {
                    console.log(error)
                })
        }
    }
}

const app = new App()
const dashboard = new Dashboard()

// on page load, redirect to anchor link
window.addEventListener('load', function(event) {
    const anchor = dashboard.getUrlAnchor() || 'home'
    const link = document.getElementById(anchor)
    if (!!link) {
        link.dispatchEvent(new Event('click'))
    }  
})

dashboard.backButton.addEventListener('click', function(e) {
    e.stopPropagation()
    if (dashboard.referer.length > 1) {
        // load previous url
        let previous = dashboard.referer.splice(0,2)[1]
        dashboard.load(previous.href)
        dashboard.setPageTitle(previous.title)
        if (previous.subtitle) {
            dashboard.setSubPageTitle(previous.subtitle)
        }
        let navLink = document.querySelector('[data-title="' + previous.title +'"]')
        if (!!navLink) {
            dashboard.setNavLinkActive(navLink)
        }
    } else {
        document.getElementById('home').dispatchEvent(new Event('click'))
    }
})

//app.on('#dashboard-nav', 'click', 'a', function(e) {
document.querySelectorAll('.dashboard-link').forEach(element => {
    element.addEventListener('click', function(e) {
        e.preventDefault()
        e.stopPropagation()

        const href = e.target.getAttribute('href')
        const title = e.target.getAttribute('data-title')

        dashboard.setNavLinkActive(e.target)
        dashboard.setPageTitle(title)
        dashboard.load(href)
        // add load to dashboard history
        dashboard.referer.unshift({
            'href': href,
            'title': title
        })

        // for mobile, hide menu on button click
        let collapseMenu = document.getElementById('sidebarMenu')
        let collapseInstance = bootstrap.Collapse.getInstance(collapseMenu) || new bootstrap.Collapse(collapseMenu)
        if (collapseInstance) {
            collapseInstance.hide()
        }
        
        return false
    })
})

// to load js form files when needed, create an observer instance
// https://tr.javascript.info/mutation-observer
var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {

        // if child has a form, then load js dependencies form's
        const form = mutation.target.querySelector('form')
        if (!!form) {
            switch (form.getAttribute('name')) {
                case 'character':
                    characterForm()
                    break
                case 'event':
                    eventForm()
                    break
                case 'category':
                    categoryForm()
                    break
                default:
            }

            // catch form submit and add files or data to ajax call
            form.addEventListener('submit', function(e) {

                e.stopPropagation()
                e.preventDefault()
    
                const formData = new FormData(this)
                const url = $(this).attr('action')

                dashboard.load(url, {
                    method: 'post',
                    formData: formData
                })
    
                return false
            })
        }

        // init tooltips and collapse of changed dom
        app.initBootstrapTooltips()
        app.initBootstrapCollapse()

        // watch action buttons on entities lists
        const editButtons = mutation.target.querySelectorAll('.btn-edit')
        if (!!editButtons) {
            for (let button of editButtons) {
                button.addEventListener('click', function(e) {

                    e.preventDefault()
                    e.stopPropagation()

                    const href = e.target.getAttribute('href')
                    dashboard.load(href)

                    const title = dashboard.typeToTitle(e.target.getAttribute('data-content-type'))
                    const subtitle = '<i class="fas fa-edit"></i> ' + e.target.getAttribute('data-content-name')
                    dashboard.setSubPageTitle(subtitle)
                    dashboard.referer.unshift({
                        'href': href,
                        'title': title,
                        'subtitle': subtitle
                    })
                    
                    return false
                })
            }
        }
        
        const viewButtons = mutation.target.querySelectorAll('.btn-view')
        if (!!viewButtons) {
            for (let button of viewButtons) {
                button.addEventListener('click', function(e) {

                    e.preventDefault()
                    e.stopPropagation()

                    const href = e.target.getAttribute('href')
                    dashboard.load(href)

                    const title = dashboard.typeToTitle(e.target.getAttribute('data-content-type'))
                    const subtitle = '<i class="fas fa-eye"></i> ' + e.target.getAttribute('data-content-name')
                    dashboard.setSubPageTitle(subtitle)
                    dashboard.referer.unshift({
                        'href': href,
                        'title': title,
                        'subtitle': subtitle
                    })

                    return false
                })
            }
        }

        const deleteButtons = mutation.target.querySelectorAll('.btn-delete')
        if (!!deleteButtons) {
            for (let button of deleteButtons) {
                button.addEventListener('click', function(e) {

                    e.preventDefault()
                    e.stopPropagation()

                    const href = e.target.getAttribute('href')
                    const needRedirection = e.target.getAttribute('data-detailed-view')
                    const entityName = e.target.getAttribute('data-content-type')
                    const contentName = e.target.getAttribute('data-content-name')
                    const sentence = dashboard.typeToSentence(entityName)
                    const title = dashboard.typeToTitle(entityName)
                    const entityId = button.getAttribute('data-entity-id')
                    let entityWrapper = dashboard.root.querySelector('#' + entityName + '-wrapper-' + entityId)
                    
                    // show confirmation modal
                    app.modal.confirm('Es-tu sûr de supprimer ' + sentence + ' ' + contentName + ' ? Cette action est définitive !', 'Ok', function(e) {
                        app.call(href, {}, function(response) {
                            if (app.isJson(response)) {
                                const data = JSON.parse(response)

                                if (data.error === false) {
                                    // if delete action in list, then remove entity from dom
                                    if (!needRedirection && !!entityWrapper) {
                                        entityWrapper.remove()
                                    }
                                    // if user delete account redirect to home
                                    else if (entityName === 'user') {
                                        window.location.href = '/'
                                    }
                                    // else redirect to list (delete in detailed view)
                                    else {
                                        let navLink = document.querySelector('[data-title="' + title +'"]')
                                        if (!!navLink) {
                                            dashboard.dispatchEvent(new Event('click'))
                                        }
                                    }
                                } else {
                                    if (!needRedirection && !!entityWrapper) { app.shake(entityWrapper) }
                                }
                                
                                // notificate removal
                                app.notification.new(data.message, {
                                    'style': data.error ? 'danger' : 'success'
                                })
                            } else {
                                if (!needRedirection && !!entityWrapper) { app.shake(entityWrapper) }
                                app.notification.new('Une erreur s\'est produite, impossible de confirmer si la suppression a bien été effectuée.', {
                                    'style': 'warning'
                                })
                            }
                        })
                    })
                    
                    return false
                })
            }
        }
    })
})

// pass options in the target node, as well as the observer options
observer.observe(dashboard.root, { childList: true })