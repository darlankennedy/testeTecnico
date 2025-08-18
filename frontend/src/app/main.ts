import '@mdi/font/css/materialdesignicons.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import BaseAlert from '@/shared/components/BaseAlert.vue'

import { VMaskInput } from 'vuetify/labs/VMaskInput'


// --- escolha inicial do tema (salvo ou sistema)
const THEME_KEY = 'APP_THEME'
const saved = (localStorage.getItem(THEME_KEY) as 'light' | 'dark' | null)
const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches
const defaultTheme = saved ?? (prefersDark ? 'dark' : 'light')

const vuetify = createVuetify({
  components:{
    ...components,
    VMaskInput
  },
  directives,
  icons: { defaultSet: 'mdi', aliases, sets: { mdi } },
  theme: {
    defaultTheme,
    themes: {
      light: {
        dark: false,
        colors: {
          background: '#FFFFFF',
          surface: '#FFFFFF',
          primary: '#1976D2',
        },
      },
      dark: {
        dark: true,
        colors: {
          background: '#121212',
          surface: '#1E1E1E',
          primary: '#86adfc',
        },
      },
    },
  },
})

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(vuetify)
app.component('BaseAlert', BaseAlert)
app.mount('#app')
