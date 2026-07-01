#!/usr/bin/env bash
set -e

# 啟動所有 Docker 服務（shop / mssql / redis / mailpit）
docker compose up -d

# 前端 build 用本機 npm，若沒裝就退回用 node:20 容器跑
if command -v npm >/dev/null 2>&1; then
    NPM="npm"
else
    NPM="docker run --rm -v $(pwd):/app -w /app node:20 npm"
fi

if [ ! -d node_modules ]; then
    echo "安裝 npm 依賴中..."
    # vue-router@5.1.0 的 peerDependency 要求 vite ^7/^8，但專案鎖定 vite ^6，
    # lockfile 本身的版本組合沒問題，只是 npm 的嚴格 peer check 會擋下來，故加上此旗標
    $NPM ci --legacy-peer-deps
fi

if [ ! -f public/build/manifest.json ]; then
    echo "Build 前端資源中..."
    $NPM run build
fi

echo ""
echo "完成！開啟 http://localhost:8999"
echo "Mailpit 郵件測試介面：http://localhost:8025"
