/* global MutationObserver */
import './julekalender.scss'

// @see https://github.com/EasyCorp/EasyAdminBundle/issues/3645#issuecomment-704799541
const dragula = require('dragula')

// https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver
const observeCollection = (collection) => {
  // Select the node that will be observed for mutations
  const targetNode = collection.querySelector(':scope > div.form-widget > div.form-widget-compound > div[data-empty-collection]')
  if (targetNode) {
    dragula([targetNode])
    // Options for the observer (which mutations to observe)
    const config = { childList: true }

    // Callback function to execute when mutations are observed
    const callback = function (mutationsList, observer) {
      for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
          mutation.target.querySelectorAll('input[name*="[position]"]').forEach((el, i) => {
            el.value = i
          })
        }
      }
    }

    // Create an observer instance linked to the callback function
    const observer = new MutationObserver(callback)

    // Start observing the target node for configured mutations
    observer.observe(targetNode, config)
  }
}

document.querySelectorAll('div.field-collection').forEach(el => observeCollection(el))
