<template>
  <PublicLayout :stats="stats">
    <Head :title="`${norma?.numero_norma || norma?.descricao} - ${norma?.tipo?.tipo || 'Norma'}`" />

    <!-- Header da norma -->
    <section class="bg-gradient-to-r from-gray-600 via-gray-700 to-gray-800 text-white py-12">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between flex-wrap">
          <div>
            <nav class="text-blue-200 text-sm mb-2">
              <Link href="/" class="hover:text-white">Início</Link>
              <span class="mx-2">/</span>
              <Link href="/consulta" class="hover:text-white">Consulta</Link>
              <span class="mx-2">/</span>
              <span>{{ norma?.numero_norma || norma?.descricao }}</span>
            </nav>
            <h1 class="text-2xl md:text-3xl font-bold">
              {{ norma?.numero_norma || norma?.descricao }}
            </h1>
            <p v-if="norma?.tipo" class="text-blue-100 mt-1">
              {{ norma.tipo.tipo }}
            </p>
          </div>
          <!-- <div class="mt-4 md:mt-0">
            <span
              :class="getVigenciaClass(norma?.vigente)"
              class="px-4 py-2 rounded-full text-white font-medium"
            >
              <i :class="getVigenciaIcon(norma?.vigente)" class="mr-2"></i>
              {{ norma?.vigente || 'Status não informado' }}
            </span>
          </div> -->
        </div>
      </div>
    </section>

    <!-- Conteúdo da norma -->
    <section class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Informações Básicas -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 bg-gray-50 p-3 rounded-lg">
          Informações Básicas
        </h2>
        <div class="border-t border-gray-200">
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.descricao">
            <span class="font-medium text-gray-700">Documento</span>
            <div class="text-gray-900 font-medium">{{ norma?.descricao }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.numero_norma">
            <span class="font-medium text-gray-700">Número</span>
            <div class="text-gray-900">{{ norma.numero_norma }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.data">
            <span class="font-medium text-gray-700">Data</span>
            <div class="text-gray-900">{{ formatarData(norma.data) }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.orgao">
            <span class="font-medium text-gray-700">Órgão</span>
            <div class="text-gray-900">{{ norma.orgao.orgao }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.data_publicacao">
            <span class="font-medium text-gray-700">Publicação</span>
            <div class="text-gray-900">{{ formatarData(norma.data_publicacao) }} - DOEPB</div>
          </div>
        </div>
      </div>

      <!-- Classificação Documental -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 bg-gray-50 p-3 rounded-lg">
          Classificação Documental
        </h2>
        <div class="border-t border-gray-200">
          <!-- <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.vigente">
            <span class="font-medium text-gray-700">Status</span>
            <div :class="getVigenciaTextClass(norma.vigente)">
              <i :class="getVigenciaIcon(norma.vigente)" class="mr-1"></i>
              {{ norma.vigente }}
            </div>
          </div> -->
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.tipo">
            <span class="font-medium text-gray-700">Tipo Normativo</span>
            <div class="text-gray-900">{{ norma.tipo.tipo }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.data_vigencia">
            <span class="font-medium text-gray-700">Vigência</span>
            <div class="text-gray-900">{{ formatarData(norma.data_vigencia) }}</div>
          </div>
          <div class="grid grid-cols-2 gap-4 py-2" v-if="norma?.data_revogacao">
            <span class="font-medium text-gray-700">Revogação</span>
            <div class="text-red-600 font-medium">{{ formatarData(norma.data_revogacao) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ementa -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-4 bg-gray-50 p-3 rounded-lg">
        Ementa
      </h2>
      <!-- Resumo -->
      <div v-if="norma?.resumo" class="mt-4 pt-4 border-t border-gray-200">
        <div class="text-gray-600 leading-relaxed">
          {{ norma.resumo }}
        </div>
      </div>

      <!-- Palavras-chave (se houver) -->
      <div v-if="norma?.palavrasChave && norma.palavrasChave.length > 0" class="mt-4 pt-4 border-t border-gray-200">
        <span class="font-medium text-gray-700 block mb-2">Palavras-chave</span>
        <div class="flex flex-wrap gap-1">
          <span
            v-for="palavra in norma.palavrasChave"
            :key="palavra?.id || Math.random()"
            class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium"
          >
            {{ palavra?.palavra_chave || palavra?.palavra }}
          </span>
        </div>
      </div>
    </div>

      <!-- Visualizador de PDF -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
        <i class="fas fa-file-pdf mr-2 text-red-600"></i>
        Documento Digital
        </h2>

        <!-- Mobile -->
        <div v-if="isMobile" class="text-center py-8">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-4">
            <i class="fas fa-mobile-alt text-4xl text-gray-500 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Visualização Mobile
            </h3>
            <p class="text-gray-600 mb-4">
            Para uma melhor experiência de leitura em dispositivos móveis,
            recomendamos abrir o documento em uma nova aba ou fazer o download.
            </p>

            <!-- Botões de ação para mobile -->
            <div class="flex flex-col gap-3">
            <button
                @click="abrirEmNovaAba"
                class="w-full bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-external-link-alt"></i>
                Abrir em Nova Aba
            </button>

            <button
                @click="baixarArquivo"
                class="w-full bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center justify-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
            >
                <i class="fas fa-download"></i>
                Baixar PDF
            </button>

            </div>
        </div>
        </div>

        <!-- Container do PDF -->
        <div v-if="!isMobile || mostrarPdfMobile" class="border border-gray-300 rounded-lg overflow-hidden" :style="{ height: isMobile ? '400px' : '550px' }">

        <iframe
            :src="`/norma/${norma.id}/view?v=${norma.updated_at}#zoom=${isMobile ? '75' : '100'}`"
            class="w-full h-full"
            frameborder="0"
            title="Visualizador de PDF"
            @load="onIframeLoad"
            @error="onIframeError"
        >
            <div class="p-4 text-center text-gray-600">
            <p class="mb-4">Seu navegador não suporta a visualização de PDFs.</p>
            <button @click="baixarArquivo" class="text-blue-600 hover:underline">
                Clique aqui para baixar o arquivo.
            </button>
            </div>
        </iframe>
        </div>

        <!-- Botões de Ação -->
        <div v-if="!isMobile || mostrarPdfMobile" class="flex flex-wrap justify-between items-center gap-3 mt-4 p-4 bg-gradient-to-r from-slate-50 to-gray-50 rounded-lg border border-gray-200">
        <div class="text-sm text-gray-600 flex items-center">
            <div class="w-2 h-2 bg-slate-400 rounded-full mr-2 animate-pulse"></div>
            <span v-if="!isMobile">Use os controles do visualizador para navegar pelo documento</span>
            <span v-else>Deslize ou use os controles para navegar</span>
        </div>

        <div class="flex gap-2 flex-wrap">
            <!-- Botão Download -->
            <button
            @click="baixarArquivo"
            class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
            >
            <i class="fas fa-download text-sm group-hover:animate-bounce"></i>
            <span class="hidden sm:inline">Baixar PDF</span>
            </button>

            <!-- Botão Nova Aba -->
            <button
            @click="abrirEmNovaAba"
            class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
            >
            <i class="fas fa-external-link-alt text-sm group-hover:rotate-12"></i>
            <span class="hidden sm:inline">Nova Aba</span>
            </button>

            <!-- Botão Compartilhar -->
            <button
            @click="compartilhar"
            class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
            >
            <i class="fas fa-share-alt text-sm group-hover:rotate-12"></i>
            <span class="hidden sm:inline">Compartilhar</span>
            </button>

            <!-- Botão Voltar -->
            <button
            @click="voltar"
            class="group bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 hover:scale-105 hover:shadow-md active:scale-95"
            >
            <i class="fas fa-arrow-left text-sm group-hover:-translate-x-1"></i>
            <span class="hidden sm:inline">Voltar</span>
            </button>
        </div>
        </div>
    </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '../Layouts/PublicLayout.vue'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  norma: {
    type: Object,
    required: true
  },
  relacionadas: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Estado para controle mobile
const isMobile = ref(false)
const mostrarPdfMobile = ref(false)

const formatarData = (data) => {
  if (!data) return ''
  try {
    return new Date(data).toLocaleDateString('pt-BR')
  } catch {
    return data
  }
}

const baixarArquivo = () => {
  const link = document.createElement('a')
  link.href = `/norma/${props.norma.id}/download`
  link.download = `${props.norma.numero_norma || props.norma.descricao || 'norma'}.pdf`
  link.target = '_blank'

  // Adicionar o link ao DOM
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const abrirEmNovaAba = () => {
  window.open(`/norma/${props.norma.id}/view`, '_blank')
}

const compartilhar = async () => {
  const titulo = props.norma?.numero_norma || props.norma?.descricao || 'Norma'
  const texto = props.norma?.resumo || props.norma?.descricao || ''
  const url = window.location.href

  if (navigator.share) {
    try {
      await navigator.share({
        title: titulo,
        text: texto,
        url: url
      })
    } catch (error) {
      if (error.name !== 'AbortError') {
        copiarLink(url)
      }
    }
  } else {
    copiarLink(url)
  }
}

const copiarLink = async (url) => {
  try {
    await navigator.clipboard.writeText(url)
    alert('Link copiado para a área de transferência!')
  } catch {
    // Fallback para navegadores antigos
    const textArea = document.createElement('textarea')
    textArea.value = url
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    alert('Link copiado para a área de transferência!')
  }
}

const voltar = () => {
  router.get('/consulta')
}

const visualizarNorma = (id) => {
  if (id) {
    router.get(`/norma/${id}`)
  }
}

// Funções para estilização de vigência
const getVigenciaClass = (vigente) => {
  switch (vigente) {
    case 'VIGENTE':
      return 'bg-green-500'
    case 'EM ANÁLISE':
      return 'bg-yellow-500'
    case 'NÃO VIGENTE':
      return 'bg-red-500'
    default:
      return 'bg-gray-500'
  }
}

const getVigenciaIcon = (vigente) => {
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

const getVigenciaTextClass = (vigente) => {
  switch (vigente) {
    case 'VIGENTE':
      return 'text-green-600 font-medium'
    case 'EM ANÁLISE':
      return 'text-yellow-600 font-medium'
    case 'NÃO VIGENTE':
      return 'text-red-600 font-medium'
    default:
      return 'text-gray-600'
  }
}

// Detectar dispositivo mobile
const detectMobile = () => {
  const userAgent = navigator.userAgent || navigator.vendor || window.opera
  const isMobileDevice = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent.toLowerCase())
  const isSmallScreen = window.innerWidth <= 768

  isMobile.value = isMobileDevice || isSmallScreen
}

// Eventos do iframe
const onIframeLoad = () => {
  console.log('PDF carregado com sucesso')
}

const onIframeError = () => {
  console.error('Erro ao carregar PDF')
}

// Lifecycle
onMounted(() => {
  detectMobile()

  // Listener para mudanças de orientação/tamanho
  window.addEventListener('resize', detectMobile)
  window.addEventListener('orientationchange', () => {
    setTimeout(detectMobile, 100)
  })
})

onUnmounted(() => {
  window.removeEventListener('resize', detectMobile)
  window.removeEventListener('orientationchange', detectMobile)
})
</script>
