import './Scene.scss'
import React from 'react'

const classNames = require('classnames')

// @see https://3dtransforms.desandro.com/card-flip
const Scene = ({ index, id, content, configuration, openDoor, displayContent, openedAt, doNotOpenUntil }) => {
  const className = classNames({
    scene: true,
    'is-open': openedAt !== null,
    'is-locked': doNotOpenUntil !== null && new Date(doNotOpenUntil) > new Date()
  })

  return (
    <div className={className}>
      <Content {...{ displayContent, index, content }} />
      <div className='door' onClick={() => openDoor(id)}>
        <div className='door__face door__face--front'><div className='label'>{index + 1}</div></div>
        <div className='door__face door__face--back' />
      </div>
    </div>
  )
}

const Content = ({ displayContent, index, content }) => (
  <>
    <div className='content' onClick={() => displayContent({ header: index + 1, body: content })} dangerouslySetInnerHTML={{ __html: content }} />
  </>
)

export default Scene
