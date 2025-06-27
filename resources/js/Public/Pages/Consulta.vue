<template>
  <PublicLayout :stats="pageStats">
    <Head title="Consulta de Normas" />
    
    <!-- Header da página -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            <!-- <i class="fas fa-search mr-3"></i> -->
            Consulta Pública de Normas
          </h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Pesquise por descrição, resumo ou palavra-chave da norma
          </p>
        </div>
      </div>
    </section>

    <!-- Formulário de Busca -->
    <section class="bg-white shadow-lg -mt-6 relative z-10">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <form @submit.prevent="buscarNormas" class="space-y-6">
          <!-- Busca por termo -->
          <div class="mb-6">
            <label for="search_term" class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-search mr-2 text-blue-600"></i>
              Termo de busca (descrição, resumo ou palavra-chave)
            </label>
            <input
              id="search_term"
              v-model="form.search_term"
              type="text"
              placeholder="Ex: portaria, regulamento, procedimento..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
            />
          </div>

          <!-- Filtros avançados -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label for="tipo_id" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-layer-group mr-2 text-blue-600"></i>
                Tipo de Norma
              </label>
              <select
                id="tipo_id"
                v-model="form.tipo_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os tipos</option>
                <option 
                  v-for="tipo in (tipos || [])" 
                  :key="tipo?.id || 'tipo-' + Math.random()" 
                  :value="tipo?.id"
                >
                  {{ tipo?.tipo || 'Tipo não informado' }}
                </option>
              </select>
            </div>

            <div>
              <label for="orgao_id" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-building mr-2 text-blue-600"></i>
                Órgão
              </label>
              <select
                id="orgao_id"
                v-model="form.orgao_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os órgãos</option>
                <option 
                  v-for="orgao in (orgaos || [])" 
                  :key="orgao?.id || 'orgao-' + Math.random()" 
                  :value="orgao?.id"
                >
                  {{ orgao?.orgao || 'Órgão não informado' }}
                </option>
              </select>
            </div>

            <div>
              <label for="vigente" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                Vigência
              </label>
              <select
                id="vigente"
                v-model="form.vigente"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas</option>
                <option value="VIGENTE">Vigente</option>
                <option value="EM ANÁLISE">Em Análise</option>
                <option value="NÃO VIGENTE">Não Vigente</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                type="button"
                @click="mostrarFiltrosData = !mostrarFiltrosData"
                class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-medium transition-all duration-300 flex items-center justify-center"
              >
                <i class="fas fa-calendar mr-2"></i>
                Filtrar por Data
              </button>
            </div>
          </div>

          <!-- Filtros de data -->
          <div v-if="mostrarFiltrosData" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg">
            <div>
              <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Data Início
              </label>
              <input
                id="data_inicio"
                v-model="form.data_inicio"
                type="date"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Data Fim
              </label>
              <input
                id="data_fim"
                v-model="form.data_fim"
                type="date"
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
              {{ carregando ? 'Consultando...' : 'Consultar' }}
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
      <div v-if="normas?.data && Array.isArray(normas.data)" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center justify-between flex-wrap">
            <div class="text-blue-800">
              <i class="fas fa-info-circle mr-2"></i>
              <strong>{{ normas?.total || 0 }}</strong> norma(s) encontrada(s)
              <span v-if="temFiltrosAtivos" class="ml-2 text-sm">
                (com filtros aplicados)
              </span>
            </div>
            <div class="text-sm text-blue-600">
              Página {{ normas?.current_page || 1 }} de {{ normas?.last_page || 1 }}
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de normas -->
      <div v-if="normas?.data && Array.isArray(normas.data) && normas.data.length > 0" class="space-y-4">
        <div
          v-for="norma in normas.data"
          :key="norma?.id || 'norma-' + Math.random()"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  {{ norma?.descricao || 'Descrição não informada' }}
                  <span v-if="norma?.tipo?.tipo" class="ml-2 text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                    {{ norma.tipo.tipo }}
                  </span>
                </h3>
                <p v-if="norma?.resumo" class="text-gray-700 mb-3 leading-relaxed">
                  {{ norma.resumo }}
                </p>
              </div>
              <div class="ml-4 flex flex-col items-end space-y-2">
                <span
                  :class="getVigenciaClass(norma?.vigente)"
                  class="px-3 py-1 rounded-full text-xs font-medium"
                >
                  <i :class="getVigenciaIcon(norma?.vigente)" class="mr-1"></i>
                  {{ norma?.vigente || 'N/A' }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 mb-4">
              <div v-if="norma?.orgao?.orgao">
                <i class="fas fa-building mr-1 text-blue-600"></i>
                {{ norma.orgao.orgao }}
              </div>
              <div v-if="norma?.data">
                <i class="fas fa-calendar mr-1 text-blue-600"></i>
                Data: {{ formatarData(norma.data) }}
              </div>
              <div v-if="norma?.palavras_chave && Array.isArray(norma.palavras_chave) && norma.palavras_chave.length > 0">
                <i class="fas fa-tags mr-1 text-blue-600"></i>
                <span class="inline-flex flex-wrap gap-1">
                  <span
                    v-for="palavra in norma.palavras_chave"
                    :key="palavra?.id || 'palavra-' + Math.random()"
                    class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs"
                  >
                    {{ palavra?.palavra_chave || 'Palavra-chave' }}
                  </span>
                  <span
                    v-if="(norma?.palavras_chave_restantes || 0) > 0"
                    class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs"
                  >
                    +{{ norma.palavras_chave_restantes }}
                  </span>
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
              <div class="flex space-x-3">
                <button
                  @click="visualizarNorma(norma?.id)"
                  :disabled="!norma?.id"
                  class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
                >
                  <i class="fas fa-eye mr-1"></i>
                  Ver Detalhes
                </button>
                <button
                  v-if="norma?.anexo_url || norma?.anexo"
                  @click="baixarArquivo(norma?.id)"
                  class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
                >
                  <i class="fas fa-download mr-1"></i>
                  Baixar PDF
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="normas?.last_page && normas.last_page > 1" class="mt-8">
        <nav class="flex items-center justify-center space-x-2">
          <button
            v-if="normas.current_page > 1"
            @click="irParaPagina(normas.current_page - 1)"
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
              :class="pagina === normas.current_page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
              class="px-3 py-2 rounded-lg transition-all duration-300"
            >
              {{ pagina }}
            </button>
          </template>
          
          <button
            v-if="normas.current_page < normas.last_page"
            @click="irParaPagina(normas.current_page + 1)"
            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg transition-all duration-300"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </nav>
      </div>

      <!-- Estado vazio -->
      <div v-else-if="normas?.data && Array.isArray(normas.data) && normas.data.length === 0" class="text-center py-12">
        <div class="bg-gray-50 rounded-lg p-8">
          <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Nenhuma norma encontrada
          </h3>
          <p class="text-gray-600 mb-6">
            Tente ajustar os filtros ou usar termos diferentes na busca.
          </p>
          <button
            @click="limparFiltros"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300"
          >
            <i class="fas fa-refresh mr-2"></i>
            Nova Busca
          </button>
        </div>
      </div>

      <!-- Estado inicial -->
      <div v-else class="text-center py-12">
        <div class="p-8">
          <i class="fas fa-search text-4xl text-blue-600 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Pronto para buscar
          </h3>
          <p class="text-gray-600 mb-4">
            Use os filtros acima para encontrar as normas que você precisa.
          </p>
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
  tipos: {
    type: Array,
    default: () => []
  },
  orgaos: {
    type: Array,
    default: () => []
  },
  normas: {
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
  }
})

// Estado do componente
const carregando = ref(false)
const mostrarFiltrosData = ref(false)

// Formulário de busca
const form = ref({
  search_term: props.filtros?.search_term || '',
  tipo_id: props.filtros?.tipo_id || '',
  orgao_id: props.filtros?.orgao_id || '',
  vigente: props.filtros?.vigente || '',
  data_inicio: props.filtros?.data_inicio || '',
  data_fim: props.filtros?.data_fim || ''
})

const temFiltrosAtivos = computed(() => {
  return !!(form.value.search_term || form.value.tipo_id || form.value.orgao_id || 
           form.value.vigente || form.value.data_inicio || form.value.data_fim)
})

const pageStats = computed(() => ({
  usuarios_ativos: props.stats?.usuarios_ativos || 0,
  normas_cadastradas: props.stats?.normas_cadastradas || 0,
  total_encontradas: props.normas?.total || 0
}))

const paginasVisiveis = computed(() => {
  if (!props.normas || !props.normas.current_page || !props.normas.last_page) {
    return []
  }
  
  const current = props.normas.current_page
  const last = props.normas.last_page
  const pages = []
  
  // Sempre mostrar primeira página
  if (current > 3) {
    pages.push(1)
    if (current > 4) pages.push('...')
  }
  
  // Páginas ao redor da atual
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  // Sempre mostrar última página
  if (current < last - 2) {
    if (current < last - 3) pages.push('...')
    pages.push(last)
  }
  
  return pages
})

// Métodos
const buscarNormas = () => {
  carregando.value = true
  
  // Adicionar parâmetro 'busca=1' para indicar que é uma busca ativa
  const params = { ...form.value, busca: 1 }
  
  router.get('/consulta', params, {
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

const buscarTodasNormas = () => {
  carregando.value = true
  
  // Limpar todos os filtros e fazer busca
  form.value = {
    search_term: '',
    tipo_id: '',
    orgao_id: '',
    vigente: '',
    data_inicio: '',
    data_fim: ''
  }
  
  // Fazer busca com parâmetro indicando que é busca ativa
  router.get('/consulta', { busca: 1 }, {
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
    tipo_id: '',
    orgao_id: '',
    vigente: '',
    data_inicio: '',
    data_fim: ''
  }
  
  router.get('/consulta', {}, {
    preserveState: true
  })
}

//Formatar data
const formatarData = (data) => {
  if (!data) return ''
  
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
    console.error('Erro ao formatar data:', error)
    return data
  }
}

const irParaPagina = (pagina) => {
  if (pagina === '...' || pagina === props.normas?.current_page || !pagina) return
  
  const params = { ...form.value, page: pagina, busca: 1 }
  
  router.get('/consulta', params, {
    preserveState: true,
    preserveScroll: true
  })
}

const visualizarNorma = (id) => {
  if (!id) {
    console.error('ID da norma não informado')
    return
  }
  router.get(`/norma/${id}`)
}

const baixarArquivo = (normaId) => {
  if (!normaId) {
    console.error('ID da norma não informado')
    return
  }
  
  // Criar um link temporário para forçar o download
  const link = document.createElement('a')
  link.href = `/norma/${normaId}/download`
  link.target = '_blank'
  
  // Adicionar o link ao DOM
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Funções para classes de vigência
const getVigenciaClass = (vigente) => {
  if (!vigente) return 'bg-gray-100 text-gray-800'
  
  switch (vigente) {
    case 'VIGENTE':
      return 'bg-green-100 text-green-800'
    case 'EM ANÁLISE':
      return 'bg-yellow-100 text-yellow-800'
    case 'NÃO VIGENTE':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getVigenciaIcon = (vigente) => {
  if (!vigente) return 'fas fa-question-circle'
  
  switch (vigente) {
    case 'VIGENTE':
      return 'fas fa-check-circle'
    case 'EM ANÁLISE':
      return 'fas fa-clock'
    case 'NÃO VIGENTE':
      return 'fas fa-times-circle'
    default:
      return 'fas fa-question-circle'
  }
}

// Watchers
watch(() => props.filtros, (newFiltros) => {
  if (newFiltros && typeof newFiltros === 'object') {
    form.value = { 
      search_term: newFiltros.search_term || '',
      tipo_id: newFiltros.tipo_id || '',
      orgao_id: newFiltros.orgao_id || '',
      vigente: newFiltros.vigente || '',
      data_inicio: newFiltros.data_inicio || '',
      data_fim: newFiltros.data_fim || ''
    }
  }
}, { immediate: true })

// Se houver filtros de data, expandir automaticamente
watch(() => [form.value.data_inicio, form.value.data_fim], ([inicio, fim]) => {
  if (inicio || fim) {
    mostrarFiltrosData.value = true
  }
}, { immediate: true })
</script>