<script setup>
import { ref, onMounted, computed } from 'vue';
import { VueFlow, useVueFlow, Panel } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import dagre from 'dagre';

const props = defineProps({
  initialData: String,
  readonly: {
    type: [String, Boolean],
    default: false
  }
});

const { fitView } = useVueFlow();

const nodes = ref([]);
const edges = ref([]);

const isReadonly = computed(() => {
  return props.readonly === 'true' || props.readonly === true;
});

const dagreGraph = new dagre.graphlib.Graph();
dagreGraph.setDefaultEdgeLabel(() => ({}));

const nodeWidth = 240;
const nodeHeight = 120;

const getLayoutedElements = (nodes, edges, direction = 'TB') => {
  const isHorizontal = direction === 'LR';
  dagreGraph.setGraph({ 
    rankdir: direction,
    nodesep: 80,
    ranksep: 100 
  });

  nodes.forEach((node) => {
    dagreGraph.setNode(node.id, { width: nodeWidth, height: nodeHeight });
  });

  edges.forEach((edge) => {
    dagreGraph.setEdge(edge.source, edge.target);
  });

  dagre.layout(dagreGraph);

  const layoutedNodes = nodes.map((node) => {
    const nodeWithPosition = dagreGraph.node(node.id);
    return {
      ...node,
      targetPosition: isHorizontal ? 'left' : 'top',
      sourcePosition: isHorizontal ? 'right' : 'bottom',
      position: {
        x: nodeWithPosition.x - nodeWidth / 2,
        y: nodeWithPosition.y - nodeHeight / 2,
      },
    };
  });

  return { nodes: layoutedNodes, edges };
};

onMounted(() => {
  if (props.initialData) {
    try {
      const data = JSON.parse(props.initialData);
      
      let initialNodes = (data.nodes || []).map(node => ({
        ...node,
        style: getNodeStyle(node.data?.type)
      }));
      
      let initialEdges = data.edges || [];

      const layouted = getLayoutedElements(initialNodes, initialEdges);
      
      nodes.value = layouted.nodes;
      edges.value = layouted.edges;
      
      setTimeout(() => {
        fitView();
      }, 50);

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
    width: '200px', 
    fontSize: '12px',
    textAlign: 'center'
  };

  const typeStyles = {
    'ivr': { backgroundColor: '#d1ecf1', borderColor: '#0dcaf0', color: '#055160' },
    'queue': { backgroundColor: '#fff3cd', borderColor: '#ffc107', color: '#664d03' },
    'ringgroup': { backgroundColor: '#d1e7dd', borderColor: '#198754', color: '#0f5132' },
    'conference': { backgroundColor: '#e2d9f3', borderColor: '#6f42c1', color: '#3d2465' },
    'callflow': { backgroundColor: '#e2e3e5', borderColor: '#6c757d', color: '#2c3034' },
    'default': { backgroundColor: '#cfe2ff', borderColor: '#0d6efd', color: '#084298' }
  };

  return { ...baseStyle, ...(typeStyles[type] || typeStyles.default) };
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

      <Panel position="top-right" class="legend-panel">
        <div class="legend-title">References</div>
        <div class="legend-item"><span class="dot ivr"></span> IVR Menu</div>
        <div class="legend-item"><span class="dot queue"></span> Queue</div>
        <div class="legend-item"><span class="dot ringgroup"></span> Ring Group</div>
        <div class="legend-item"><span class="dot callflow"></span> Call Flow</div>
        <div class="legend-item"><span class="dot default"></span> Extension</div>
      </Panel>

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

.legend-panel {
  background: white;
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border: 1px solid #eee;
}
.legend-title { font-weight: bold; margin-bottom: 5px; font-size: 12px; }
.legend-item { display: flex; align-items: center; font-size: 11px; margin-bottom: 3px; }
.dot { width: 12px; height: 12px; border-radius: 3px; margin-right: 5px; }
.dot.ivr { background: #d1ecf1; border: 1px solid #0dcaf0; }
.dot.queue { background: #fff3cd; border: 1px solid #ffc107; }
.dot.ringgroup { background: #d1e7dd; border: 1px solid #198754; }
.dot.callflow { background: #e2e3e5; border: 1px solid #6c757d; }
.dot.default { background: #cfe2ff; border: 1px solid #0d6efd; }
</style>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
</style>