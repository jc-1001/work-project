/**
 * 處理商品圖片路徑
 * - 假資料：完整 URL（e.g. https://picsum.photos/...）→ 直接使用
 * - 上傳圖片：相對路徑（e.g. products/xxx.jpg）→ 加上 storage 前綴
 */
export function getImageUrl(image) {
  if (!image) return "";
  return image.startsWith("http")
    ? image
    : `http://localhost:8080/storage/${image}`;
}
