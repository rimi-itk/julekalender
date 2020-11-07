import './Julekalender.scss'
import { Modal } from 'react-bootstrap'
import Scene from './component/Scene'
import React, { useState } from 'react'

function Julekalender ({ scenes = [] }) {
  const [content, setContent] = useState(null)

  return (
    <>
      <div className='julekalender'>
        {scenes && scenes.map((scene, index) => (
          <Scene key={`scene-${index}`} setContent={setContent} index={index} {...scene} />
        ))}
      </div>

      <Modal size='xl' centered show={content !== null} onHide={() => setContent(null)}>
        <Modal.Header closeButton>
          {content?.header &&
            <Modal.Title>{content.header}</Modal.Title>}
        </Modal.Header>

        <Modal.Body>
          {content?.body &&
            <div dangerouslySetInnerHTML={{ __html: content.body }} />}
        </Modal.Body>
      </Modal>
    </>
  )
}

const resize = e => {
  const app = document.querySelector('.App')
  if (app) {
    const scaleX = window.innerWidth / app.offsetWidth
    const scaleY = window.innerHeight / app.offsetHeight
    const scale = Math.min(scaleX, scaleY)
    // const scale = 0.5

    console.log(scaleX, scaleY, scale)

    app.style.transform = `scale(${scale})`
    // app.style.transformOrigin = `left -${app.offsetHeight/2}px`
    app.style.transformOrigin = window.innerWidth / window.innerHeight < app.offsetWidth / app.offsetHeight
      ? `left ${scale * window.innerHeight - scaleY * app.offsetHeight}px`
      : `${window.innerWidth - scaleY * app.offsetWidth}px top`
    //
    // document.body.style.width =
    app.parentNode.style.width = `${scale * app.offsetWidth}px`
    // document.body.style.height =
    app.parentNode.style.height = `${scale * app.offsetHeight}px`
    document.body.style.overflow = 'hidden'
  }
}

window.addEventListener('resize', resize)
window.addEventListener('load', resize)
// resize()

export default Julekalender
