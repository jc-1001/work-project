import { ref } from 'vue'

const favorites = ref(JSON.parse(localStorage.getItem('favorites')) || [])

const save = () => {
    localStorage.setItem('favorites', JSON.stringify(favorites.value))
}

export function useFavorites() {
    const isFavorited = (productId) =>
        favorites.value.some((p) => p.id === productId)

    const toggleFavorite = (product) => {
        const index = favorites.value.findIndex((p) => p.id === product.id)
        if (index !== -1) {
            favorites.value.splice(index, 1)
        } else {
            favorites.value.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                stock: product.stock,
                category_id: product.category_id ?? null,
            })
        }
        save()
    }

    const removeFavorite = (productId) => {
        const index = favorites.value.findIndex((p) => p.id === productId)
        if (index !== -1) {
            favorites.value.splice(index, 1)
            save()
        }
    }

    return { favorites, isFavorited, toggleFavorite, removeFavorite }
}
