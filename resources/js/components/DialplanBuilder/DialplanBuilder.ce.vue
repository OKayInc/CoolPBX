<script setup>
import { ref, onMounted, computed } from 'vue';
import { VueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';

const props = defineProps({
  initialData: String,
  readonly: {
    type: [String, Boolean],
    default: false
  }
});

const nodes = ref([]);
const edges = ref([]);

// Convertir readonly a boolean
const isReadonly = computed(() => {
  return props.readonly === 'true' || props.readonly === true;
});

onMounted(() => {
  if (props.initialData) {
    try {
      const data = JSON.parse(props.initialData);
      nodes.value = data.nodes || [];
      edges.value = data.edges || [];
      console.log('✅ Loaded nodes:', nodes.value.length);
      console.log('✅ Loaded edges:', edges.value.length);
    } catch (e) {
      console.error('❌ Error parsing initial data:', e);
    }
  }
});
</script>

<template>
  <div class="dialplan-builder-container">
    <VueFlow 
      v-model:nodes="nodes" 
      v-model:edges="edges"
      :default-zoom="1"
      :min-zoom="0.2"
      :max-zoom="4"
      :nodes-draggable="!isReadonly"
      :nodes-connectable="!isReadonly"
      :elements-selectable="!isReadonly"
    >
      <Background pattern-color="#aaa" :gap="16" />
      <Controls :show-interactive="!isReadonly" />
    </VueFlow>
  </div>
</template>

<style scoped>
.dialplan-builder-container {
  width: 100%;
  height: 600px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
}
</style>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
</style>