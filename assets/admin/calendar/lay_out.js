import './lay_out.scss'

const scenes = document.querySelectorAll('.scene')

const getCropBoxes = () => {
  return JSON.parse(document.getElementById('form_cropBoxes').value)
}

const cropBoxesHistory = [getCropBoxes()]

const setCropBoxes = (cropBoxes, noHistory = false) => {
  if (!noHistory) {
    cropBoxesHistory.push(cropBoxes)
  }
  console.log({ cropBoxesHistory })
  document.getElementById('form_cropBoxes').value = JSON.stringify(cropBoxes)
  showScenes()
}

const undo = document.getElementById('undo')

undo.addEventListener('click', event => {
  if (cropBoxesHistory.length > 1) {
    console.log(cropBoxesHistory)
    cropBoxesHistory.pop()
    console.log(cropBoxesHistory)
    setCropBoxes(cropBoxesHistory[cropBoxesHistory.length - 1], true)
  }
})

// @see https://stackoverflow.com/a/2450976
const shuffle = (array) => {
  let currentIndex = array.length; let temporaryValue; let randomIndex

  // While there remain elements to shuffle...
  while (currentIndex !== 0) {
    // Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex)
    currentIndex -= 1

    // And swap it with the current element.
    temporaryValue = array[currentIndex]
    array[currentIndex] = array[randomIndex]
    array[randomIndex] = temporaryValue
  }

  return array
}

const showScenes = () => {
  const cropBoxes = getCropBoxes()
  scenes.forEach((scene, index) => {
    const cropBox = cropBoxes[index] ?? null
    if (cropBox) {
      scene.style.left = cropBox.left + 'px'
      scene.style.top = cropBox.top + 'px'
      scene.style.width = cropBox.width + 'px'
      scene.style.height = cropBox.height + 'px'
    }
  })

  undo.style.visibility = cropBoxesHistory.length > 1 ? 'visible' : 'hidden'
}

const layOut = () => {
  const columns = parseInt(document.getElementById('form_columns').value)
  const sceneWidth = parseInt(document.getElementById('form_width').value)
  const sceneHeight = parseInt(document.getElementById('form_height').value)
  const [imageWidth, imageHeight] = [document.getElementById('scenes').offsetWidth, document.getElementById('scenes').offsetHeight]

  const rows = Math.ceil(scenes.length / columns)

  const horizontalGap = Math.round((imageWidth - columns * sceneWidth) / (columns + 1))
  const horizontalOffset = horizontalGap
  const verticalGap = Math.round((imageHeight - rows * sceneHeight) / (rows + 1))
  const verticalOffset = verticalGap

  let cropBoxes = []
  for (let row = 0; row < rows; row++) {
    for (let column = 0; column < columns; column++) {
      cropBoxes.push({
        left: horizontalOffset + column * (sceneWidth + horizontalGap),
        top: verticalOffset + row * (sceneHeight + verticalGap),
        width: sceneWidth,
        height: sceneHeight
      })
    }
  }
  cropBoxes = cropBoxes.slice(0, scenes.length)
  if (document.getElementById('form_shuffle').checked) {
    shuffle(cropBoxes)
  }

  setCropBoxes(cropBoxes)
}

window.addEventListener('load', showScenes)

document.getElementById('form_layOut').addEventListener('click', layOut)

scenes.forEach(scene => {
  const dragger = scene.querySelector('.label')
  dragger.setAttribute('draggable', 'true')
  dragger.addEventListener('dragstart', event => {
    event.dataTransfer.setData('start-drag', JSON.stringify({
      type: 'move',
      index: scene.dataset.index,
      clientX: event.clientX,
      clientY: event.clientY
    }))
  })

  const corners = scene.querySelectorAll('.corner')
  corners.forEach(corner => {
    corner.setAttribute('draggable', 'true')
    corner.addEventListener('dragstart', event => {
      event.dataTransfer.setData('start-drag', JSON.stringify({
        type: 'resize ' + corner.classList,
        index: scene.dataset.index,
        clientX: event.clientX,
        clientY: event.clientY
      }))
    })
  })
})

const container = document.getElementById('scenes')

container.addEventListener('dragover', event => {
  event.preventDefault()
})

container.addEventListener('drop', event => {
  if (event.dataTransfer.getData('start-drag')) {
    const {
      type,
      index,
      clientX: startClientX,
      clientY: startClientY
    } = JSON.parse(event.dataTransfer.getData('start-drag'))

    const cropBoxes = getCropBoxes()
    const cropBox = cropBoxes[index] ?? null
    if (cropBox) {
      const offset = [event.clientX - startClientX, event.clientY - startClientY]
      if (type.indexOf('move') === 0) {
        cropBox.left += offset[0]
        cropBox.top += offset[1]
        cropBoxes[index] = cropBox
      } else if (type.indexOf('resize') === 0) {
        if (type.indexOf('left') > -1) {
          cropBox.left += offset[0]
          cropBox.width -= offset[0]
        } else if (type.indexOf('right') > -1) {
          cropBox.width += offset[0]
        }
        if (type.indexOf('top') > -1) {
          cropBox.top += offset[1]
          cropBox.height -= offset[1]
        } else if (type.indexOf('bottom') > -1) {
          cropBox.height += offset[1]
        }
      }

      setCropBoxes(cropBoxes)
      showScenes()
    }
  }
})

document.querySelector('form').addEventListener('reset', event => { showScenes() })
