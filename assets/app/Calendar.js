/* global fetch */
import './Calendar.scss'

import Scene from './component/Scene'
import React, { useEffect, useState } from 'react'
import Alert from 'react-bootstrap/Alert'
import Modal from 'react-bootstrap/Modal'

function Calendar ({ data_url: dataUrl, scene_open_url_template: sceneOpenUrlTemplate }) {
  const [isLoaded, setIsLoaded] = useState(false)
  const [error, setError] = useState(null)
  const [scenes, setScenes] = useState(null)
  const [content, displayContent] = useState(null)

  const openDoor = id => {
    const url = sceneOpenUrlTemplate.replace('{{ id }}', id)
    fetch(url, { method: 'PATCH' })
      .then(res => res.json())
      .then(
        (result) => {
          if (result.data) {
            setScenes(result.data)
          } else if (result.errors) {
            // @see https://jsonapi.org/examples/#error-objects
            const error = result.errors[0]
            displayContent({
              header: error.title,
              body: error?.detail
            })
          }
        },
        (error) => console.log(error)
      )
  }

  useEffect(() => {
    fetch(dataUrl)
      .then(res => res.json())
      .then(
        (result) => {
          setIsLoaded(true)
          setScenes(result.data)
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          setIsLoaded(true)
          setError(error)
        }
      )
  }, [])

  let display = null
  if (error) {
    display = <Alert variant='danger'>{error.message}</Alert>
  } else if (!isLoaded) {
    display = <Alert variant='info'>Loading...</Alert>
  } else {
    display = (
      <>
        {scenes &&
          <div className='scenes'>
            {scenes.map((scene, index) => (
              <Scene key={`scene-${index}`} {...{ openDoor, displayContent, index }} {...scene} />
            ))}
          </div>}

        <Modal size='xl' centered show={content !== null} onHide={() => displayContent(null)}>
          <Modal.Header closeButton>
            {content?.header &&
              <Modal.Title>{content.header}</Modal.Title>}
          </Modal.Header>

          {content?.body &&
            <Modal.Body>
              <div dangerouslySetInnerHTML={{ __html: content.body }} />
            </Modal.Body>}
        </Modal>
      </>
    )
  }

  return <div className='calendar'>{display}</div>
}

const resize = e => {
  const app = document.querySelector('.calendar')
  if (app) {
    const scaleX = window.innerWidth / app.offsetWidth
    const scaleY = window.innerHeight / app.offsetHeight
    const scale = Math.min(scaleX, scaleY)

    console.log(scaleX, scaleY, scale)

    app.style.transform = `scale(${scale})`
    // app.style.transformOrigin = `left -${app.offsetHeight/2}px`
    app.style.transformOrigin = window.innerWidth / window.innerHeight < app.offsetWidth / app.offsetHeight
      ? `left ${scale * window.innerHeight - scaleY * app.offsetHeight}px`
      : `${window.innerWidth - scaleY * app.offsetWidth}px top`

    app.parentNode.style.width = `${scale * app.offsetWidth}px`
    app.parentNode.style.height = `${scale * app.offsetHeight}px`
    document.body.style.overflow = 'hidden'
  }
}

window.addEventListener('resize', resize)
window.addEventListener('load', resize)

export default Calendar
