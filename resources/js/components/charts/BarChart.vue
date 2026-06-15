<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import {
    Chart,
    BarController,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
    Colors,
} from "chart.js";

Chart.register(
    BarController,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
    Colors,
);

const props = defineProps({
    title: { type: String, required: true },
    categories: { type: Array, required: true },
    data: { type: Array, required: true },
    icon: { type: String, required: false },
    clickable: { type: Boolean, default: false },
});

const emit = defineEmits(["bar-click"]);

const canvasRef = ref(null);
let chart = null;

onMounted(() => {
    Chart.getChart(canvasRef.value)?.destroy();
    chart = new Chart(canvasRef.value, {
        type: "bar",
        data: {
            labels: props.categories,
            datasets: [
                {
                    data: props.data,
                },
            ],
        },
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            onClick: (_, elements) => {
                if (props.clickable && elements.length > 0) {
                    emit("bar-click", elements[0].index);
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
            },
            plugins: {
                legend: { display: false },
            },
        },
    });
});

watch(
    [() => props.categories, () => props.data],
    () => {
        if (!chart) return;
        chart.data.labels = props.categories;
        chart.data.datasets[0].data = props.data;
        chart.update();
    },
    { deep: true },
);

onUnmounted(() => chart?.destroy());
</script>

<template>
    <v-card height="100%">
        <v-card-title class="text-body-1 font-weight-bold pt-4 px-4">
            <v-icon v-if="icon" size="20" class="mr-1">{{ icon }}</v-icon>
            {{ title }}
        </v-card-title>
        <v-card-text>
            <div style="height: 220px; position: relative">
                <canvas ref="canvasRef"></canvas>
            </div>
        </v-card-text>
    </v-card>
</template>
