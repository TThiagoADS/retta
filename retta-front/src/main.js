import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@fortawesome/fontawesome-free/css/all.css'
import 'vuetify/styles'
import { fa } from 'vuetify/iconsets/fa'
import { aliases, mdi } from 'vuetify/iconsets/mdi'


const vuetify = createVuetify({
  components,
  directives,
   icons: {
    defaultSet: 'fa',
    aliases,
    sets: {
      fa,
      mdi,
    }
  },
})

const app = createApp(App)
app.use(router)
app.use(vuetify)
app.mount('#app')
