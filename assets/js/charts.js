import ApexCharts from 'apexcharts';

function createChartBar(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const seriesData = JSON.parse(el.dataset.series || '[]');
    const labelsData = JSON.parse(el.dataset.labels || '[]');
    const color = el.dataset.color || 'var(--color-base-200)';
    const chartName = el.dataset.name || 'Data';

    const options = {
        chart: {
            type: 'bar',
            background: 'transparent',
            height: 250,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'inherit',
            sparkline: { enabled: false },
        },
        colors: [color],
        stroke: { curve: 'smooth', width: 4 },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                borderRadius: 4,
                endingShape: 'rounded'
            }
        },

        fill: {
            type: 'solid',
            opacity: 1
        },
        series: [{ name: chartName, data: seriesData }],
        xaxis: {
            categories: labelsData,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: 'oklch(var(--bc) / 0.5)' } }
        },
        yaxis: { show: true },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                colors: ['#000000']
            },
        },
        theme: {
            mode: document.documentElement.getAttribute('data-theme').includes('dark') ? 'dark' : 'light'
        }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
}

function createChartLine(elementId, price = false) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const seriesData = JSON.parse(el.dataset.series || '[]');
    const labelsData = JSON.parse(el.dataset.labels || '[]');
    const color = el.dataset.color || 'var(--color-base-200)';
    const chartName = el.dataset.name || 'Data';

    const options = {
        chart: {
            type: 'line',
            background: 'transparent',
            height: 250,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'inherit',
            sparkline: { enabled: false },
        },
        colors: [color],
        stroke: { curve: 'smooth', width: 4 },
        plotOptions: {
            bar: {
                horizontal: true,
                columnWidth: '60%',
                borderRadius: 4,
                endingShape: 'rounded'
            }
        },

        fill: {
            type: 'solid',
            opacity: 1
        },
        series: [{ name: chartName, data: seriesData }],
        xaxis: {
            categories: labelsData,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: 'oklch(var(--bc) / 0.5)' } }
        },
        yaxis: { show: true },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return (price ? val + " €" : val);
            },
            style: {
                fontSize: '12px',
                colors: ['#000000']
            },
        },
        theme: {
            mode: document.documentElement.getAttribute('data-theme').includes('dark') ? 'dark' : 'light'
        }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
}

// Initialisation groupée
const initAllCharts = () => {
    createChartBar('chart-stockPerCategory');
    createChartLine('chart-sellPricePerCategory', true);
    createChartBar('chart-stock-value');
};

document.addEventListener('turbo:load', initAllCharts);