<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
    Colors,
} from "chart.js";

Chart.register(DoughnutController, ArcElement, Tooltip, Legend, Colors);

const props = defineProps({
    title: { type: String, required: true },
    data: { type: Array, required: true },
});

const canvasRef = ref(null);
let chart = null;

onMounted(() => {
    Chart.getChart(canvasRef.value)?.destroy();
    chart = new Chart(canvasRef.value, {
        type: "doughnut",
        data: {
            labels: props.data.map((d) => d.name),
            datasets: [
                {
                    data: props.data.map((d) => d.value),
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: "left" },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce(
                                (a, b) => a + b,
                                0,
                            );
                            const pct = total
                                ? Math.round((ctx.parsed / total) * 100)
                                : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });
});

watch(
    () => props.data,
    (newData) => {
        if (!chart) return;
        chart.data.labels = newData.map((d) => d.name);
        chart.data.datasets[0].data = newData.map((d) => d.value);
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
