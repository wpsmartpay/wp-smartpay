// Only the Modal plugin is used on the frontend ($.modal('show'/'hide'),
// show.bs.modal / hidden.bs.modal events). Importing the full Bootstrap
// bundle adds ~500 KB of unused plugins (Carousel, Tooltip, Popover, etc.).
import 'bootstrap/js/dist/modal'
