<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import {
    Chart,
    LineController,
    CategoryScale,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
    Legend,
    Colors,
} from "chart.js";

Chart.register(
    LineController,
    CategoryScale,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
    Legend,
    Colors,
);

const props = defineProps({
    title: { type: String, required: true },
    labels: { type: Array, required: true },
    series: { type: Array, required: true },
});

const canvasRef = ref(null);
let chart = null;

function buildDatasets() {
    return props.series.map((s) => ({
        label: s.name,
        data: s.data,
        tension: 0.4,
        fill: false,
        pointRadius: 3,
    }));
}

onMounted(() => {
    Chart.getChart(canvasRef.value)?.destroy();
    chart = new Chart(canvasRef.value, {
        type: "line",
        data: {
            labels: props.labels,
            datasets: buildDatasets(),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { stepSize: 1, precision: 0 } },
            },
            plugins: {
                legend: { display: props.series.length > 1 },
            },
        },
    });
});

watch(
    [() => props.labels, () => props.series],
    () => {
        if (!chart) return;
        chart.data.labels = props.labels;
        chart.data.datasets = buildDatasets();
        chart.update();
    },
    { deep: true },
);

onUnmounted(() => chart?.destroy());
</script>

<template>
    <v-card height="100%">
        <v-card-title class="text-body-1 font-weight-bold pt-4 px-4">{{
            title
        }}</v-card-title>
        <v-card-text>
            <div style="height: 220px; position: relative">
                <canvas ref="canvasRef"></canvas>
            </div>
        </v-card-text>
    </v-card>
</template>
