import '../css/shield.css';

// The Logs page's empty states. Plugin Kit loads only on pages that use one of its components.
if (document.querySelector('pk-state-panel')) {
    void Promise.all([
        import('@verbb/plugin-kit-web/plugin-kit.css'),
        import('@verbb/plugin-kit-web/components/state-panel/pk-state-panel.js'),
    ]);
}
