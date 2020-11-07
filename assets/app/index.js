import React from 'react'
import ReactDOM from 'react-dom'
import Julekalender from './Julekalender'

const config = JSON.parse(document.getElementById('julekalender-config').innerText)

ReactDOM.render(
  <React.StrictMode>
    <Julekalender {...config} />
  </React.StrictMode>,
  document.getElementById('root')
)
