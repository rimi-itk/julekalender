/* global MutationObserver */
import './calendar.scss'
import Cropper from 'cropperjs'

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

const cropBoxSelect = function () {
  const container = this.parentNode
  const config = JSON.parse(container.dataset.options)

  const sourceImage = document.querySelector(config.image_field_selector + ' img')
  if (!sourceImage) {
    return
  }

  container.classList.add('is-cropping')

  const image = document.createElement('img')
  image.src = sourceImage.src
  container.insertBefore(image, this)

  container.cropper = new Cropper(image, {
    viewMode: 1,
    ready (event) {
      try {
        const input = container.querySelector('input')
        console.log(input.value)
        this.cropper.setCropBoxData(JSON.parse(input.value))
        console.log(JSON.parse(input.value))
      } catch (e) {}
    }
  })

  container.cropImage = image
}

const cropBoxSave = function () {
  console.log('save')
  const container = this.parentNode.parentNode

  if (container.cropper) {
    const input = container.querySelector('input')
    const data = container.cropper.getCropBoxData()
    input.value = JSON.stringify({
      left: Math.round(data.left),
      top: Math.round(data.top),
      width: Math.round(data.width),
      height: Math.round(data.height)
    })
    console.log(input.value)
  }

  cropBoxCancel.apply(this)
}

const cropBoxCancel = function () {
  console.log('cancel')
  const container = this.parentNode.parentNode

  if (container.cropper) {
    container.cropper.destroy()
    delete container.cropper
  }
  if (container.cropImage) {
    container.cropImage.parentNode.removeChild(container.cropImage)
  }
  container.classList.remove('is-cropping')
}

document.querySelectorAll('.crop-box-box-select').forEach(el => el.addEventListener('click', cropBoxSelect))
document.querySelectorAll('.crop-box-box-save').forEach(el => el.addEventListener('click', cropBoxSave))
document.querySelectorAll('.crop-box-box-cancel').forEach(el => el.addEventListener('click', cropBoxCancel))
