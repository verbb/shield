import '../css/shield.css';
// Plugin Kit's stylesheet loads with the page, in <head>, so its FOUCE rules hide pk-* elements until their components load.
import '@verbb/plugin-kit-web/plugin-kit.css';

// The Logs page's empty states. Plugin Kit loads only on pages that use one of its components.
if (document.querySelector('pk-state-panel')) {
    void import('@verbb/plugin-kit-web/components/state-panel/pk-state-panel.js');
}
