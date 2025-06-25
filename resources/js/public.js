import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

// Import direto da página
import Home from './Public/Pages/Home.vue'

createInertiaApp({
  resolve: (name) => {
    if (name === 'Home') return Home;
    return null;
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
