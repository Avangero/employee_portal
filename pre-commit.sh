#!/bin/bash

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

echo "🔍 Проверка и форматирование кода..."

# Запускаем Duster для PHP файлов
echo "Запуск Duster для PHP файлов..."
./vendor/bin/duster fix
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при выполнении Duster${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Duster успешно отформатировал PHP файлы${NC}"

# Запускаем Prettier для всех поддерживаемых файлов
echo "Запуск Prettier для форматирования кода..."
npx prettier --write "**/*.{js,jsx,ts,tsx,css,scss,vue,html,json,md,yaml,yml}"
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при выполнении Prettier${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Prettier успешно отформатировал файлы${NC}"

echo -e "${GREEN}✨ Форматирование кода успешно завершено!${NC}" 