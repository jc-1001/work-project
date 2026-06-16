import { ref, onMounted, onUnmounted } from "vue";

const cartCount = ref(0);

const syncCount = () => {
    try {
        const saved = localStorage.getItem("cart");
        const items = saved ? JSON.parse(saved) : [];
        cartCount.value = items.reduce(
            (sum, item) => sum + Number(item.quantity || 1),
            0,
        );
    } catch {
        cartCount.value = 0;
    }
};

export function useCart() {
    const onStorage = (e) => {
        if (e.key === "cart") syncCount();
    };

    onMounted(() => {
        syncCount();
        window.addEventListener("storage", onStorage);
        window.addEventListener("cart-updated", syncCount);
    });

    onUnmounted(() => {
        window.removeEventListener("storage", onStorage);
        window.removeEventListener("cart-updated", syncCount);
    });

    return { cartCount, syncCount };
}
