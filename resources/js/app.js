import Alpine from 'alpinejs'
import projectsDirectory from './projects'
import Sortable from 'sortablejs'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.min.css'
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js'
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css'
import moment from 'jalali-moment'
import './echo'
import './realtime'

window.Alpine = Alpine
window.Sortable = Sortable
window.flatpickr = flatpickr
window.moment = moment
Alpine.data('projectsDirectory', projectsDirectory)
Alpine.start()
