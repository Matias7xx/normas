<template>
  <PublicLayout :stats="pageStats">
    <Head title="Boletim Interno" />
    
    <!-- Header da página -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            Boletim Interno
          </h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Pesquise boletins por data de publicação ou número do Boletim
          </p>
          <div v-if="mostrandoMesAtual" class="mt-4 bg-blue-600 bg-opacity-20 rounded-lg p-3 inline-block">
            <i class="fas fa-calendar-alt mr-2"></i>
            Exibindo boletins de {{ formatarMesAno(mesAtual) }}
          </div>
        </div>
      </div>
    </section>

    <!-- Formulário de Busca -->
    <section class="bg-white shadow-lg -mt-6 relative z-10">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <form @submit.prevent="buscarBoletins" class="space-y-6">
          <!-- Busca por termo (Nº do Boletim) -->
          <div class="mb-6">
            <label for="search_term" class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-search mr-2 text-blue-600"></i>
              Termo de busca (número do boletim)
            </label>
            <input
              id="search_term"
              v-model="form.search_term"
              type="text"
              placeholder="Ex: BSPC Nº 2158, 2160..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
            />
          </div>

          <!-- Filtros de data aprimorados -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Data exata -->
            <div>
              <label for="data_publicacao" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                Data exata de publicação
              </label>
              <input
                id="data_publicacao"
                v-model="form.data_publicacao"
                type="date"
                :max="dataMaxima"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <!-- Mês/Ano -->
            <div>
              <label for="mes_ano" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Mês/Ano de publicação
              </label>
              <input
                id="mes_ano"
                v-model="form.mes_ano"
                type="month"
                :max="mesMaximo"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="flex flex-wrap gap-3 justify-center">
            <button
              type="submit"
              :disabled="carregando"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center"
            >
              <i v-if="carregando" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-search mr-2"></i>
              {{ carregando ? 'Buscando...' : 'Buscar Boletins' }}
            </button>
            
            <button
              type="button"
              @click="limparFiltros"
              class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 hover:scale-105 shadow-md flex items-center"
            >
              <i class="fas fa-times mr-2"></i>
              Limpar Filtros
            </button>

          </div>
        </form>
      </div>
    </section>

    <!-- Resultados -->
    <section class="max-w-7xl mx-auto px-4 py-8">
      <!-- Estatísticas da busca -->
      <div v-if="boletins?.data && Array.isArray(boletins.data)" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center justify-between flex-wrap">
            <div class="text-blue-800">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>{{ boletins?.total || 0 }}</strong> encontrado(s)
              <span v-if="temFiltrosAtivos" class="ml-2 text-sm">
                (com filtros aplicados)
              </span>
              <span v-else-if="mostrandoMesAtual" class="ml-2 text-sm">
                (do mês atual)
              </span>
            </div>
            <div class="text-sm text-blue-600">
              Página {{ boletins?.current_page || 1 }} de {{ boletins?.last_page || 1 }}
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de boletins -->
      <div v-if="boletins?.data && Array.isArray(boletins.data) && boletins.data.length > 0" class="space-y-4">
        <div
          v-for="boletim in boletins.data"
          :key="boletim?.id || 'boletim-' + Math.random()"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                  <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                  {{ boletim?.nome || 'Boletim Informativo' }}
                </h3>
                <p v-if="boletim?.descricao" class="text-gray-700 mb-3 leading-relaxed">
                  {{ boletim.descricao }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
              <div v-if="boletim?.data_publicacao">
                <i class="fas fa-calendar mr-1 text-blue-600"></i>
                Data de Publicação: {{ formatarDataPublicacao(boletim.data_publicacao) }}
              </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
              <div class="flex space-x-3">
                <button
                  v-if="boletim?.arquivo"
                  @click="visualizarPDF(boletim?.id)"
                  class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:shadow-md text-sm"
                >
                  <i class="fas fa-eye"></i>
                  Visualizar
                </button>

                <button
                  v-if="boletim?.arquivo"
                  @click="baixarBoletim(boletim?.id, boletim?.nome)"
                  :disabled="baixandoId === boletim?.id"
                  class="bg-gradient-to-r from-[#c1a85a] to-[#b39c4f] hover:from-[#a8914a] hover:to-[#9b853f] disabled:from-gray-400 disabled:to-gray-500 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:shadow-md disabled:cursor-not-allowed text-sm"
                >
                  <i class="fas fa-download" :class="{ 'animate-spin': baixandoId === boletim?.id }"></i>
                  {{ baixandoId === boletim?.id ? 'Baixando...' : 'Baixar' }}
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <!-- <div v-if="boletins?.last_page && boletins.last_page > 1" class="mt-8">
        <nav class="flex items-center justify-center space-x-2">
          <button
            v-if="boletins.current_page > 1"
            @click="irParaPagina(boletins.current_page - 1)"
            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg transition-all duration-300"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          
          <template v-for="pagina in paginasVisiveis" :key="pagina">
            <button
              v-if="pagina === '...'"
              disabled
              class="bg-white border border-gray-300 text-gray-400 px-3 py-2 rounded-lg cursor-not-allowed"
            >
              ...
            </button>
            <button
              v-else
              @click="irParaPagina(pagina)"
              :class="pagina === boletins.current_page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
              class="px-3 py-2 rounded-lg transition-all duration-300"
            >
              {{ pagina }}
            </button>
          </template>
          
          <button
            v-if="boletins.current_page < boletins.last_page"
            @click="irParaPagina(boletins.current_page + 1)"
            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg transition-all duration-300"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </nav>
      </div> -->

      <!-- Estado vazio -->
      <div v-else-if="boletins?.data && Array.isArray(boletins.data) && boletins.data.length === 0" class="text-center py-12">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-8">
          <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Nenhum boletim encontrado
          </h3>
          <p class="text-gray-600 mb-6">
            <span v-if="mostrandoMesAtual">
              Não há boletins publicados no mês atual.
            </span>
            <span v-else>
              Tente ajustar os filtros.
            </span>
          </p>
        </div>
      </div>

      <!-- Estado inicial -->
      <div v-else class="text-center py-12">
        <div class="p-8">
          <p class="text-gray-600 mb-4">
            Use os filtros acima para encontrar boletins específicos ou visualize os boletins do mês atual.
          </p>
          <button
            @click="mostrarMesAtual"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300"
          >
            <i class="fas fa-calendar-check mr-2"></i>
            Ver Boletins do Mês Atual
          </button>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'

const props = defineProps({
  boletins: {
    type: Object,
    default: () => null
  },
  filtros: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  mostrandoMesAtual: {
    type: Boolean,
    default: false
  },
  mesAtual: {
    type: String,
    default: () => new Date().toISOString().slice(0, 7) // YYYY-MM
  }
})

// Estado do componente
const carregando = ref(false)
const baixandoId = ref(null)

// Formulário de busca
const form = ref({
  search_term: props.filtros?.search_term || '',
  data_publicacao: props.filtros?.data_publicacao || '',
  mes_ano: props.filtros?.mes_ano || ''
})

const dataMaxima = computed(() => {
  // Data atual no formato YYYY-MM-DD
  const hoje = new Date()
  return hoje.toISOString().split('T')[0]
})

const mesMaximo = computed(() => {
  // Mês atual no formato YYYY-MM
  const hoje = new Date()
  return hoje.toISOString().slice(0, 7)
})

const temFiltrosAtivos = computed(() => {
  return !!(form.value.search_term || form.value.data_publicacao || form.value.mes_ano)
})

const pageStats = computed(() => ({
  usuarios_ativos: props.stats?.usuarios_ativos || 0,
  normas_cadastradas: props.stats?.normas_cadastradas || 0,
  boletins_encontrados: props.boletins?.total || 0
}))

const paginasVisiveis = computed(() => {
  if (!props.boletins || !props.boletins.current_page || !props.boletins.last_page) {
    return []
  }
  
  const current = props.boletins.current_page
  const last = props.boletins.last_page
  const pages = []
  
  if (current > 3) {
    pages.push(1)
    if (current > 4) pages.push('...')
  }
  
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  if (current < last - 2) {
    if (current < last - 3) pages.push('...')
    pages.push(last)
  }
  
  return pages
})

// Métodos
const buscarBoletins = () => {
  carregando.value = true
  
  // Limpar campo conflitante se preencheu o outro
  if (form.value.data_publicacao && form.value.mes_ano) {
    form.value.mes_ano = ''
  }
  
  const params = { ...form.value, busca: 1 }
  
  router.get('/boletins', params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      carregando.value = false
    },
    onError: (errors) => {
      console.error('Erro na busca:', errors)
      carregando.value = false
    }
  })
}

const mostrarMesAtual = () => {
  carregando.value = true
  
  form.value = {
    search_term: '',
    data_publicacao: '',
    mes_ano: props.mesAtual
  }
  
  const params = { mes_ano: props.mesAtual, busca: 1 }
  
  router.get('/boletins', params, {
    preserveState: true,
    onFinish: () => {
      carregando.value = false
    }
  })
}

const buscarTodosBoletins = () => {
  carregando.value = true
  
  // Limpar todos os filtros
  form.value = {
    search_term: '',
    data_publicacao: '',
    mes_ano: ''
  }
  
  router.get('/boletins', { busca: 1 }, {
    preserveState: true,
    onFinish: () => {
      carregando.value = false
    },
    onError: (errors) => {
      console.error('Erro na busca:', errors)
      carregando.value = false
    }
  })
}

const limparFiltros = () => {
  form.value = {
    search_term: '',
    data_publicacao: '',
    mes_ano: ''
  }
  
  router.get('/boletins', {}, {
    preserveState: true
  })
}

const formatarDataPublicacao = (data) => {
  if (!data) return 'Data não disponível'
  
  try {
    const dataObj = new Date(data)
    
    if (isNaN(dataObj.getTime())) {
      return data
    }
    
    return dataObj.toLocaleDateString('pt-BR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      timeZone: 'America/Sao_Paulo'
    })
  } catch (error) {
    console.error('Erro ao formatar data de publicação:', error)
    return data
  }
}

const formatarMesAno = (mesAno) => {
  if (!mesAno) return 'Mês atual'
  
  try {
    const [ano, mes] = mesAno.split('-')
    const meses = [
      'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
      'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ]
    
    return `${meses[parseInt(mes) - 1]} de ${ano}`
  } catch (error) {
    return mesAno
  }
}

const irParaPagina = (pagina) => {
  if (pagina === '...' || pagina === props.boletins?.current_page || !pagina) return
  
  const params = { ...form.value, page: pagina, busca: 1 }
  
  router.get('/boletins', params, {
    preserveState: true,
    preserveScroll: true
  })
}

const visualizarPDF = (id) => {
  if (!id) {
    console.error('ID do boletim não informado')
    return
  }
  
  window.open(`/boletim/view/${id}`, '_blank')
}

const baixarBoletim = async (id, nome) => {
  if (!id) {
    console.error('ID do boletim não informado')
    return
  }
  
  baixandoId.value = id
  
  try {
    const link = document.createElement('a')
    link.href = `/boletim/download/${id}`
    link.download = `${nome || 'boletim'}.pdf`
    link.target = '_blank'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    await new Promise(resolve => setTimeout(resolve, 1000))
    
  } catch (error) {
    console.error('Erro ao baixar boletim:', error)
    alert('Erro ao baixar o arquivo. Tente novamente.')
  } finally {
    baixandoId.value = null
  }
}

// Watchers
watch(() => props.filtros, (newFiltros) => {
  if (newFiltros && typeof newFiltros === 'object') {
    form.value = { 
      search_term: newFiltros.search_term || '',
      data_publicacao: newFiltros.data_publicacao || '',
      mes_ano: newFiltros.mes_ano || ''
    }
  }
}, { immediate: true })

// Limpar campo conflitante quando preencher outro
watch(() => form.value.data_publicacao, (newVal) => {
  if (newVal && form.value.mes_ano) {
    form.value.mes_ano = ''
  }
})

watch(() => form.value.mes_ano, (newVal) => {
  if (newVal && form.value.data_publicacao) {
    form.value.data_publicacao = ''
  }
})
</script>

<style scoped>
/* Animação de loading no ícone de download */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>