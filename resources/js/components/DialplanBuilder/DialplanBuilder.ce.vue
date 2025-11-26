<script setup>
import { ref, onMounted, computed } from 'vue';
import { VueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';

const props = defineProps({
  initialData: String,
  readonly: {
    type: [String, Boolean],
    default: false
  }
});

const nodes = ref([]);
const edges = ref([]);

const isReadonly = computed(() => {
  return props.readonly === 'true' || props.readonly === true;
});

onMounted(() => {
  if (props.initialData) {
    try {
      const data = JSON.parse(props.initialData);
      
      // Aplicar estilos según el tipo de nodo
      nodes.value = (data.nodes || []).map(node => ({
        ...node,
        style: getNodeStyle(node.data?.type)
      }));
      
      edges.value = data.edges || [];
      
      console.log('✅ Loaded nodes:', nodes.value.length);
      console.log('✅ Loaded edges:', edges.value.length);
    } catch (e) {
      console.error('❌ Error parsing initial data:', e);
    }
  }
});

function getNodeStyle(type) {
  const baseStyle = {
    padding: '10px',
    borderRadius: '8px',
    border: '2px solid',
    minWidth: '200px',
    fontSize: '12px'
  };

  const typeStyles = {
    'ivr': { 
      backgroundColor: '#d1ecf1', 
      borderColor: '#0dcaf0',
      color: '#055160'
    },
    'queue': { 
      backgroundColor: '#fff3cd', 
      borderColor: '#ffc107',
      color: '#664d03'
    },
    'ringgroup': { 
      backgroundColor: '#d1e7dd', 
      borderColor: '#198754',
      color: '#0f5132'
    },
    'conference': { 
      backgroundColor: '#e2d9f3', 
      borderColor: '#6f42c1',
      color: '#3d2465'
    },
    'callflow': { 
      backgroundColor: '#e2e3e5', 
      borderColor: '#6c757d',
      color: '#2c3034'
    },
    'default': { 
      backgroundColor: '#cfe2ff', 
      borderColor: '#0d6efd',
      color: '#084298'
    }
  };

  return {
    ...baseStyle,
    ...(typeStyles[type] || typeStyles.default)
  };
}
</script>

<template>
  <div class="dialplan-builder-container">
    <VueFlow 
      v-model:nodes="nodes" 
      v-model:edges="edges"
      :default-zoom="0.8"
      :min-zoom="0.1"
      :max-zoom="2"
      :nodes-draggable="!isReadonly"
      :nodes-connectable="!isReadonly"
      :elements-selectable="!isReadonly"
      fit-view-on-init
    >
      <Background pattern-color="#aaa" :gap="16" />
      <Controls :show-interactive="!isReadonly" />
      <MiniMap />
    </VueFlow>
  </div>
</template>

<style scoped>
.dialplan-builder-container {
  width: 100%;
  height: 100%;
  min-height: 600px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fafafa;
}
</style>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
</style>