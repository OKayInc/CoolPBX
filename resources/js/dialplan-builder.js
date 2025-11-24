import { defineCustomElement } from 'vue';
import DialplanBuilderComponent from './components/DialplanBuilder/DialplanBuilder.ce.vue';

const DialplanBuilder = defineCustomElement(DialplanBuilderComponent);

customElements.define('dialplan-builder', DialplanBuilder);