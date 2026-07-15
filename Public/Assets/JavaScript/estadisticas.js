(() => {
    'use strict';

    const dataElement = document.getElementById('estadisticasData');
    const periodSelect = document.getElementById('periodo');
    const facultySelect = document.getElementById('id_facultad');
    const careerSelect = document.getElementById('id_carrera');
    const customDateFields = document.querySelectorAll('.estadisticas-custom-date');

    const toggleCustomDates = () => {
        const isCustom = periodSelect?.value === 'personalizado';

        customDateFields.forEach((field) => {
            field.classList.toggle('is-disabled', !isCustom);
            field.querySelectorAll('input').forEach((input) => {
                input.disabled = !isCustom;
            });
        });
    };

    const filterCareers = () => {
        if (!facultySelect || !careerSelect) {
            return;
        }

        const facultyId = Number(facultySelect.value || 0);
        const currentCareer = Number(careerSelect.value || 0);
        let currentVisible = currentCareer === 0;

        Array.from(careerSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const careerFaculty = Number(option.dataset.facultad || 0);
            const visible = facultyId === 0 || careerFaculty === facultyId;
            option.hidden = !visible;
            option.disabled = !visible;

            if (visible && Number(option.value) === currentCareer) {
                currentVisible = true;
            }
        });

        if (!currentVisible) {
            careerSelect.value = '0';
        }
    };

    periodSelect?.addEventListener('change', toggleCustomDates);
    facultySelect?.addEventListener('change', filterCareers);
    toggleCustomDates();
    filterCareers();

    if (!dataElement || typeof window.Chart === 'undefined') {
        return;
    }

    let data = {};

    try {
        data = JSON.parse(dataElement.textContent || '{}');
    } catch (error) {
        console.error('No se pudieron cargar los datos de estadísticas.', error);
        return;
    }

    const unit = typeof data.unidad === 'string' && data.unidad.trim() !== ''
        ? data.unidad.trim()
        : 'usos';
    const unitTitle = unit.charAt(0).toUpperCase() + unit.slice(1);

    const css = getComputedStyle(document.documentElement);
    const colors = {
        espresso: css.getPropertyValue('--espresso').trim() || '#382417',
        walnut: css.getPropertyValue('--walnut').trim() || '#6D3C1C',
        caramel: css.getPropertyValue('--caramel').trim() || '#C57938',
        paper: css.getPropertyValue('--paper').trim() || '#E7C196',
        sage: css.getPropertyValue('--sage').trim() || '#7E9B76',
        muted: css.getPropertyValue('--muted').trim() || '#7E6D5D',
        cream: css.getPropertyValue('--cream').trim() || '#FFF9F0',
    };

    const palette = [
        colors.caramel,
        colors.sage,
        colors.walnut,
        colors.paper,
        '#9B6F4A',
        '#5F7D70',
        '#B7956D',
        '#7D5A50',
    ];

    Chart.defaults.font.family = 'Nunito, system-ui, sans-serif';
    Chart.defaults.color = colors.muted;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 9;

    const shorten = (value, max = 34) => {
        const text = String(value ?? '');
        return text.length > max ? `${text.slice(0, max - 1)}…` : text;
    };

    const commonGrid = {
        color: 'rgba(109, 60, 28, 0.08)',
        drawBorder: false,
    };

    const commonTicks = {
        color: colors.muted,
        font: { weight: '700' },
    };

    const createHorizontalBar = (canvasId, chartData, label, backgroundColor) => {
        const canvas = document.getElementById(canvasId);

        if (!canvas || !Array.isArray(chartData?.labels) || chartData.labels.length === 0) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: chartData.labels.map((item) => shorten(item, 45)),
                datasets: [{
                    label,
                    data: chartData.values,
                    backgroundColor,
                    borderRadius: 9,
                    borderSkipped: false,
                    barThickness: 'flex',
                    maxBarThickness: 34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                interaction: { mode: 'nearest', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => chartData.labels[items[0]?.dataIndex ?? 0] ?? '',
                            label: (context) => `${label}: ${context.parsed.x} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: { beginAtZero: true, grid: commonGrid, ticks: { ...commonTicks, precision: 0 } },
                    y: { grid: { display: false }, ticks: commonTicks },
                },
            },
        });
    };

    createHorizontalBar('graficoRanking', data.ranking, unitTitle, colors.caramel);
    createHorizontalBar('graficoEstudiantes', data.estudiantes, `${unitTitle} de estudiantes`, colors.sage);
    createHorizontalBar('graficoDocentes', data.docentes, `${unitTitle} de docentes`, colors.walnut);
    createHorizontalBar('graficoFacultades', data.facultades, unitTitle, colors.caramel);

    const distributionCanvas = document.getElementById('graficoDistribucion');

    if (distributionCanvas && Array.isArray(data.distribucion?.values)) {
        new Chart(distributionCanvas, {
            type: 'doughnut',
            data: {
                labels: data.distribucion.labels,
                datasets: [{
                    data: data.distribucion.values,
                    backgroundColor: [colors.sage, colors.caramel],
                    borderColor: colors.cream,
                    borderWidth: 5,
                    hoverOffset: 7,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 18, font: { weight: '800' } } },
                    tooltip: { callbacks: { label: (context) => `${context.label}: ${context.parsed} ${unit}` } },
                },
            },
        });
    }

    const trendCanvas = document.getElementById('graficoTendencia');

    if (trendCanvas && Array.isArray(data.tendencia?.labels)) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: data.tendencia.labels,
                datasets: [
                    {
                        label: 'Estudiantes',
                        data: data.tendencia.estudiantes,
                        borderColor: colors.sage,
                        backgroundColor: 'rgba(126, 155, 118, 0.14)',
                        pointBackgroundColor: colors.sage,
                        pointBorderColor: colors.cream,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Docentes',
                        data: data.tendencia.docentes,
                        borderColor: colors.caramel,
                        backgroundColor: 'rgba(197, 121, 56, 0.10)',
                        pointBackgroundColor: colors.caramel,
                        pointBorderColor: colors.cream,
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { font: { weight: '800' } } },
                    tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${context.parsed.y} ${unit}` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { ...commonTicks, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 } },
                    y: { beginAtZero: true, grid: commonGrid, ticks: { ...commonTicks, precision: 0 } },
                },
            },
        });
    }

    const matrixCanvas = document.getElementById('graficoUnidadesLibros');

    if (
        matrixCanvas &&
        Array.isArray(data.unidades_libros?.labels) &&
        data.unidades_libros.labels.length > 0 &&
        Array.isArray(data.unidades_libros?.datasets)
    ) {
        new Chart(matrixCanvas, {
            type: 'bar',
            data: {
                labels: data.unidades_libros.labels.map((item) => shorten(item, 52)),
                datasets: data.unidades_libros.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.values,
                    backgroundColor: palette[index % palette.length],
                    borderRadius: 7,
                    borderSkipped: false,
                    maxBarThickness: 34,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 14,
                            font: { weight: '800' },
                            generateLabels: (chart) => Chart.defaults.plugins.legend.labels.generateLabels(chart).map((item) => ({
                                ...item,
                                text: shorten(item.text, 32),
                            })),
                        },
                    },
                    tooltip: {
                        callbacks: {
                            title: (items) => data.unidades_libros.labels[items[0]?.dataIndex ?? 0] ?? '',
                            label: (context) => `${context.dataset.label}: ${context.parsed.x} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, grid: commonGrid, ticks: { ...commonTicks, precision: 0 } },
                    y: { stacked: true, grid: { display: false }, ticks: commonTicks },
                },
            },
        });
    }

    const academicTrendCanvas = document.getElementById('graficoTendenciaAcademica');

    if (
        academicTrendCanvas &&
        Array.isArray(data.tendencia_academica?.labels) &&
        Array.isArray(data.tendencia_academica?.datasets)
    ) {
        new Chart(academicTrendCanvas, {
            type: 'line',
            data: {
                labels: data.tendencia_academica.labels,
                datasets: data.tendencia_academica.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.values,
                    borderColor: palette[index % palette.length],
                    backgroundColor: palette[index % palette.length],
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 3,
                    tension: 0.32,
                    fill: false,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { weight: '800' },
                            generateLabels: (chart) => Chart.defaults.plugins.legend.labels.generateLabels(chart).map((item) => ({
                                ...item,
                                text: shorten(item.text, 38),
                            })),
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.parsed.y} ${unit}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { ...commonTicks, maxRotation: 0, autoSkip: true, maxTicksLimit: 16 } },
                    y: { beginAtZero: true, grid: commonGrid, ticks: { ...commonTicks, precision: 0 } },
                },
            },
        });
    }
})();
