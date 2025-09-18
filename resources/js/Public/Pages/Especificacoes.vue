<template>
  <PublicLayout :stats="pageStats">
    <Head title="Especificações Técnicas" />

    <!-- Header da página -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center">
          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            Especificações Técnicas
          </h1>
          <p class="text-xl text-blue-100 max-w-2xl mx-auto">
            Documentos técnicos para equipamentos
          </p>
        </div>
      </div>
    </section>

    <!-- Lista de especificações -->
    <section class="max-w-7xl mx-auto px-4 py-8 min-h-[60vh]">
    <!-- Loading state -->
    <div v-if="carregando" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Carregando especificações...</p>
    </div>

    <!-- Lista de especificações -->
    <div v-else-if="especificacoes && especificacoes.length > 0" class="space-y-4">
        <div
        v-for="(especificacao, index) in especificacoes"
        :key="especificacao?.id || `spec-${index}`"
        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200"
        >
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                {{ especificacao?.nome || 'Especificação técnica' }}
                </h3>
                <p v-if="especificacao?.descricao" class="text-gray-700 mb-3 leading-relaxed">
                {{ especificacao.descricao }}
                </p>
            </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
            <div v-if="especificacao?.created_at">
                <i class="fas fa-calendar mr-1 text-blue-600"></i>
                Data de Criação: {{ formatarData(especificacao.created_at) }}
            </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="flex space-x-3">
                <button
                v-if="especificacao?.arquivo"
                @click="visualizarPDF(especificacao?.id)"
                class="bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                <i class="fas fa-eye"></i>
                Visualizar
                </button>

                <button
                v-if="especificacao?.arquivo"
                @click="baixarEspecificacao(especificacao?.id, especificacao?.nome)"
                :disabled="baixandoId === especificacao?.id"
                class="bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
                >
                <i class="fas fa-download" :class="{ 'animate-spin': baixandoId === especificacao?.id }"></i>
                {{ baixandoId === especificacao?.id ? 'Baixando...' : 'Baixar' }}
                </button>
            </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Estado vazio -->
    <div v-else class="text-center py-12">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-8">
        <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">
            Nenhuma especificação encontrada
        </h3>
        <p class="text-gray-600 mb-6">
            No momento não há especificações técnicas disponíveis.
        </p>
        </div>
    </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'

const props = defineProps({
  especificacoes: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const carregando = ref(false)
const baixandoId = ref(null)

const pageStats = computed(() => ({
  usuarios_ativos: props.stats?.usuarios_ativos || 0,
  normas_cadastradas: props.stats?.normas_cadastradas || 0,
  especificacoes_count: props.especificacoes?.length || 0
}))

const formatarData = (data) => {
  if (!data) return 'Data não disponível'

  try {
    const dataObj = new Date(data)

    if (isNaN(dataObj.getTime())) {
      return data
    }

    return dataObj.toLocaleDateString('pt-BR', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      timeZone: 'America/Sao_Paulo'
    })
  } catch (error) {
    console.error('Erro ao formatar data:', error)
    return data
  }
}

const baixarEspecificacao = async (id, nome) => {
  if (!id) {
    console.error('ID da especificação não informado')
    return
  }

  baixandoId.value = id

  try {
    // Criar um link temporário para forçar o download
    const link = document.createElement('a')
    link.href = `/especificacao/download/${id}`
    link.download = `${nome || 'especificacao'}.pdf`
    link.target = '_blank'

    // Adicionar o link ao DOM
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    // Pequeno delay para melhorar a UX
    await new Promise(resolve => setTimeout(resolve, 1000))

  } catch (error) {
    console.error('Erro ao baixar especificação:', error)
    alert('Erro ao baixar o arquivo. Tente novamente.')
  } finally {
    baixandoId.value = null
  }
}

const visualizarPDF = (id) => {
  if (!id) {
    console.error('ID da especificação não informado')
    return
  }

  // Abrir PDF em nova aba
  window.open(`/especificacao/view/${id}`, '_blank')
}

const recarregarPagina = () => {
  window.location.reload()
}

onMounted(() => {
  console.log('Especificações carregadas:', props.especificacoes)
})
</script>

<style scoped>
/* Acessibilidade */
button:focus {
  outline: 2px solid #1f1f1f;
  outline-offset: 2px;
}

/* Estados interativos */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}

/* Animação de loading nos botões */
button:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

/* Estados visuais */
.group:hover {
  background-color: #f9fafb;
}

/* Bordas */
.border-gray-100 {
  border-color: #f3f4f6;
}

/* Lista */
.bg-white > div:nth-child(even) {
  background-color: #fafafa;
}

.bg-white > div:nth-child(even):hover {
  background-color: #f5f5f5;
}

/* Transições */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

@media (max-width: 640px) {
  /* Botões ocupam toda a largura em telas muito pequenas */
  .flex.flex-col.sm\\:flex-row button {
    min-height: 44px;
  }

  /* Padding reduzido em telas pequenas */
  .px-4.sm\\:px-6 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  /* Texto ligeiramente menor em mobile */
  .text-lg {
    font-size: 1.1rem;
  }

  /* Ícones menores */
  .text-3xl.sm\\:text-4xl {
    font-size: 2rem;
  }
}

/* Hover effects */
@media (min-width: 1024px) {
  button:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }

  .group:hover {
    transform: translateY(-1px);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }
}

/* Animação para o ícone de loading */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.fa-spin {
  animation: spin 1s linear infinite;
}
</style>
