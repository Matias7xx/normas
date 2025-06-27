import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: async (name) => {
    // Para Laravel Mix, usar require.context ou import dinâmico
    try {
      // Tentar importar dinamicamente
      const module = await import(`./Public/Pages/${name}.vue`)
      return module.default || module
    } catch (error) {
      console.warn(`Página "${name}" não encontrada:`, error)
      
      // Fallback para Home
      try {
        const homeModule = await import('./Public/Pages/Home.vue')
        return homeModule.default || homeModule
      } catch (homeError) {
        console.error('Erro ao carregar página Home:', homeError)
        
        // Fallback final
        return {
          template: `
            <div style="padding: 20px; text-align: center;">
              <h2>Página "${name}" não encontrada</h2>
              <p>Redirecionando para a página inicial...</p>
            </div>
          `,
          mounted() {
            setTimeout(() => {
              window.location.href = '/'
            }, 2000)
          }
        }
      }
    }
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
}).catch(err => {
  console.error('Erro crítico do Inertia:', err)
})