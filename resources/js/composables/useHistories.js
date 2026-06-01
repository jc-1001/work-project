import { ref } from 'vue'

const MAX_HISTORY = 20

const histories = ref(JSON.parse(localStorage.getItem('histories')) || [])

const save = () => {
    localStorage.setItem('histories', JSON.stringify(histories.value))
}

export function useHistories() {
    const isHistoried = (productId) =>
        histories.value.some((p) => p.id === productId)

    const addHistory = (product) => {
        const index = histories.value.findIndex((p) => p.id === product.id)
        if (index !== -1) histories.value.splice(index, 1)
        histories.value.unshift({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            stock: product.stock,
            category_id: product.category_id ?? null,
        })
        if (histories.value.length > MAX_HISTORY)
            histories.value = histories.value.slice(0, MAX_HISTORY)
        save()
    }

    const removeHistory = (productId) => {
        const index = histories.value.findIndex((p) => p.id === productId)
        if (index !== -1) {
            histories.value.splice(index, 1)
            save()
        }
    }

    return { histories, isHistoried, addHistory, removeHistory }
}
