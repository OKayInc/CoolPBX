<script setup>
import { ref, onMounted, computed } from 'vue';
import { VueFlow, useVueFlow, Panel, MarkerType } from '@vue-flow/core';
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

const isReadonly = computed(() => props.readonly === 'true' || props.readonly === true);

const dagreGraph = new dagre.graphlib.Graph();
dagreGraph.setDefaultEdgeLabel(() => ({}));

const nodeWidth = 280;
const nodeHeight = 100;

const getLayoutedElements = (nodes, edges, direction = 'LR') => {
  const isHorizontal = direction === 'LR';
  dagreGraph.setGraph({
    rankdir: direction,
    nodesep: 50,
    ranksep: 150
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
        type: 'custom-card',
        data: {
            ...node.data,
            icon: getIconForType(node.data?.type),
            colorClass: getClassForType(node.data?.type)
        }
      }));

      let initialEdges = (data.edges || []).map(edge => ({
        ...edge,
        type: 'smoothstep',
        markerEnd: MarkerType.ArrowClosed,
        style: { strokeWidth: 2, stroke: '#b1b1b7' },
        labelStyle: { fill: '#444', fontWeight: 600, fontSize: 11 },
        labelBgStyle: { fill: '#fff', fillOpacity: 0.8, rx: 4, ry: 4 },
      }));

      const layouted = getLayoutedElements(initialNodes, initialEdges);
      
      nodes.value = layouted.nodes;
      edges.value = layouted.edges;
      
      setTimeout(() => fitView({ padding: 0.2 }), 50);

    } catch (e) {
      console.error('❌ Error parsing initial data:', e);
    }
  }
});

function getIconForType(type) {
    const icons = {
        'ivr': '🤖',
        'queue': '👥',
        'ringgroup': '🔔',
        'callflow': '🔀',
        'conference': '🎤',
        'default': '📞'
    };
    return icons[type] || '📄';
}

function getClassForType(type) {
    return type || 'default';
}
</script>

<template>
  <div class="dialplan-builder-container">
    <VueFlow 
      v-model:nodes="nodes" 
      v-model:edges="edges"
      :default-zoom="0.7"
      :min-zoom="0.1"
      :max-zoom="2"
      :nodes-draggable="!isReadonly" 
      :nodes-connectable="false" 
      :elements-selectable="true"
      fit-view-on-init
    >
      <Background pattern-color="#e0e0e0" :gap="20" />
      <Controls :show-interactive="!isReadonly" />
      <MiniMap />

      <template #node-custom-card="{ data }">
        <div class="custom-node-card" :class="data.colorClass">
            <div class="node-header">
                <span class="node-icon">{{ data.icon }}</span>
                <span class="node-number">{{ data.number }}</span>
            </div>
            <div class="node-body">
                <div class="node-title">{{ data.label }}</div>
                <div v-if="data.context" class="node-subtitle">{{ data.context }}</div>
            </div>
            <div v-if="data.isCallFlow" class="node-badge">Flow</div>
        </div>
      </template>

      <Panel position="top-right" class="legend-panel">
        <div class="legend-title">References</div>
        <div class="legend-grid">
            <div class="legend-item"><span class="dot ivr"></span> IVR</div>
            <div class="legend-item"><span class="dot queue"></span> Queue</div>
            <div class="legend-item"><span class="dot ringgroup"></span> Ring Group</div>
            <div class="legend-item"><span class="dot callflow"></span> Call Flow</div>
            <div class="legend-item"><span class="dot conference"></span> Conference</div>
            <div class="legend-item"><span class="dot default"></span> Extension</div>
        </div>
      </Panel>

    </VueFlow>
  </div>
</template>

<style scoped>
.dialplan-builder-container {
  width: 100%;
  height: 100%;
  min-height: 700px;
  border-radius: 12px;
  background: #f8f9fa;
  box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
}

.custom-node-card {
    background: white;
    border: 1px solid #ddd;
    border-left: 5px solid #999;
    border-radius: 8px;
    width: 260px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
    overflow: hidden;
    font-family: 'Segoe UI', sans-serif;
}

.custom-node-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 10;
}

.node-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    background: rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    font-size: 0.85rem;
    font-weight: 600;
    color: #555;
}

.node-number {
    font-family: monospace;
    background: rgba(255,255,255,0.8);
    padding: 1px 6px;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.1);
}

.node-body {
    padding: 10px;
    text-align: left;
}

.node-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #333;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.node-subtitle {
    font-size: 0.75rem;
    color: #888;
}

.node-badge {
    position: absolute;
    top: 2px;
    right: 35%;
    background: #6c757d;
    color: white;
    font-size: 9px;
    padding: 1px 4px;
    border-radius: 4px;
}

.custom-node-card.ivr { border-left-color: #0dcaf0; }
.custom-node-card.ivr .node-header { background: #e0f7fa; color: #006064; }

.custom-node-card.queue { border-left-color: #ffc107; }
.custom-node-card.queue .node-header { background: #fff8e1; color: #ff6f00; }

.custom-node-card.ringgroup { border-left-color: #198754; }
.custom-node-card.ringgroup .node-header { background: #e8f5e9; color: #1b5e20; }

.custom-node-card.callflow { border-left-color: #6c757d; }
.custom-node-card.callflow .node-header { background: #f8f9fa; color: #212529; }

.custom-node-card.conference { border-left-color: #6f42c1; }
.custom-node-card.conference .node-header { background: #f3e5f5; color: #4a148c; }

.custom-node-card.default { border-left-color: #0d6efd; }
.custom-node-card.default .node-header { background: #e3f2fd; color: #0d47a1; }

.legend-panel {
  background: white;
  padding: 12px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  border: 1px solid #eee;
}
.legend-title { font-weight: 700; margin-bottom: 8px; font-size: 13px; color: #333; border-bottom: 1px solid #eee; padding-bottom: 4px;}
.legend-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.legend-item { display: flex; align-items: center; font-size: 11px; color: #555; }
.dot { width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; display: inline-block;}

.dot.ivr { background: #0dcaf0; }
.dot.queue { background: #ffc107; }
.dot.ringgroup { background: #198754; }
.dot.callflow { background: #6c757d; }
.dot.conference { background: #6f42c1; }
.dot.default { background: #0d6efd; }

</style>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
</style>