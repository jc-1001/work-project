const appUrl = import.meta.env.VITE_API_URL ?? "http://localhost:8999";

export function getImageUrl(image) {
  if (!image) return "";
  return image.startsWith("http")
    ? image
    : `${appUrl}/storage/${image}`;
}
