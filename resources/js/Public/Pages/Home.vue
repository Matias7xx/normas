<template>
  <PublicLayout :stats="stats">
    <Head title="Início" />

    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="mb-8">
          <img
            src="/images/brasao_pcpb.png"
            alt="Brasão PCPB"
            class="h-21 w-20 mx-auto mb-6"
          />
          <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
            Biblioteca de <span class="text-[#c1a85a]">Normas</span>
          </h1>
          <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-8">
            Sistema de consulta de normas da Polícia Civil da Paraíba
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <Link
              href="/consulta"
              class="bg-[#c1a85a] hover:bg-[#a8914a] text-gray-900 px-8 py-4 rounded-lg font-bold text-lg transition-all duration-300 hover:scale-105 shadow-lg flex items-center"
            >
              <i class="fas fa-search mr-3"></i>
              Consultar Normas
            </Link>

            <button
              @click="scrollToStats"
              class="bg-transparent border-2 border-white hover:bg-white hover:text-blue-800 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 flex items-center"
            >
              <i class="fas fa-chart-bar mr-3"></i>
              Estatísticas
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Estatísticas -->
    <section ref="statsSection" class="py-16 bg-gradient-to-r from-gray-50 to-gray-100">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Números do Sistema
          </h2>
          <p class="text-xl text-gray-600">
            Dados atualizados sobre as normas disponíveis
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
            <div class="text-4xl font-bold text-blue-600 mb-2">
              {{ animatedStats.total_normas }}
            </div>
            <div class="text-gray-600 font-medium">Total de Normas</div>
            <div class="text-sm text-gray-500 mt-1">Cadastradas no sistema</div>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
            <div class="text-4xl font-bold text-blue-600 mb-2">
              {{ animatedStats.normas_vigentes }}
            </div>
            <div class="text-gray-600 font-medium">Normas Vigentes</div>
            <div class="text-sm text-gray-500 mt-1">Atualmente em vigor</div>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
            <div class="text-4xl font-bold text-blue-600 mb-2">
              {{ animatedStats.tipos_count }}
            </div>
            <div class="text-gray-600 font-medium">Tipos de Normas</div>
            <div class="text-sm text-gray-500 mt-1">Categorias disponíveis</div>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-300">
            <div class="text-4xl font-bold text-blue-600 mb-2">
              {{ animatedStats.orgaos_count }}
            </div>
            <div class="text-gray-600 font-medium">Órgãos</div>
            <div class="text-sm text-gray-500 mt-1">Órgãos específicos</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pesquisa Rápida -->
    <section class="py-16">
      <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Consulta Rápida
          </h2>
          <p class="text-lg text-gray-600">
            Encontre rapidamente a norma que você precisa
          </p>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-8">
          <form @submit.prevent="realizarBuscaRapida" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
              <input
                v-model="buscaRapida"
                type="text"
                placeholder="Digite o termo de consulta (ex: portaria, regulamento, procedimento...)"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
              />
              <button
                type="submit"
                :disabled="!buscaRapida.trim() || carregandoBusca"
                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center justify-center min-w-[140px]"
              >
                <i v-if="carregandoBusca" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-search mr-2"></i>
                {{ carregandoBusca ? 'Consultando...' : 'Consultar' }}
              </button>
            </div>
          </form>

          <div class="mt-6 flex flex-wrap gap-2 justify-center">
            <span class="text-sm text-gray-600 mr-2">Sugestões:</span>
            <button
              v-for="sugestao in sugestoesBusca"
              :key="sugestao"
              @click="buscaRapida = sugestao; realizarBuscaRapida()"
              class="bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 text-gray-700 px-3 py-1 rounded-full text-sm transition-all duration-300"
            >
              {{ sugestao }}
            </button>
          </div>
        </div>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_normas: 0,
      normas_vigentes: 0,
      tipos_count: 0,
      orgaos_count: 0
    })
  }
})

// State
const buscaRapida = ref('')
const carregandoBusca = ref(false)
const statsSection = ref(null)
const animatedStats = ref({
  total_normas: 0,
  normas_vigentes: 0,
  tipos_count: 0,
  orgaos_count: 0
})

const sugestoesBusca = [
  'portaria',
  'cvli',
  'instrução normativa',
  'deam',
  'edital',
  'sesds',
  'superintendência'
]

const realizarBuscaRapida = () => {
  if (!buscaRapida.value.trim()) return

  carregandoBusca.value = true

  router.visit('/consulta', {
    data: { search_term: buscaRapida.value },
    onFinish: () => {
      carregandoBusca.value = false
    }
  })
}

const scrollToStats = () => {
  if (statsSection.value) {
    statsSection.value.scrollIntoView({ behavior: 'smooth' })
  }
}

const animateStats = () => {
  const duration = 2000 // 2 segundos
  const steps = 60
  const stepDuration = duration / steps

  const targets = {
    total_normas: props.stats.total_normas || 0,
    normas_vigentes: props.stats.normas_vigentes || 0,
    tipos_count: props.stats.tipos_count || 0,
    orgaos_count: props.stats.orgaos_count || 0
  }

  let step = 0

  const animate = () => {
    step++
    const progress = step / steps
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)

    animatedStats.value = {
      total_normas: Math.floor(targets.total_normas * easeOutQuart),
      normas_vigentes: Math.floor(targets.normas_vigentes * easeOutQuart),
      tipos_count: Math.floor(targets.tipos_count * easeOutQuart),
      orgaos_count: Math.floor(targets.orgaos_count * easeOutQuart)
    }

    if (step < steps) {
      setTimeout(animate, stepDuration)
    } else {
      animatedStats.value = targets
    }
  }

  animate()
}

// Lifecycle
onMounted(() => {
  // Animar estatísticas quando a página carregar
  setTimeout(animateStats, 500)
})
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
