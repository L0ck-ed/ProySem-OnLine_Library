/**
 * Tooltip externo en HTML para gráficos de Chart.js.
 *
 * Los tooltips normales de Chart.js se dibujan DENTRO del canvas,
 * por lo que si el canvas es pequeño (ej. 120x120) el texto se recorta
 * sin importar el maxWidth configurado. Esta función crea un <div>
 * flotante fuera del canvas para mostrar el contenido completo
 * (por ejemplo, el nombre completo de un libro) sin que se corte.
 *
 * Uso: en las opciones de cualquier Chart.js, dentro de
 * plugins.tooltip, configurar:
 *   enabled: false,
 *   position: 'nearest',
 *   external: externalTooltipHandler
 *
 * @param {Object} context - Contexto que Chart.js pasa automáticamente
 *                           al callback "external" del tooltip.
 */
function externalTooltipHandler(context) {
    const { chart, tooltip } = context;

    let tooltipEl = document.getElementById('chartjs-tooltip-flotante');
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip-flotante';
        tooltipEl.style.position = 'absolute';
        tooltipEl.style.pointerEvents = 'none';
        tooltipEl.style.background = 'rgba(56, 36, 23, 0.95)';
        tooltipEl.style.color = '#FFF9F0';
        tooltipEl.style.borderRadius = '12px';
        tooltipEl.style.padding = '10px 16px';
        tooltipEl.style.fontSize = '14px';
        tooltipEl.style.lineHeight = '1.4';
        tooltipEl.style.maxWidth = '240px';
        tooltipEl.style.whiteSpace = 'normal';
        tooltipEl.style.wordWrap = 'break-word';
        tooltipEl.style.zIndex = '9999';
        tooltipEl.style.boxShadow = '0 4px 14px rgba(0,0,0,0.25)';
        tooltipEl.style.transition = 'opacity .1s ease';
        document.body.appendChild(tooltipEl);
    }

    // Ocultar si no hay tooltip activo
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }

    // Construir el contenido (título en negrita + líneas del cuerpo)
    if (tooltip.body) {
        let innerHtml = '';
        (tooltip.title || []).forEach(title => {
            if (title && title.trim() !== '') {
                innerHtml += `<div style="font-weight:bold; margin-bottom:4px;">${title}</div>`;
            }
        });
        tooltip.body.forEach(item => {
            item.lines.forEach(line => {
                innerHtml += `<div>${line}</div>`;
            });
        });
        tooltipEl.innerHTML = innerHtml;
    }

    const canvasRect = chart.canvas.getBoundingClientRect();
    let left = canvasRect.left + window.scrollX + tooltip.caretX;
    let top = canvasRect.top + window.scrollY + tooltip.caretY;

    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = left + 'px';
    tooltipEl.style.top = top + 'px';

    // Evitar que el tooltip se salga de la pantalla
    requestAnimationFrame(() => {
        const rect = tooltipEl.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            tooltipEl.style.left = (window.innerWidth - rect.width - 10 + window.scrollX) + 'px';
        }
        if (rect.left < 0) {
            tooltipEl.style.left = (10 + window.scrollX) + 'px';
        }
        if (rect.top < 0) {
            tooltipEl.style.top = (10 + window.scrollY) + 'px';
        }
    });
}