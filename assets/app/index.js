import React from 'react'
import ReactDOM from 'react-dom'
import App from './App'

const config = JSON.parse(document.getElementById('julekalender-config').innerText)

ReactDOM.render(
  <React.StrictMode>
    <App {...config} />
  </React.StrictMode>,
  document.getElementById('root')
)
