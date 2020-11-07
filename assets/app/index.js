import React from 'react'
import ReactDOM from 'react-dom'
import Calendar from './Calendar'

const config = JSON.parse(document.getElementById('calendar-config').innerText)

ReactDOM.render(
  <React.StrictMode>
    <Calendar {...config} />
  </React.StrictMode>,
  document.getElementById('root')
)
