const scenes = document.querySelectorAll('.scene')

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
  const cropBoxes = JSON.parse(document.getElementById('form_cropBoxes').value)
  scenes.forEach((scene, index) => {
    const cropBox = cropBoxes[index] ?? null
    if (cropBox) {
      scene.style.left = cropBox.left + 'px'
      scene.style.top = cropBox.top + 'px'
      scene.style.width = cropBox.width + 'px'
      scene.style.height = cropBox.height + 'px'
    }
  })
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

  document.getElementById('form_cropBoxes').value = JSON.stringify(cropBoxes)
  showScenes()
}

window.addEventListener('load', showScenes)

document.getElementById('form_layOut').addEventListener('click', layOut)
