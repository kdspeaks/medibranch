import "./bootstrap";
// resources/js/app.js

import './../../vendor/power-components/livewire-powergrid/dist/powergrid'

import theme from "./theme";
import avatarComponent from "./avatar";

window.avatarComponent = avatarComponent; // ← MAKE IT AVAILABLE TO BLADE
Alpine.data("themeStore", theme);    




document.addEventListener('livewire:navigated', () => { 
    // sidebarToggler();
})

window.addEventListener('livewire:updated', () => {
    
});
// Livewire.hook('morph.updated', ({ el, component }) => {
//     console.log('[Livewire] DOM updated — reinitializing...');
//     sidebarToggler();
// })

