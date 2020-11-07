import './Scene.scss'
import React, { useState } from 'react'

const classNames = require('classnames')

// @see https://3dtransforms.desandro.com/card-flip
const Scene = ({ index, content, configuration, setContent }) => {
  const [isOpen, setIsOpen] = useState(false)
  const className = classNames({ scene: true, 'is-open': isOpen })
  const style = {
    left: configuration?.x,
    top: configuration?.y,
    width: configuration.width || '200px',
    height: configuration.height || '200px'
  }

  console.log(configuration, style)

  return (
    <div className={className} style={style}>
      <Content {...{ setContent, index, content }} />
      <div className='door' onClick={() => setIsOpen(true)}>
        <div className='door__face door__face--front'><div className='label'>{index + 1}</div></div>
        <div className='door__face door__face--back' />
      </div>
    </div>
  )
}

const Content = ({ setContent, index, content }) => (
  <>
    <div className='content' onClick={() => setContent({ header: index + 1, body: content })}>{content}</div>
  </>
)

export default Scene
